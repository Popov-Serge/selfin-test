<?php

namespace App\Repositories\Contracts;

use App\Models\LeadDelivery;

interface LeadDeliveryRepositoryInterface
{
    public function updateStatus(
        LeadDelivery $delivery,
        string $status,
    ): LeadDelivery;

    public function findByPartnerAndRequestIdForUpdate(
        int $partnerId,
        string $requestId,
    ): ?LeadDelivery;
}
