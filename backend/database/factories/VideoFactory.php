<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Stream;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'stream_id' => Stream::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'url' => 'videos/test.mp4',
            'duration' => fake()->numberBetween(60, 3600),
        ];
    }
}
