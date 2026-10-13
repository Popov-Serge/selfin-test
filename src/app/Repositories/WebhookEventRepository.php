<?php

namespace App\Repositories;

use App\DTO\WebhookEventData;
use App\Models\WebhookEvent;
use App\Repositories\Contracts\WebhookEventRepositoryInterface;

class WebhookEventRepository implements WebhookEventRepositoryInterface
{
    public function findByPartnerAndEventId(
        int $partnerId,
        string $eventId,
    ): ?WebhookEvent {
        return WebhookEvent::query()
            ->where('partner_id', $partnerId)
            ->where('event_id', $eventId)
            ->first();
    }

    public function create(WebhookEventData $data): WebhookEvent
    {
        $event = new WebhookEvent;

        $event->partner_id = $data->partnerId;
        $event->delivery_id = $data->deliveryId;
        $event->request_id = $data->requestId;
        $event->status = $data->status;
        $event->amount = $data->amount;
        $event->payload = $data->payload;
        $event->payload_hash = $data->payloadHash;
        $event->received_at = $data->receivedAt;
        $event->processing_result = $data->processingResult;
        $event->save();

        return $event;
    }

    public function markProcessed(WebhookEvent $event): WebhookEvent
    {
        $event->processing_result = 'processed';
        $event->processed_at = now();
        $event->save();

        return $event->refresh();
    }

    public function findByPartnerAndPayloadHash(
        int $partnerId,
        string $payloadHash,
    ): ?WebhookEvent {
        return WebhookEvent::query()
            ->where('partner_id', $partnerId)
            ->where('payload_hash', $payloadHash)
            ->first();
    }
}
