<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\Partner;
use App\Services\LeadDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverLeadToPartner implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $leadId,
        public readonly int $partnerId,
    ) {}

    public function backoff(): array
    {
        return [10, 20];
    }

    public function handle(LeadDeliveryService $deliveryService): void
    {
        $lead = Lead::query()->findOrFail($this->leadId);
        $partner = Partner::query()->findOrFail($this->partnerId);

        if (! $partner->is_active) {
            return;
        }

        $deliveryService->deliver($lead, $partner);
    }
}
