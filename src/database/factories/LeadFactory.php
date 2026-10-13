<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'patronymic' => null,
            'birth_date' => fake()->date(),
            'phone' => '+7999'.fake()->numerify('#######'),
            'email' => fake()->safeEmail(),
            'amount' => 15000000,
            'term' => 12,
            'payload' => [],
        ];
    }
}
