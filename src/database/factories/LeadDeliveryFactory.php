<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadDelivery;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadDelivery>
 */
class LeadDeliveryFactory extends Factory
{
    protected $model = LeadDelivery::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'partner_id' => Partner::factory(),
            'request_id' => null,
            'status' => 'pending',
            'last_error' => null,
            'attempts_count' => 0,
            'sent_at' => null,
        ];
    }
}
