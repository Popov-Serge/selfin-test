<?php

namespace App\Repositories;

use App\DTO\DeliveryRevenueData;
use App\Models\DeliveryRevenue;
use App\Repositories\Contracts\DeliveryRevenueRepositoryInterface;

class DeliveryRevenueRepository implements DeliveryRevenueRepositoryInterface
{
    public function existsForDelivery(int $deliveryId): bool
    {
        return DeliveryRevenue::query()
            ->where('delivery_id', $deliveryId)
            ->exists();
    }

    public function create(DeliveryRevenueData $data): DeliveryRevenue
    {
        $revenue = new DeliveryRevenue;

        $revenue->delivery_id = $data->deliveryId;
        $revenue->webhook_event_id = $data->webhookEventId;
        $revenue->amount = $data->amount;
        $revenue->currency = $data->currency;
        $revenue->save();

        return $revenue;
    }
}
