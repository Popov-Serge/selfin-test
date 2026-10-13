<?php

namespace App\DTO;

use Carbon\CarbonInterface;

/**
 * Data required to persist a partner webhook event.
 *
 * @property-read int $partnerId
 * @property-read int $deliveryId
 * @property-read string $requestId
 * @property-read string $status
 * @property-read int $amount
 * @property-read array $payload
 * @property-read string $payloadHash
 * @property-read CarbonInterface $receivedAt
 * @property-read string $processingResult
 */
final readonly class WebhookEventData
{
    public function __construct(
        public int $partnerId,
        public int $deliveryId,
        public string $requestId,
        public string $status,
        public int $amount,
        public array $payload,
        public string $payloadHash,
        public CarbonInterface $receivedAt,
        public string $processingResult = 'received',
    ) {}
}
