<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $patronymic
 * @property Carbon $birth_date
 * @property string $phone
 * @property string|null $email
 * @property int $amount Сумма займа в копейках
 * @property int $term
 * @property string|null $issuing_region
 * @property string|null $issuing_region_kladr_id
 * @property string|null $issuing_city
 * @property string|null $employment_type
 * @property string|null $income
 * @property string|null $education_type
 * @property string|null $marital_status
 * @property array<string, mixed> $payload
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, LeadDelivery> $deliveries
 */
class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'patronymic',
        'birth_date',
        'phone',
        'email',
        'amount',
        'term',
        'issuing_region',
        'issuing_region_kladr_id',
        'issuing_city',
        'employment_type',
        'income',
        'education_type',
        'marital_status',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'amount' => 'integer',
            'term' => 'integer',
            'payload' => 'array',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(LeadDelivery::class);
    }
}
