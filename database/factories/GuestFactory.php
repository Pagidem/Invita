<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ci' => $this->faker->unique()->numerify('#########'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            
            'phone' => $this->faker->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'invitations' => $this->faker->numberBetween(1, 5),

            'notes' => $this->faker->sentence(),

        ];
    }
}
