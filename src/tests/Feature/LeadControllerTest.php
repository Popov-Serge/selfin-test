<?php

namespace Tests\Feature;

use App\Jobs\DeliverLeadToPartner;
use App\Models\Lead;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_lead_and_queues_delivery_for_active_partners(): void
    {
        Queue::fake();

        Partner::factory()->create(['is_active' => true]);
        Partner::factory()->create(['is_active' => true]);
        Partner::factory()->create(['is_active' => false]);

        $response = $this->postJson('/api/leads', $this->validLeadData());

        $response
            ->assertAccepted()
            ->assertJson([
                'message' => 'Lead accepted',
                'deliveries_queued' => 2,
            ])
            ->assertJsonStructure(['lead_id']);

        $this->assertDatabaseHas('leads', [
            'id' => $response->json('lead_id'),
            'first_name' => 'Ivan',
            'last_name' => 'Petrov',
            'phone' => '+79991234567',
            'amount' => 10000,
        ]);

        $this->assertSame(1, Lead::query()->count());

        Queue::assertPushed(DeliverLeadToPartner::class, 2);
    }

    public function test_it_rejects_invalid_lead_data(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/leads', [
            'first_name' => '',
            'last_name' => '',
            'birth_date' => 'not-a-date',
            'phone' => '',
            'amount' => 0,
            'term' => 0,
        ]);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'birth_date',
            'phone',
            'amount',
            'term',
            'passport',
            'address_registration',
            'address_residence',
            'sub_ids',
        ]);

        $this->assertDatabaseCount('leads', 0);

        Queue::assertNothingPushed();
    }

    public function test_it_creates_lead_without_queuing_delivery_when_no_active_partners_exist(): void
    {
        Queue::fake();

        Partner::factory()->create(['is_active' => false]);

        $response = $this->postJson('/api/leads', $this->validLeadData());

        $response
            ->assertAccepted()
            ->assertJson([
                'message' => 'Lead accepted',
                'deliveries_queued' => 0,
            ]);

        $this->assertDatabaseCount('leads', 1);

        Queue::assertNothingPushed();
    }

    private function validLeadData(): array
    {
        return [
            'first_name' => 'Ivan',
            'last_name' => 'Petrov',
            'birth_date' => '1990-05-15',
            'phone' => '+79991234567',
            'amount' => 10000,
            'term' => 12,
            'passport' => [
                'series' => '1234',
                'number' => '567890',
                'issued_by' => 'Test department',
                'issued_date' => '2015-06-10',
                'division_code' => '123-456',
            ],
            'address_registration' => [
                'region' => 'Краснодарский край',
                'city' => 'Краснодар',
                'street' => 'Тестовая',
                'house' => '1',
            ],
            'address_residence' => [
                'region' => 'Краснодарский край',
                'city' => 'Краснодар',
                'street' => 'Тестовая',
                'house' => '1',
            ],
            'sub_ids' => [
                'sub_id_1' => 'test-sub-id-1',
            ],
        ];
    }
}
