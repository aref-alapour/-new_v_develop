<?php

namespace EscapeZoom\Core\Modules\Commerce\Repositories;

use EscapeZoom\Core\Modules\Commerce\Models\CheckoutOrder;
use EscapeZoom\Core\Modules\Commerce\Models\OrderFinance;
use EscapeZoom\Core\Modules\Commerce\Models\OrderGameSnapshot;
use EscapeZoom\Core\Modules\Commerce\Models\OrderParticipant;
use EscapeZoom\Core\Modules\Commerce\Models\OrderUserSnapshot;
use EscapeZoom\Core\Modules\WpCore\Models\Post;
use EscapeZoom\Core\Modules\WpCore\Models\PostMeta;
use EscapeZoom\Core\Modules\WpCore\Models\User;
use EscapeZoom\Core\Modules\WpCore\Models\UserMeta;

class CheckoutOrderRepository
{
    public function findByOrderId(int $orderId): ?CheckoutOrder
    {
        return CheckoutOrder::query()->where('order_id', $orderId)->first();
    }

    public function createDraft(array $attributes): CheckoutOrder
    {
        $defaults = [
            'order_status' => 'draft',
            'stage_status' => 'wc-incart',
            'quantity' => 1,
        ];

        return CheckoutOrder::query()->create(array_merge($defaults, $attributes));
    }

    public function upsertFinance(CheckoutOrder $order, array $finance): OrderFinance
    {
        $gross = (int) ($finance['gross_amount'] ?? 0);
        $coupon = (int) ($finance['coupon_discount_amount'] ?? 0);
        $level = (int) ($finance['level_discount_amount'] ?? 0);
        $payable = max(0, $gross - $coupon - $level);

        $payload = [
            'currency' => $finance['currency'] ?? 'IRR',
            'payment_type' => $finance['payment_type'] ?? 'complete',
            'price_unit' => (int) ($finance['price_unit'] ?? 0),
            'gross_amount' => $gross,
            'coupon_discount_amount' => $coupon,
            'level_discount_amount' => $level,
            'payable_amount' => $payable,
            'wallet_amount' => (int) ($finance['wallet_amount'] ?? 0),
            'online_amount' => (int) ($finance['online_amount'] ?? 0),
            'installment_amount' => (int) ($finance['installment_amount'] ?? 0),
            'paid_amount' => (int) ($finance['paid_amount'] ?? 0),
            'remaining_amount' => max(0, $payable - (int) ($finance['paid_amount'] ?? 0)),
            'coupon_code' => $finance['coupon_code'] ?? null,
            'pricing_snapshot_json' => $finance['pricing_snapshot_json'] ?? null,
        ];

        return OrderFinance::query()->updateOrCreate(
            ['order_id' => (int) $order->order_id],
            $payload
        );
    }

    public function recomputeAggregatesFromLedger(CheckoutOrder $order, PaymentTransactionRepository $transactions): CheckoutOrder
    {
        $totals = $transactions->aggregateSuccessfulTotalsByOrderId((int) $order->order_id);
        $paid = max(0, (int) $totals['net']);
        $finance = OrderFinance::query()->where('order_id', (int) $order->order_id)->first();
        $payable = $finance ? (int) $finance->payable_amount : 0;
        $remaining = max(0, $payable - $paid);

        $walletDebit = (int) $transactions->findByOrderId((int) $order->order_id)
            ->where('status', 'success')
            ->where('direction', 'debit')
            ->where('channel', 'wallet')
            ->sum('amount');
        $onlineDebit = (int) $transactions->findByOrderId((int) $order->order_id)
            ->where('status', 'success')
            ->where('direction', 'debit')
            ->where('channel', 'online')
            ->sum('amount');
        $installmentDebit = (int) $transactions->findByOrderId((int) $order->order_id)
            ->where('status', 'success')
            ->where('direction', 'debit')
            ->where('channel', 'installment')
            ->sum('amount');

        if ($finance instanceof OrderFinance) {
            $finance->fill([
                'wallet_amount' => $walletDebit,
                'online_amount' => $onlineDebit,
                'installment_amount' => $installmentDebit,
                'paid_amount' => $paid,
                'remaining_amount' => $remaining,
            ]);
            $finance->save();
        }

        return $order->refresh();
    }

    public function upsertUserSnapshot(CheckoutOrder $order): OrderUserSnapshot
    {
        $user = $order->user_id ? User::query()->where('ID', (int) $order->user_id)->first() : null;
        $meta = [];
        if ($user) {
            $rows = UserMeta::query()->where('user_id', (int) $user->ID)->get(['meta_key', 'meta_value']);
            foreach ($rows as $row) {
                $meta[$row->meta_key] = $row->meta_value;
            }
        }

        return OrderUserSnapshot::query()->updateOrCreate(
            ['order_id' => (int) $order->order_id],
            [
                'user_id' => $order->user_id,
                'first_name' => $meta['first_name'] ?? null,
                'last_name' => $meta['last_name'] ?? null,
                'display_name' => $user?->display_name,
                'phone_number' => $meta['billing_phone'] ?? null,
                'email' => $user?->user_email,
                'registered_at_snapshot' => $user?->user_registered,
                'snapshot_json' => $meta,
            ]
        );
    }

    public function upsertGameSnapshot(CheckoutOrder $order): ?OrderGameSnapshot
    {
        if (!$order->product_id) {
            return null;
        }

        $post = Post::query()->where('ID', (int) $order->product_id)->first();
        $metas = PostMeta::query()->where('post_id', (int) $order->product_id)->get(['meta_key', 'meta_value']);
        $metaMap = [];
        foreach ($metas as $meta) {
            $metaMap[$meta->meta_key] = $meta->meta_value;
        }

        return OrderGameSnapshot::query()->updateOrCreate(
            ['order_id' => (int) $order->order_id],
            [
                'game_id' => $order->product_id,
                'game_name' => $post?->post_title,
                'game_city' => $metaMap['game_city'] ?? null,
                'game_area' => $metaMap['room_loc'] ?? null,
                'game_duration' => isset($metaMap['room_duration']) ? (int) $metaMap['room_duration'] : null,
                'game_brand' => $metaMap['brand_title'] ?? null,
                'manager_id' => isset($metaMap['sans_manager']) ? (int) $metaMap['sans_manager'] : null,
                'price_at_order' => isset($metaMap['ticket_price']) ? (int) $metaMap['ticket_price'] : null,
                'snapshot_json' => $metaMap,
            ]
        );
    }

    public function replaceParticipants(int $orderId, array $phones): void
    {
        OrderParticipant::query()->where('order_id', $orderId)->delete();
        foreach ($phones as $index => $phone) {
            $trimmed = trim((string) $phone);
            if ($trimmed === '') {
                continue;
            }
            OrderParticipant::query()->create([
                'order_id' => $orderId,
                'phone_number' => $trimmed,
                'is_main_customer' => $index === 0,
            ]);
        }
    }

    public function markStatus(CheckoutOrder $order, string $status): CheckoutOrder
    {
        $allowed = [
            'draft',
            'pending',
            'partially_paid',
            'paid',
            'failed',
            'cancelled',
            'refunded',
            'expired',
        ];

        if (!in_array($status, $allowed, true)) {
            return $order;
        }

        $order->order_status = $status;
        $order->save();

        return $order->refresh();
    }
}
