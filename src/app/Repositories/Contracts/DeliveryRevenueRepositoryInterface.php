<?php

namespace App\Repositories\Contracts;

use App\DTO\DeliveryRevenueData;
use App\Models\DeliveryRevenue;

interface DeliveryRevenueRepositoryInterface
{
    public function existsForDelivery(int $deliveryId): bool;

    public function create(DeliveryRevenueData $data): DeliveryRevenue;
}
