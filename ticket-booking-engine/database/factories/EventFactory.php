<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+1 month');

        return [

            'title' => fake()->randomElement([
                'Movie Night',
                'Tech Conference',
                'Theater Show',
                'Music Concert'
            ]),

            'description' => fake()->sentence(),

            'start_time' => $start,

            'end_time' => (clone $start)->modify('+2 hours'),

            'status' => 'upcoming',

            'price' => fake()->randomNumber(2),
        ];
    }
}
