<?php

namespace EscapeZoom\Core\Modules\Commerce\Repositories;

use EscapeZoom\Core\Modules\Commerce\Models\PaymentTransaction;
use Illuminate\Support\Collection;

class PaymentTransactionRepository
{
    public function createEvent(array $attributes): PaymentTransaction
    {
        $existing = PaymentTransaction::query()
            ->where('idempotency_key', $attributes['idempotency_key'])
            ->first();

        if ($existing instanceof PaymentTransaction) {
            return $existing;
        }

        return PaymentTransaction::query()->create($attributes);
    }

    public function findByGatewayReference(?string $gateway, ?string $gatewayTransactionId, ?string $gatewayReferenceId): Collection
    {
        $query = PaymentTransaction::query();

        if ($gateway !== null && $gateway !== '') {
            $query->where('gateway', $gateway);
        }

        if ($gatewayTransactionId !== null && $gatewayTransactionId !== '') {
            $query->where('gateway_transaction_id', $gatewayTransactionId);
        }

        if ($gatewayReferenceId !== null && $gatewayReferenceId !== '') {
            $query->where('gateway_reference_id', $gatewayReferenceId);
        }

        return $query->orderByDesc('id')->get();
    }

    /**
     * @return array{debit:string,credit:string,net:string}
     */
    public function aggregateSuccessfulTotalsByOrderId(int $orderId): array
    {
        $debit = (string) PaymentTransaction::query()
            ->where('order_id', $orderId)
            ->where('status', 'success')
            ->where('direction', 'debit')
            ->sum('amount');

        $credit = (string) PaymentTransaction::query()
            ->where('order_id', $orderId)
            ->where('status', 'success')
            ->where('direction', 'credit')
            ->sum('amount');

        $net = (string) ((int) $debit - (int) $credit);

        return [
            'debit' => $debit,
            'credit' => $credit,
            'net' => $net,
        ];
    }

    public function findByOrderId(int $orderId): Collection
    {
        return PaymentTransaction::query()
            ->where('order_id', $orderId)
            ->orderByDesc('id')
            ->get();
    }
}
