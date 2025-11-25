<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientBalance>
 */
class ClientBalanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'credit_limit' => $this->faker->randomFloat(2, 0, 50000),
            'last_transaction_at' => null,
        ];
    }
}
