<?php

namespace App\Services;

use App\Models\Stream;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecordingService
{
    public function __construct(
        private LiveKitService $liveKitService
    ) {
    }

    public function ensureRoomExists(Stream $stream): array
    {
        $token = $this->makeRoomCreateToken();

        Log::info('Ensuring LiveKit room exists', [
            'stream_id' => $stream->id,
            'room_name' => $stream->room_name,
            'url' => $this->roomServiceUrl('CreateRoom'),
        ]);

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(5)
            ->post($this->roomServiceUrl('CreateRoom'), [
                'name' => $stream->room_name,
                'empty_timeout' => 60 * 30,
                'max_participants' => 100,
            ]);

        Log::info('LiveKit CreateRoom response', [
            'stream_id' => $stream->id,
            'room_name' => $stream->room_name,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        if ($response->failed()) {
            $body = $response->json();

            $message =
                data_get($body, 'msg')
                ?? data_get($body, 'message')
                ?? $response->body();

            if (!str_contains(strtolower((string) $message), 'already exists')) {
                throw new \RuntimeException($message ?: 'Failed to create room.');
            }
        }

        return $response->json() ?? [];
    }

    /**
     * Optional fallback: records only the broadcaster participant.
     */
    public function startParticipantRecording(Stream $stream, User $user): array
    {
        $token = $this->makeRoomRecordToken($stream->room_name);

        $payload = [
            'room_name' => $stream->room_name,
            'identity' => $this->liveKitService->broadcasterIdentity($user),
            'file_outputs' => [
                [
                    'file_type' => 'MP4',
                    'filepath' => $this->absoluteRecordingPath($stream),
                ],
            ],
            'preset' => 'H264_720P_30',
        ];

        Log::info('Starting participant recording', [
            'stream_id' => $stream->id,
            'room_name' => $stream->room_name,
            'identity' => $this->liveKitService->broadcasterIdentity($user),
            'filepath' => $this->absoluteRecordingPath($stream),
            'url' => $this->twirpUrl('StartParticipantEgress'),
            'payload' => $payload,
        ]);

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->connectTimeout(5)
            ->post($this->twirpUrl('StartParticipantEgress'), $payload);

        Log::info('StartParticipantEgress response', [
            'stream_id' => $stream->id,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                $response->json('msg')
                    ?? $response->json('message')
                    ?? $response->body()
                    ?? 'Failed to start recording.'
            );
        }

        $data = $response->json();

        return [
            'egress_id' => data_get($data, 'egress_id'),
            'status' => data_get($data, 'status'),
            'raw' => $data,
        ];
    }

    /**
     * Main method: records the whole room composition
     * (camera + screen share + layout), not only one participant.
     */
    public function startRoomCompositeRecording(Stream $stream): array
    {
        $token = $this->makeGlobalRoomRecordToken();

        $payload = [
            'room_name' => $stream->room_name,
            'layout' => 'speaker-dark',
            'file_outputs' => [
                [
                    'file_type' => 'MP4',
                    'filepath' => $this->absoluteRecordingPath($stream),
                ],
            ],
        ];

        Log::info('Starting room composite recording', [
            'stream_id' => $stream->id,
            'room_name' => $stream->room_name,
            'filepath' => $this->absoluteRecordingPath($stream),
            'url' => $this->twirpUrl('StartRoomCompositeEgress'),
            'payload' => $payload,
        ]);

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->connectTimeout(5)
            ->post($this->twirpUrl('StartRoomCompositeEgress'), $payload);

        Log::info('StartRoomCompositeEgress response', [
            'stream_id' => $stream->id,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                $response->json('msg')
                    ?? $response->json('message')
                    ?? $response->body()
                    ?? 'Failed to start room composite recording.'
            );
        }

        $data = $response->json();

        return [
            'egress_id' => data_get($data, 'egress_id'),
            'status' => data_get($data, 'status'),
            'raw' => $data,
        ];
    }

    public function stopRecording(string $egressId): ?array
    {
        $url = rtrim(config('services.livekit.api_url') ?? env('LIVEKIT_API_URL'), '/')
            . '/twirp/livekit.Egress/StopEgress';

        Log::info('Stopping egress recording', [
            'egress_id' => $egressId,
            'url' => $url,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->makeGlobalRoomRecordToken(),
            'Content-Type' => 'application/json',
        ])->post($url, [
            'egress_id' => $egressId,
        ]);

        $body = $response->json();

        Log::info('StopEgress response', [
            'egress_id' => $egressId,
            'status' => $response->status(),
            'body' => $body,
        ]);

        if ($response->successful()) {
            return is_array($body) ? $body : [];
        }

        $code = strtolower((string) ($body['code'] ?? ''));
        $message = strtolower((string) ($body['msg'] ?? $body['message'] ?? 'failed to stop recording'));

        $nonFatal =
            $response->status() === 404 ||
            $response->status() === 412 ||
            str_contains($message, 'egress_complete') ||
            str_contains($message, 'egress_failed') ||
            str_contains($message, 'cannot be stopped') ||
            str_contains($message, 'does not exist') ||
            $code === 'failed_precondition' ||
            $code === 'not_found';

        if ($nonFatal) {
            Log::warning('Ignoring non-fatal stop recording error.', [
                'egress_id' => $egressId,
                'status' => $response->status(),
                'code' => $body['code'] ?? null,
                'message' => $body['msg'] ?? $body['message'] ?? null,
            ]);

            return is_array($body) ? $body : [
                'egress_id' => $egressId,
                'status' => 'IGNORED',
            ];
        }

        throw new \RuntimeException($body['msg'] ?? $body['message'] ?? 'Failed to stop recording.');
    }

    public function publicRecordingUrl(Stream $stream): string
    {
        return asset('storage/' . $this->relativeRecordingPath($stream));
    }

    public function relativeRecordingPath(Stream $stream): string
    {
        return trim(config('services.livekit.public_recordings_dir', 'videos/recordings'), '/')
            . '/stream-' . $stream->id . '.mp4';
    }

    public function absoluteRecordingPath(Stream $stream): string
    {
        return rtrim(config('services.livekit.egress_mount_path', '/recordings'), '/')
            . '/stream-' . $stream->id . '.mp4';
    }

    public function extractDurationSeconds(array $egressInfo): int
    {
        $nanoseconds = (int) data_get($egressInfo, 'file_results.0.duration', 0);

        if ($nanoseconds <= 0) {
            return 0;
        }

        return (int) floor($nanoseconds / 1_000_000_000);
    }

    public function extractSizeBytes(array $egressInfo): ?int
    {
        $size = data_get($egressInfo, 'file_results.0.size');

        return $size !== null ? (int) $size : null;
    }

    public function extractFileLocation(array $egressInfo): ?string
    {
        return data_get($egressInfo, 'file_results.0.location')
            ?? data_get($egressInfo, 'file_results.0.filename');
    }

    private function twirpUrl(string $method): string
    {
        return rtrim(config('services.livekit.api_url'), '/')
            . '/twirp/livekit.Egress/' . $method;
    }

    private function roomServiceUrl(string $method): string
    {
        return rtrim(config('services.livekit.api_url'), '/')
            . '/twirp/livekit.RoomService/' . $method;
    }

    private function makeRoomCreateToken(): string
    {
        return $this->encodeJwt([
            'iss' => config('services.livekit.api_key'),
            'sub' => 'laravel-room-service',
            'nbf' => now()->subSeconds(5)->timestamp,
            'exp' => now()->addHour()->timestamp,
            'video' => [
                'roomCreate' => true,
            ],
        ]);
    }

    private function makeGlobalRoomRecordToken(): string
    {
        return $this->encodeJwt([
            'iss' => config('services.livekit.api_key'),
            'sub' => 'laravel-egress-service',
            'nbf' => now()->subSeconds(5)->timestamp,
            'exp' => now()->addHour()->timestamp,
            'video' => [
                'roomRecord' => true,
            ],
        ]);
    }

    private function makeRoomRecordToken(string $roomName): string
    {
        return $this->encodeJwt([
            'iss' => config('services.livekit.api_key'),
            'sub' => 'laravel-egress-service',
            'nbf' => now()->subSeconds(5)->timestamp,
            'exp' => now()->addHour()->timestamp,
            'video' => [
                'room' => $roomName,
                'roomRecord' => true,
            ],
        ]);
    }

    private function encodeJwt(array $payload): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];

        $signingInput = implode('.', $segments);

        $signature = hash_hmac(
            'sha256',
            $signingInput,
            (string) config('services.livekit.api_secret'),
            true
        );

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
