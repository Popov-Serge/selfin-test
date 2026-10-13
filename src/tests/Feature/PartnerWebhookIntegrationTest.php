<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadDelivery;
use App\Models\Partner;
use App\Models\WebhookEvent;
use App\Services\PartnerTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerWebhookIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_request_id_returns404(): void
    {
        $partner = Partner::query()->create([
            'name' => 'Test partner',
            'slug' => 'test-partner',
            'endpoint_url' => 'https://partner.example/webhook',
            'is_active' => true,
            'timeout_seconds' => 10,
            'api_token_id' => 'test-token',
            'api_token_hash' => hash('sha256', 'test-secret'),
        ]);

        $tokenService = $this->mock(PartnerTokenService::class);

        $tokenService
            ->shouldReceive('authenticate')
            ->once()
            ->with('test-token.test-secret')
            ->andReturn($partner);

        $response = $this
            ->withHeader('Authorization', 'Bearer test-token.test-secret')
            ->postJson('/api/webhook/status', [
                'request_id' => 'unknown-request',
                'status' => 'approved',
                'amount' => '1500.00',
            ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('webhook_events', 0);
        $this->assertDatabaseCount('delivery_revenues', 0);
    }

    public function test_duplicate_approved_webhook_creates_onl_one_event_and_revenue(): void
    {
        $partner = Partner::query()->create([
            'name' => 'Test partner',
            'slug' => 'test-partner',
            'endpoint_url' => 'https://partner.example/webhook',
            'is_active' => true,
            'timeout_seconds' => 10,
            'api_token_id' => 'test-token',
            'api_token_hash' => hash('sha256', 'test-secret'),
        ]);

        $lead = Lead::query()->create([
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'patronymic' => null,
            'birth_date' => '1990-01-01',
            'phone' => '+79990000000',
            'email' => 'ivan@example.com',
            'amount' => 100000,
            'term' => 12,
            'payload' => [],
        ]);

        LeadDelivery::query()->create([
            'lead_id' => $lead->id,
            'partner_id' => $partner->id,
            'request_id' => 'req-duplicate-test',
            'status' => 'sent',
        ]);

        $tokenService = $this->mock(PartnerTokenService::class);
        $tokenService
            ->shouldReceive('authenticate')
            ->twice()
            ->with('test-token.test-secret')
            ->andReturn($partner);

        $payload = [
            'request_id' => 'req-duplicate-test',
            'status' => 'approved',
            'amount' => '1500.00',
        ];

        $firstResponse = $this
            ->withHeader('Authorization', 'Bearer test-token.test-secret')
            ->postJson('/api/webhook/status', $payload);

        $firstResponse
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $secondResponse = $this
            ->withHeader('Authorization', 'Bearer test-token.test-secret')
            ->postJson('/api/webhook/status', $payload);

        $secondResponse
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->assertSame(
            1,
            WebhookEvent::query()
                ->where('partner_id', $partner->id)
                ->count(),
        );

        $this->assertDatabaseCount('delivery_revenues', 1);

        $this->assertDatabaseHas('delivery_revenues', [
            'delivery_id' => LeadDelivery::query()
                ->where('partner_id', $partner->id)
                ->where('request_id', 'req-duplicate-test')
                ->value('id'),
            'amount' => 150000,
            'currency' => 'RUB',
        ]);

        $this->assertSame(
            $firstResponse->json('event_id'),
            $secondResponse->json('event_id'),
        );
    }
}
