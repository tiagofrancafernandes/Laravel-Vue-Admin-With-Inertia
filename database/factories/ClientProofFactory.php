<?php

namespace Database\Factories;

use App\Models\ClientProof;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientProof>
 */
class ClientProofFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ClientProof>
     */
    protected $model = ClientProof::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'type' => $this->faker->randomElement(['deposit', 'payment']),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'file_path' => 'client-proofs/' . $this->faker->uuid . '.pdf',
            'status' => 'pending',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the proof is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the proof is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the proof is of type deposit.
     */
    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'deposit',
        ]);
    }

    /**
     * Indicate that the proof is of type payment.
     */
    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payment',
        ]);
    }
}
