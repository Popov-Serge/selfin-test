<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $delivery_id
 * @property int $webhook_event_id
 * @property int $amount Доход в копейках
 * @property string $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LeadDelivery $delivery
 * @property-read WebhookEvent $webhookEvent
 */
class DeliveryRevenue extends Model
{
    protected $fillable = [
        'delivery_id',
        'webhook_event_id',
        'amount',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(LeadDelivery::class, 'delivery_id');
    }

    public function webhookEvent(): BelongsTo
    {
        return $this->belongsTo(WebhookEvent::class, 'webhook_event_id');
    }
}
