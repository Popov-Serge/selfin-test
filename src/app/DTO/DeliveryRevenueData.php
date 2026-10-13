<?php

namespace App\DTO;

/**
 * Data required to register revenue for a lead delivery.
 *
 * @property-read int $deliveryId
 * @property-read int $webhookEventId
 * @property-read int $amount
 * @property-read string $currency
 */
final readonly class DeliveryRevenueData
{
    public function __construct(
        public int $deliveryId,
        public int $webhookEventId,
        public int $amount,
        public string $currency = 'RUB',
    ) {}
}
