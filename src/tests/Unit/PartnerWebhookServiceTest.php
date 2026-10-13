<?php

namespace Tests\Unit;

use App\DTO\DeliveryRevenueData;
use App\DTO\PartnerWebhookData;
use App\DTO\WebhookEventData;
use App\Models\LeadDelivery;
use App\Models\WebhookEvent;
use App\Repositories\Contracts\DeliveryRevenueRepositoryInterface;
use App\Repositories\Contracts\LeadDeliveryRepositoryInterface;
use App\Repositories\Contracts\WebhookEventRepositoryInterface;
use App\Services\PartnerWebhookService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PartnerWebhookServiceTest extends TestCase
{
    public function test_duplicate_webhook_does_not_create_event_or_revenue(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(
                fn (callable $callback) => $callback(),
            );

        $delivery = new LeadDelivery;
        $delivery->setAttribute('id', 10);
        $delivery->setAttribute('partner_id', 2);

        $existingEvent = new WebhookEvent;
        $existingEvent->setAttribute('id', 20);

        $deliveryRepository = $this->createMock(
            LeadDeliveryRepositoryInterface::class,
        );
        $deliveryRepository
            ->expects($this->once())
            ->method('findByPartnerAndRequestIdForUpdate')
            ->with(2, 'req-1')
            ->willReturn($delivery);

        $deliveryRepository
            ->expects($this->never())
            ->method('updateStatus');

        $eventRepository = $this->createMock(
            WebhookEventRepositoryInterface::class,
        );

        $eventRepository
            ->expects($this->once())
            ->method('findByPartnerAndPayloadHash')
            ->with(
                2,
                hash(
                    'sha256',
                    json_encode(
                        [
                            'request_id' => 'req-1',
                            'status' => 'approved',
                            'amount' => 150000,
                        ],
                        JSON_THROW_ON_ERROR,
                    ),
                ),
            )
            ->willReturn($existingEvent);

        $eventRepository
            ->expects($this->never())
            ->method('create');

        $eventRepository
            ->expects($this->never())
            ->method('markProcessed');

        $revenueRepository = $this->createMock(
            DeliveryRevenueRepositoryInterface::class,
        );

        $revenueRepository
            ->expects($this->never())
            ->method('existsForDelivery');

        $revenueRepository
            ->expects($this->never())
            ->method('create');

        $service = new PartnerWebhookService(
            $deliveryRepository,
            $eventRepository,
            $revenueRepository,
        );

        $result = $service->handle(
            2,
            new PartnerWebhookData(
                requestId: 'req-1',
                status: 'approved',
                amount: '1500.00',
            ),
        );

        $this->assertSame($existingEvent, $result);
    }

    public function test_approved_webhook_creates_revenue(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(
                fn (callable $callback) => $callback(),
            );

        $delivery = new LeadDelivery;
        $delivery->setAttribute('id', 10);
        $delivery->setAttribute('partner_id', 2);

        $event = new WebhookEvent;
        $event->setAttribute('id', 20);

        $deliveryRepository = $this->createMock(
            LeadDeliveryRepositoryInterface::class,
        );

        $deliveryRepository
            ->expects($this->once())
            ->method('findByPartnerAndRequestIdForUpdate')
            ->with(2, 'req-2')
            ->willReturn($delivery);

        $deliveryRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($delivery, 'approved')
            ->willReturn($delivery);

        $eventRepository = $this->createMock(
            WebhookEventRepositoryInterface::class,
        );

        $eventRepository
            ->expects($this->once())
            ->method('findByPartnerAndPayloadHash')
            ->willReturn(null);

        $eventRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(
                fn (WebhookEventData $data): bool => $data->partnerId === 2
                    && $data->deliveryId === 10
                    && $data->requestId === 'req-2'
                    && $data->status === 'approved'
                    && $data->amount === 150000,
            ))
            ->willReturn($event);

        $eventRepository
            ->expects($this->once())
            ->method('markProcessed')
            ->with($event)
            ->willReturn($event);

        $revenueRepository = $this->createMock(
            DeliveryRevenueRepositoryInterface::class,
        );

        $revenueRepository
            ->expects($this->once())
            ->method('existsForDelivery')
            ->with(10)
            ->willReturn(false);

        $revenueRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(
                fn (DeliveryRevenueData $data): bool => $data->deliveryId === 10
                    && $data->webhookEventId === 20
                    && $data->amount === 150000,
            ));

        $service = new PartnerWebhookService(
            $deliveryRepository,
            $eventRepository,
            $revenueRepository,
        );

        $result = $service->handle(
            2,
            new PartnerWebhookData(
                requestId: 'req-2',
                status: 'approved',
                amount: '1500.00',
            ),
        );

        $this->assertSame($event, $result);
    }

    public function test_rejected_webhook_does_not_create_revenue(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(
                fn (callable $callback) => $callback(),
            );

        $delivery = new LeadDelivery;
        $delivery->setAttribute('id', 10);
        $delivery->setAttribute('partner_id', 2);

        $event = new WebhookEvent;
        $event->setAttribute('id', 30);

        $deliveryRepository = $this->createMock(
            LeadDeliveryRepositoryInterface::class,
        );

        $deliveryRepository
            ->expects($this->once())
            ->method('findByPartnerAndRequestIdForUpdate')
            ->with(2, 'req-3')
            ->willReturn($delivery);

        $deliveryRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($delivery, 'rejected')
            ->willReturn($delivery);

        $eventRepository = $this->createMock(
            WebhookEventRepositoryInterface::class,
        );

        $eventRepository
            ->expects($this->once())
            ->method('findByPartnerAndPayloadHash')
            ->willReturn(null);

        $eventRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($event);

        $eventRepository
            ->expects($this->once())
            ->method('markProcessed')
            ->with($event)
            ->willReturn($event);

        $revenueRepository = $this->createMock(
            DeliveryRevenueRepositoryInterface::class,
        );

        $revenueRepository
            ->expects($this->never())
            ->method('existsForDelivery');

        $revenueRepository
            ->expects($this->never())
            ->method('create');

        $service = new PartnerWebhookService(
            $deliveryRepository,
            $eventRepository,
            $revenueRepository,
        );

        $result = $service->handle(
            2,
            new PartnerWebhookData(
                requestId: 'req-3',
                status: 'rejected',
                amount: '1500.00',
            ),
        );

        $this->assertSame($event, $result);
    }

    public function test_unknown_request_id_throws_not_found_exception(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(
                fn (callable $callback) => $callback(),
            );

        $deliveryRepository = $this->createMock(
            LeadDeliveryRepositoryInterface::class,
        );

        $deliveryRepository
            ->expects($this->once())
            ->method('findByPartnerAndRequestIdForUpdate')
            ->with(2, 'unknown-request')
            ->willReturn(null);

        $deliveryRepository
            ->expects($this->never())
            ->method('updateStatus');

        $eventRepository = $this->createMock(
            WebhookEventRepositoryInterface::class,
        );

        $eventRepository
            ->expects($this->never())
            ->method('findByPartnerAndPayloadHash');

        $eventRepository
            ->expects($this->never())
            ->method('create');

        $eventRepository
            ->expects($this->never())
            ->method('markProcessed');

        $revenueRepository = $this->createMock(
            DeliveryRevenueRepositoryInterface::class,
        );

        $revenueRepository
            ->expects($this->never())
            ->method('existsForDelivery');

        $revenueRepository
            ->expects($this->never())
            ->method('create');

        $service = new PartnerWebhookService(
            $deliveryRepository,
            $eventRepository,
            $revenueRepository,
        );

        $this->expectException(NotFoundHttpException::class);

        $service->handle(
            2,
            new PartnerWebhookData(
                requestId: 'unknown-request',
                status: 'approved',
                amount: '1500.00',
            ),
        );
    }

    public function test_invalid_amount_does_not_create_event_or_revenue(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(
                fn (callable $callback) => $callback(),
            );

        $delivery = new LeadDelivery;
        $delivery->setAttribute('id', 10);
        $delivery->setAttribute('partner_id', 2);

        $deliveryRepository = $this->createMock(
            LeadDeliveryRepositoryInterface::class,
        );

        $deliveryRepository
            ->expects($this->once())
            ->method('findByPartnerAndRequestIdForUpdate')
            ->with(2, 'req-invalid')
            ->willReturn($delivery);

        $deliveryRepository
            ->expects($this->never())
            ->method('updateStatus');

        $eventRepository = $this->createMock(
            WebhookEventRepositoryInterface::class,
        );

        $eventRepository
            ->expects($this->never())
            ->method('findByPartnerAndPayloadHash');

        $eventRepository
            ->expects($this->never())
            ->method('create');

        $revenueRepository = $this->createMock(
            DeliveryRevenueRepositoryInterface::class,
        );

        $revenueRepository
            ->expects($this->never())
            ->method('create');

        $service = new PartnerWebhookService(
            $deliveryRepository,
            $eventRepository,
            $revenueRepository,
        );

        $this->expectException(\InvalidArgumentException::class);

        $service->handle(
            2,
            new PartnerWebhookData(
                requestId: 'req-invalid',
                status: 'approved',
                amount: '1500.999',
            ),
        );
    }

    public function test_approved_webhook_does_not_create_duplicate_revenue(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(
                fn (callable $callback) => $callback(),
            );

        $delivery = new LeadDelivery;
        $delivery->setAttribute('id', 10);
        $delivery->setAttribute('partner_id', 2);

        $event = new WebhookEvent;
        $event->setAttribute('id', 40);

        $deliveryRepository = $this->createMock(
            LeadDeliveryRepositoryInterface::class,
        );

        $deliveryRepository
            ->expects($this->once())
            ->method('findByPartnerAndRequestIdForUpdate')
            ->with(2, 'req-4')
            ->willReturn($delivery);

        $deliveryRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with($delivery, 'approved')
            ->willReturn($delivery);

        $eventRepository = $this->createMock(
            WebhookEventRepositoryInterface::class,
        );

        $eventRepository
            ->expects($this->once())
            ->method('findByPartnerAndPayloadHash')
            ->willReturn(null);

        $eventRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($event);

        $eventRepository
            ->expects($this->once())
            ->method('markProcessed')
            ->with($event)
            ->willReturn($event);

        $revenueRepository = $this->createMock(
            DeliveryRevenueRepositoryInterface::class,
        );

        $revenueRepository
            ->expects($this->once())
            ->method('existsForDelivery')
            ->with(10)
            ->willReturn(true);

        $revenueRepository
            ->expects($this->never())
            ->method('create');

        $service = new PartnerWebhookService(
            $deliveryRepository,
            $eventRepository,
            $revenueRepository,
        );

        $result = $service->handle(
            2,
            new PartnerWebhookData(
                requestId: 'req-4',
                status: 'approved',
                amount: '1500.00',
            ),
        );

        $this->assertSame($event, $result);
    }
}
