<?php

namespace App\Services;

use App\Models\Stream;
use App\Models\User;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class LiveKitService
{
    public function broadcasterIdentity(User $user): string
    {
        return 'broadcaster-' . $user->id;
    }

    public function viewerIdentity(User $user): string
    {
        return 'viewer-' . $user->id . '-' . uniqid();
    }

    public function createBroadcasterToken(Stream $stream, User $user): string
    {
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($this->broadcasterIdentity($user))
            ->setName($user->name);

        $videoGrant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($stream->room_name)
            ->setCanPublish(true)
            ->setCanSubscribe(true)
            ->setCanPublishData(true);

        return (new AccessToken(
            env('LIVEKIT_API_KEY'),
            env('LIVEKIT_API_SECRET')
        ))
            ->init($tokenOptions)
            ->setGrant($videoGrant)
            ->toJwt();
    }

    public function createViewerToken(Stream $stream, User $user): string
    {
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($this->viewerIdentity($user))
            ->setName($user->name);

        $videoGrant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($stream->room_name)
            ->setCanPublish(false)
            ->setCanSubscribe(true)
            ->setCanPublishData(false);

        return (new AccessToken(
            env('LIVEKIT_API_KEY'),
            env('LIVEKIT_API_SECRET')
        ))
            ->init($tokenOptions)
            ->setGrant($videoGrant)
            ->toJwt();
    }
}
