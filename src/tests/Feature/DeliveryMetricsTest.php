<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadDelivery;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_delivery_counts_by_status(): void
    {
        $lead = Lead::factory()->create();
        $partner = Partner::factory()->create();

        LeadDelivery::factory()->create([
            'lead_id' => $lead->id,
            'partner_id' => $partner->id,
            'status' => 'sent',
            'request_id' => 'request-sent',
        ]);

        LeadDelivery::factory()->create([
            'lead_id' => $lead->id,
            'partner_id' => Partner::factory()->create()->id,
            'status' => 'failed',
            'request_id' => null,
        ]);

        LeadDelivery::factory()->create([
            'lead_id' => $lead->id,
            'partner_id' => Partner::factory()->create()->id,
            'status' => 'pending',
            'request_id' => null,
        ]);

        $this->getJson('/api/metrics/deliveries')
            ->assertOk()
            ->assertExactJson([
                'successful_deliveries' => 1,
                'failed_deliveries' => 1,
                'pending_deliveries' => 1,
            ]);
    }
}
