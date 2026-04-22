<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('stream.{streamId}', function ($user, $streamId) {
    return !is_null($user);
});

Broadcast::channel('user.{userId}.notifications', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
