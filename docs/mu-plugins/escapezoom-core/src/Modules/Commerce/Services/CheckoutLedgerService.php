<?php

namespace EscapeZoom\Core\Modules\Commerce\Services;

use EscapeZoom\Core\Modules\Commerce\Models\CheckoutOrder;
use EscapeZoom\Core\Modules\Commerce\Models\OrderFinance;
use EscapeZoom\Core\Modules\Commerce\Repositories\CheckoutOrderRepository;
use EscapeZoom\Core\Modules\Commerce\Repositories\PaymentTransactionRepository;
use EscapeZoom\Core\Modules\Marketing\Models\Marketing;

class CheckoutLedgerService
{
    public function __construct(
        private readonly CheckoutOrderRepository $orders = new CheckoutOrderRepository(),
        private readonly PaymentTransactionRepository $transactions = new PaymentTransactionRepository()
    ) {
    }

    public function getByOrderId(int $orderId): ?CheckoutOrder
    {
        $order = CheckoutOrder::query()->where('order_id', $orderId)->first();

        if (defined('EZ_LEDGER_SHADOW_READ_ENABLED') && EZ_LEDGER_SHADOW_READ_ENABLED) {
            $comparator = new ShadowReadComparator();
            $result = $comparator->compareOrder($orderId);
            if (($result['legacy_found'] ?? false) !== ($result['finance_found'] ?? false)) {
                error_log('[EZ_CORE] SHADOW_READ_MISMATCH order_id=' . $orderId);
            }
        }

        if ($order instanceof CheckoutOrder || !defined('EZ_LEDGER_LEGACY_FALLBACK_ENABLED') || !EZ_LEDGER_LEGACY_FALLBACK_ENABLED) {
            return $order;
        }

        $legacy = Marketing::query()->where('order_id', $orderId)->first();
        if (!$legacy) {
            return null;
        }

        return $this->createOrUpdateIncart([
            'order_id' => $orderId,
            'user_id' => isset($legacy->customer_id) ? (int) $legacy->customer_id : null,
            'product_id' => isset($legacy->game_id) ? (int) $legacy->game_id : null,
            'quantity' => isset($legacy->order_tickets_quantity) ? (int) $legacy->order_tickets_quantity : 1,
        ]);
    }

    public function createOrUpdateIncart(array $payload): CheckoutOrder
    {
        $orderId = (int) ($payload['order_id'] ?? 0);
        if ($orderId < 1) {
            throw new \InvalidArgumentException('order_id is required.');
        }
        $checkout = $this->getByOrderId($orderId);

        if (!$checkout instanceof CheckoutOrder) {
            $checkout = $this->orders->createDraft($payload + [
                'order_id' => $orderId,
                'stage_status' => 'wc-incart',
            ]);
        } else {
            $checkout->fill($payload + ['stage_status' => 'wc-incart']);
            $checkout->save();
        }

        $this->orders->upsertUserSnapshot($checkout);
        $this->orders->upsertGameSnapshot($checkout);
        $participants = is_array($payload['participants'] ?? null) ? $payload['participants'] : [];
        if ($participants !== []) {
            $this->orders->replaceParticipants($orderId, $participants);
        }
        $this->transactions->createEvent([
            'order_id' => $orderId,
            'user_id' => $checkout->user_id,
            'gateway' => 'system',
            'channel' => 'online',
            'event_type' => 'wc-incart',
            'direction' => 'debit',
            'status' => 'pending',
            'amount' => 1,
            'currency' => 'IRR',
            'idempotency_key' => 'incart:' . $orderId,
            'occurred_at' => current_time('mysql'),
        ]);

        return $checkout->refresh();
    }

    public function markCheckoutFilled(int $orderId, array $payload = []): ?CheckoutOrder
    {
        $checkout = $this->getByOrderId($orderId);
        if (!$checkout instanceof CheckoutOrder) {
            return null;
        }
        $checkout->fill($payload + ['stage_status' => 'wc-checkout']);
        $checkout->save();
        $this->orders->upsertFinance($checkout, $payload['finance'] ?? []);
        return $checkout->refresh();
    }

