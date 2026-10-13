<?php

namespace App\DTO;

/**
 * Data transfer object containing a partner webhook payload.
 *
 * @property-read string $requestId Partner's request identifier.
 * @property-read string $status Delivery status reported by the partner.
 * @property-read string $amount Monetary amount in the original decimal representation.
 */
final class PartnerWebhookData
{
    public function __construct(
        public string $requestId,
        public string $status,
        public string $amount,
    ) {}
}
