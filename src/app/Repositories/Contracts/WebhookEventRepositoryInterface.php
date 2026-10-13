<?php

namespace App\Repositories\Contracts;

use App\DTO\WebhookEventData;
use App\Models\WebhookEvent;

interface WebhookEventRepositoryInterface
{
    public function findByPartnerAndEventId(
        int $partnerId,
        string $eventId,
    ): ?WebhookEvent;

    public function create(WebhookEventData $data): WebhookEvent;

    public function markProcessed(WebhookEvent $event): WebhookEvent;

    public function findByPartnerAndPayloadHash(
        int $partnerId,
        string $payloadHash,
    ): ?WebhookEvent;
}
