<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Stream;
use App\Models\Video;
use App\Models\Comment;
use App\Models\Message;
use App\Models\Reaction;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(10)->create();

        $users->each(function ($user) {
            $user->profile()->create([
                'avatar' => null,
                'background_image' => null,
                'bio' => fake()->sentence(),
            ]);
        });

        $categories = Category::factory(5)->create();

        $streams = Stream::factory(15)->make()->each(function ($stream) use ($users, $categories) {
            $stream->user_id = $users->random()->id;
            $stream->category_id = $categories->random()->id;
            $stream->save();
        });

        $videos = $streams->map(function ($stream) {
            return Video::factory()->create([
                'user_id' => $stream->user_id,
                'stream_id' => $stream->id,
                'category_id' => $stream->category_id,
            ]);
        });

        foreach ($videos as $video) {
            Comment::factory(3)->create([
                'user_id' => $users->random()->id,
                'video_id' => $video->id,
            ]);
        }

        Message::factory(20)->make()->each(function ($msg) use ($users) {
            $msg->sender_id = $users->random()->id;
            $msg->receiver_id = $users->random()->id;
            $msg->save();
        });

        foreach ($streams as $stream) {
            Reaction::factory(5)->create([
                'user_id' => $users->random()->id,
                'stream_id' => $stream->id,
            ]);
        }

        Subscription::factory(15)->make()->each(function ($sub) use ($users) {
            $sub->subscriber_id = $users->random()->id;
            $sub->streamer_id = $users->random()->id;
            $sub->save();
        });
    }
}
