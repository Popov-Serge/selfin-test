<?php

namespace Tests\Feature;

use App\DTO\PartnerWebhookData;
use App\Models\Partner;
use App\Models\WebhookEvent;
use App\Services\PartnerTokenService;
use App\Services\PartnerWebhookService;
use Mockery;
use Tests\TestCase;

class PartnerWebhookTest extends TestCase
{
    public function test_request_without_token_returns401(): void
    {
        $response = $this->postJson('/api/webhook/status', [
            'request_id' => 'request-123',
            'status' => 'approved',
            'amount' => '100.00',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_valid_token_passes_request_to_webhook_service(): void
    {
        $partner = new Partner;
        $partner->id = 42;
        $partner->is_active = true;

        $tokenService = Mockery::mock(PartnerTokenService::class);
        $tokenService
            ->shouldReceive('authenticate')
            ->once()
            ->with('valid-token')
            ->andReturn($partner);

        $this->app->instance(PartnerTokenService::class, $tokenService);

        $event = new WebhookEvent;
        $event->id = 123;

        $webhookService = Mockery::mock(PartnerWebhookService::class);
        $webhookService
            ->shouldReceive('handle')
            ->once()
            ->with(
                42,
                Mockery::on(
                    fn (PartnerWebhookData $data): bool => $data->requestId === 'request-123'
                        && $data->status === 'approved'
                        && $data->amount === '100.00'
                ),
            )
            ->andReturn($event);

        $this->app->instance(PartnerWebhookService::class, $webhookService);

        $response = $this
            ->withHeader('Authorization', 'Bearer valid-token')
            ->postJson('/api/webhook/status', [
                'request_id' => 'request-123',
                'status' => 'approved',
                'amount' => '100.00',
            ]);

        $response->assertOk();
    }

    /**
     * @throws \Throwable
     */
    public function test_request_with_invalid_token_returns401(): void
    {
        $tokenService = Mockery::mock(PartnerTokenService::class);
        $tokenService
            ->shouldReceive('authenticate')
            ->once()
            ->with('invalid-token')
            ->andReturn(null);

        $this->app->instance(PartnerTokenService::class, $tokenService);

        $webhookService = Mockery::mock(PartnerWebhookService::class);
        $webhookService->shouldNotReceive('handle');

        $this->app->instance(PartnerWebhookService::class, $webhookService);

        $response = $this
            ->withHeader('Authorization', 'Bearer invalid-token')
            ->postJson('/api/webhook/status', [
                'request_id' => 'request-123',
                'status' => 'approved',
                'amount' => '100.00',
            ]);

        $response
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * @throws \Throwable
     */
    public function test_request_with_invalid_status_returns422(): void
    {
        $partner = new Partner;
        $partner->id = 42;
        $partner->is_active = true;

        $tokenService = Mockery::mock(PartnerTokenService::class);
        $tokenService
            ->shouldReceive('authenticate')
            ->once()
            ->with('valid-token')
            ->andReturn($partner);

        $this->app->instance(PartnerTokenService::class, $tokenService);

        $webhookService = Mockery::mock(PartnerWebhookService::class);
        $webhookService->shouldNotReceive('handle');

        $this->app->instance(PartnerWebhookService::class, $webhookService);

        $response = $this
            ->withHeader('Authorization', 'Bearer valid-token')
            ->postJson('/api/webhook/status', [
                'request_id' => 'request-123',
                'status' => 'pending',
                'amount' => '100.00',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * @throws \Throwable
     */
    public function test_request_with_invalid_amount_returns422(): void
    {
        $partner = new Partner;
        $partner->id = 42;
        $partner->is_active = true;

        $tokenService = Mockery::mock(PartnerTokenService::class);
        $tokenService
            ->shouldReceive('authenticate')
            ->once()
            ->with('valid-token')
            ->andReturn($partner);

        $this->app->instance(PartnerTokenService::class, $tokenService);

        $webhookService = Mockery::mock(PartnerWebhookService::class);
        $webhookService->shouldNotReceive('handle');

        $this->app->instance(PartnerWebhookService::class, $webhookService);

        $response = $this
            ->withHeader('Authorization', 'Bearer valid-token')
            ->postJson('/api/webhook/status', [
                'request_id' => 'request-123',
                'status' => 'approved',
                'amount' => '100.999',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }
}
