<?php

namespace App\Services;

use App\Models\LeadDelivery;

class DeliveryMetricsService
{
    public function snapshot(): array
    {
        return [
            'successful_deliveries' => LeadDelivery::query()
                ->where('status', 'sent')
                ->count(),

            'failed_deliveries' => LeadDelivery::query()
                ->where('status', 'failed')
                ->count(),

            'pending_deliveries' => LeadDelivery::query()
                ->where('status', 'pending')
                ->count(),
        ];
    }
}