    public function markBankPending(int $orderId): ?CheckoutOrder
    {
        $checkout = $this->getByOrderId($orderId);
        if (!$checkout instanceof CheckoutOrder) {
            return null;
        }
        $checkout->stage_status = 'wc-bank-pending';
        $checkout->save();
        $this->transactions->createEvent([
            'order_id' => $orderId,
            'user_id' => $checkout->user_id,
            'gateway' => 'bank',
            'channel' => 'online',
            'event_type' => 'bank-request',
            'direction' => 'debit',
            'status' => 'pending',
            'amount' => 1,
            'currency' => 'IRR',
            'idempotency_key' => 'bank-request:' . $orderId,
            'occurred_at' => current_time('mysql'),
        ]);
        return $checkout->refresh();
    }

    public function handleCallbackSuccess(int $orderId): ?CheckoutOrder
    {
        $checkout = $this->recalculate($orderId);
        if (!$checkout instanceof CheckoutOrder) {
            return null;
        }
        $checkout->stage_status = 'wc-complete';
        $checkout->order_status = 'paid';
        $checkout->save();
        $this->transactions->createEvent([
            'order_id' => $orderId,
            'user_id' => $checkout->user_id,
            'gateway' => 'bank',
            'channel' => 'online',
            'event_type' => 'callback-success',
            'direction' => 'debit',
            'status' => 'success',
            'amount' => 1,
            'currency' => 'IRR',
            'idempotency_key' => 'callback-success:' . $orderId,
            'occurred_at' => current_time('mysql'),
        ]);
        $this->syncToMarktingSuccess($orderId, $checkout);
        return $checkout->refresh();
    }

    public function handleCallbackFailure(int $orderId): ?CheckoutOrder
    {
        $checkout = $this->getByOrderId($orderId);
        if (!$checkout instanceof CheckoutOrder) {
            return null;
        }
        $checkout->stage_status = 'wc-failed';
        $checkout->order_status = 'failed';
        $checkout->save();
        $this->transactions->createEvent([
            'order_id' => $orderId,
            'user_id' => $checkout->user_id,
            'gateway' => 'bank',
            'channel' => 'online',
            'event_type' => 'callback-fail',
            'direction' => 'debit',
            'status' => 'failed',
            'amount' => 1,
            'currency' => 'IRR',
            'idempotency_key' => 'callback-fail:' . $orderId,
            'occurred_at' => current_time('mysql'),
        ]);
        $this->syncToMarktingFailure($orderId, $checkout);
        return $checkout->refresh();
    }

    public function expireIncart(int $orderId): ?CheckoutOrder
    {
        $checkout = $this->getByOrderId($orderId);
        if (!$checkout instanceof CheckoutOrder) {
            return null;
        }
        $checkout->stage_status = 'wc-expired';
        $checkout->order_status = 'expired';
        $checkout->save();
        return $checkout->refresh();
    }

    public function recalculate(int $orderId): ?CheckoutOrder
    {
        $order = $this->getByOrderId($orderId);
        if (!$order instanceof CheckoutOrder) {
            return null;
        }
        return $this->orders->recomputeAggregatesFromLedger($order, $this->transactions);
    }

    private function syncToMarktingSuccess(int $orderId, CheckoutOrder $checkout): void
    {
        $finance = OrderFinance::query()->where('order_id', $orderId)->first();
        Marketing::query()->updateOrCreate(
            ['order_id' => $orderId],
            [
                'customer_id' => $checkout->user_id,
                'game_id' => $checkout->product_id,
                'order_status' => 'wc-completed',
                'order_paid' => (int) ($finance->paid_amount ?? 0),
                'order_tickets_quantity' => (int) $checkout->quantity,
            ]
        );
    }

    private function syncToMarktingFailure(int $orderId, CheckoutOrder $checkout): void
    {
        $finance = OrderFinance::query()->where('order_id', $orderId)->first();
        Marketing::query()->updateOrCreate(
            ['order_id' => $orderId],
            [
                'customer_id' => $checkout->user_id,
                'game_id' => $checkout->product_id,
                'order_status' => 'wc-failed',
                'order_paid' => (int) ($finance->paid_amount ?? 0),
                'order_tickets_quantity' => (int) $checkout->quantity,
            ]
        );
    }
}
