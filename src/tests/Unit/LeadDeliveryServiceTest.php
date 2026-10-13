<?php

namespace Tests\Unit;

use App\Exceptions\PartnerRequestException;
use App\Models\Lead;
use App\Models\LeadDelivery;
use App\Models\Partner;
use App\Services\HttpPartnerClient;
use App\Services\LeadDeliveryService;
use App\Services\PartnerClientInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class LeadDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function makeLead(): Lead
    {
        return Lead::factory()->create();
    }

    public function makePartner(): Partner
    {
        return Partner::factory()->create();
    }

    public function test_it_throws_exception_for_client_error(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Invalid lead'], 422),
        ]);

        try {
            app(HttpPartnerClient::class)->send(
                $this->makeLead(),
                $this->makePartner(),
            );

            $this->fail('Expected PartnerRequestException was not thrown.');
        } catch (PartnerRequestException $exception) {
            $this->assertSame(422, $exception->httpStatus);
        }
    }

    public function test_it_retries_failed_delivery_successfully(): void
    {
        $lead = $this->makeLead();
        $partner = $this->makePartner();

        $client = Mockery::mock(PartnerClientInterface::class);

        $client->shouldReceive('send')
            ->once()
            ->with($lead, $partner)
            ->andThrow(new RuntimeException('Partner unavailable'));

        $client->shouldReceive('send')
            ->once()
            ->with($lead, $partner)
            ->andReturn('request-retry-123');

        $this->app->instance(PartnerClientInterface::class, $client);

        $service = app(LeadDeliveryService::class);

        try {
            $service->deliver($lead, $partner);
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Partner unavailable', $exception->getMessage());
        }

        $this->assertDatabaseHas('lead_deliveries', [
            'lead_id' => $lead->id,
            'partner_id' => $partner->id,
            'status' => 'failed',
            'attempts_count' => 1,
            'last_error' => 'Partner unavailable',
        ]);

        $delivery = $service->deliver($lead, $partner);

        $this->assertSame('sent', $delivery->status);
        $this->assertSame('request-retry-123', $delivery->request_id);
        $this->assertSame(2, $delivery->attempts_count);
        $this->assertNull($delivery->last_error);
        $this->assertNotNull($delivery->sent_at);

        $this->assertDatabaseHas('lead_deliveries', [
            'lead_id' => $lead->id,
            'partner_id' => $partner->id,
            'status' => 'sent',
            'request_id' => 'request-retry-123',
            'attempts_count' => 2,
            'last_error' => null,
        ]);
    }

    public function test_it_saves_successful_delivery(): void
    {
        $lead = $this->makeLead();
        $partner = $this->makePartner();

        LeadDelivery::factory()->create([
            'lead_id' => $lead->id,
            'partner_id' => $partner->id,
            'request_id' => 'request-existing',
            'status' => 'sent',
            'attempts_count' => 1,
            'sent_at' => now(),
        ]);

        $client = Mockery::mock(PartnerClientInterface::class);
        $client->shouldNotReceive('send');

        $this->app->instance(PartnerClientInterface::class, $client);
        $delivery = app(LeadDeliveryService::class)->deliver($lead, $partner);
        $this->assertSame('sent', $delivery->status);
        $this->assertSame('request-existing', $delivery->request_id);
        $this->assertSame(1, $delivery->attempts_count);
    }
}
