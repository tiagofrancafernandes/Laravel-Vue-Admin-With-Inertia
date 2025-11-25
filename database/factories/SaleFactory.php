<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->randomFloat(2, 10, 1000);

        return [
            'sale_number' => strtoupper(Str::random(12)),
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'items' => json_encode([
                [
                    'description' => 'Sample Item',
                    'quantity' => 1,
                    'unit_price' => $total,
                ],
            ]),
            'subtotal' => $total,
            'discount' => 0,
            'total_amount' => $total,
            'status' => fake()->randomElement(['completed', 'cancelled']),
            'notes' => fake()->optional()->text(),
        ];
    }

    /**
     * Indicate that the sale is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the sale is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
