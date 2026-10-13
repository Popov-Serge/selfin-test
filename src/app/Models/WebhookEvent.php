<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $partner_id
 * @property int|null $delivery_id
 * @property string $request_id
 * @property string|null $event_id
 * @property string|null $status
 * @property int|null $amount Сумма в копейках
 * @property array<string, mixed> $payload
 * @property string $payload_hash
 * @property string $processing_result
 * @property Carbon $received_at
 * @property Carbon|null $processed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Partner $partner
 * @property-read LeadDelivery|null $delivery
 */
class WebhookEvent extends Model
{
    protected $fillable = [
        'partner_id',
        'delivery_id',
        'request_id',
        'event_id',
        'status',
        'amount',
        'payload',
        'payload_hash',
        'processing_result',
        'received_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(LeadDelivery::class, 'delivery_id');
    }
}
