<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $endpoint_url
 * @property bool $is_active
 * @property string|null $api_token_hash
 * @property int $timeout_seconds
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, LeadDelivery> $deliveries
 */
class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'endpoint_url',
        'is_active',
        'api_token_id',
        'api_token_hash',
        'timeout_seconds',
    ];

    protected $hidden = [
        'api_token_id',
        'api_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'timeout_seconds' => 'integer',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(LeadDelivery::class);
    }
}
