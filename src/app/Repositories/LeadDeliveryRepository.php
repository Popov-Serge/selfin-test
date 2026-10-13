<?php

namespace App\Repositories;

use App\Models\LeadDelivery;
use App\Repositories\Contracts\LeadDeliveryRepositoryInterface;

class LeadDeliveryRepository implements LeadDeliveryRepositoryInterface
{
    public function updateStatus(
        LeadDelivery $delivery,
        string $status,
    ): LeadDelivery {
        $delivery->status = $status;
        $delivery->save();

        return $delivery->refresh();
    }

    public function findByPartnerAndRequestIdForUpdate(
        int $partnerId,
        string $requestId,
    ): ?LeadDelivery {
        return LeadDelivery::query()
            ->where('partner_id', $partnerId)
            ->where('request_id', $requestId)
            ->lockForUpdate()
            ->first();
    }
}
