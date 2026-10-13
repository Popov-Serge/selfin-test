<?php

namespace App\Services;

use App\DTO\DeliveryRevenueData;
use App\DTO\PartnerWebhookData;
use App\DTO\WebhookEventData;
use App\Models\WebhookEvent;
use App\Repositories\Contracts\DeliveryRevenueRepositoryInterface;
use App\Repositories\Contracts\LeadDeliveryRepositoryInterface;
use App\Repositories\Contracts\WebhookEventRepositoryInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PartnerWebhookService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly LeadDeliveryRepositoryInterface $deliveryRepository,
        private readonly WebhookEventRepositoryInterface $eventRepository,
        private readonly DeliveryRevenueRepositoryInterface $revenueRepository,
    ) {}

    public function handle(
        int $partnerId,
        PartnerWebhookData $data,
    ): WebhookEvent {
        return DB::transaction(function () use (
            $partnerId,
            $data,
        ): WebhookEvent {
            $delivery = $this->deliveryRepository->findByPartnerAndRequestIdForUpdate(
                $partnerId,
                $data->requestId,
            );

            if ($delivery === null) {
                throw new NotFoundHttpException('Delivery not found');
            }

            $amount = $this->toMinorUnits($data->amount);
            $payload = [
                'request_id' => $data->requestId,
                'status' => $data->status,
                'amount' => $amount,
            ];

            $payloadHash = hash(
                'sha256',
                json_encode($payload, JSON_THROW_ON_ERROR),
            );

            $existingEvent = $this->eventRepository->findByPartnerAndPayloadHash(
                $delivery->partner_id,
                $payloadHash,
            );

            if ($existingEvent !== null) {
                return $existingEvent;
            }

            $event = $this->eventRepository->create(
                new WebhookEventData(
                    partnerId: $delivery->partner_id,
                    deliveryId: $delivery->id,
                    requestId: $data->requestId,
                    status: $data->status,
                    amount: $amount,
                    payload: $payload,
                    payloadHash: $payloadHash,
                    receivedAt: now(),
                ),
            );

            $this->deliveryRepository->updateStatus(
                $delivery,
                $data->status,
            );

            if (
                $data->status === 'approved'
                && ! $this->revenueRepository->existsForDelivery($delivery->id)
            ) {
                $this->revenueRepository->create(
                    new DeliveryRevenueData(
                        deliveryId: $delivery->id,
                        webhookEventId: $event->id,
                        amount: $amount,
                    ),
                );
            }

            return $this->eventRepository->markProcessed($event);
        });
    }

    private function toMinorUnits(string $amount): int
    {
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException(
                'Amount must have at most two decimal places.',
            );
        }

        [$units, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $units * 100)
            + (int) str_pad($fraction, 2, '0');
    }
}
