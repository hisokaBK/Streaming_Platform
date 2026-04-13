<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        $names = ['Bilal', 'Sara', 'Amine', 'Yassine', 'Mouna'];

        return [
            'user_id' => User::factory(),
            'content' => fake()->randomElement([
                fake()->randomElement($names) . ' started following you.',
                fake()->randomElement($names) . ' sent you a message.',
                fake()->randomElement($names) . ' commented on your stream.',
                fake()->randomElement($names) . ' reacted to your stream.',
            ]),
            'is_read' => fake()->boolean(30),
        ];
    }
}
