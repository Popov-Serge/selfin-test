<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lead_id
 * @property int $partner_id
 * @property string|null $request_id
 * @property string $status
 * @property string|null $last_error
 * @property int $attempts_count
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Lead $lead
 * @property-read Partner $partner
 * @property-read Collection<int, WebhookEvent> $webhookEvents
 * @property-read DeliveryRevenue|null $revenue
 */
class LeadDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'partner_id',
        'request_id',
        'status',
        'last_error',
        'attempts_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts_count' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class, 'delivery_id');
    }

    public function revenue(): HasOne
    {
        return $this->hasOne(DeliveryRevenue::class, 'delivery_id');
    }
}
