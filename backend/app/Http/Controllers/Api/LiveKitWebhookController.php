<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Stream;
use App\Models\Video;
use Agence104\LiveKit\WebhookReceiver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LiveKitWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $authHeader = $request->header('Authorization', '');

        try {
            $receiver = new WebhookReceiver(
                config('services.livekit.api_key'),
                config('services.livekit.api_secret')
            );

            $receiver->receive($rawBody, $authHeader);
        } catch (\Throwable $e) {
            Log::warning('Invalid LiveKit webhook signature', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Invalid LiveKit webhook signature.',
            ], 401);
        }

        $payload = json_decode($rawBody, true);

        if (!is_array($payload)) {
            return response()->json([
                'message' => 'Invalid webhook payload.',
            ], 422);
        }

        $event = data_get($payload, 'event');

        Log::info('LiveKit webhook received', [
            'event' => $event,
            'payload' => $payload,
        ]);

        if ($event !== 'egress_ended') {
            return response()->json([
                'message' => 'Webhook received.',
                'event' => $event,
            ]);
        }

        $egressInfo = data_get($payload, 'egressInfo')
            ?? data_get($payload, 'egress_info')
            ?? [];

        $egressId = data_get($egressInfo, 'egressId')
            ?? data_get($egressInfo, 'egress_id');

        if (!$egressId) {
            Log::warning('Missing egress id in webhook payload', [
                'payload' => $payload,
            ]);

            return response()->json([
                'message' => 'Missing egress id.',
            ], 422);
        }

        $stream = Stream::with('categories')
            ->where('recording_egress_id', $egressId)
            ->first();

        if (!$stream) {
            Log::warning('No stream found for egress webhook', [
                'egress_id' => $egressId,
            ]);

            return response()->json([
                'message' => 'No related stream found.',
            ], 404);
        }

        $error = data_get($egressInfo, 'error');
        $status = data_get($egressInfo, 'status');

        if (!empty($error)) {
            $stream->update([
                'recording_status' => 'failed',
                'recording_ended_at' => now(),
            ]);

            Log::error('LiveKit egress ended with error', [
                'stream_id' => $stream->id,
                'egress_id' => $egressId,
                'status' => $status,
                'error' => $error,
            ]);

            return response()->json([
                'message' => 'Egress ended with error.',
            ]);
        }

        $fileResult = data_get($egressInfo, 'fileResults.0')
            ?? data_get($egressInfo, 'file_results.0')
            ?? data_get($egressInfo, 'file')
            ?? [];

        $filename = data_get($fileResult, 'filename');
        $durationNs = (int) (
            data_get($fileResult, 'duration')
            ?? 0
        );
        $sizeBytes = data_get($fileResult, 'size');

        if (!$filename) {
            $stream->update([
                'recording_status' => 'failed',
                'recording_ended_at' => now(),
            ]);

            Log::warning('Missing recorded filename in egress webhook', [
                'stream_id' => $stream->id,
                'egress_id' => $egressId,
                'egress_info' => $egressInfo,
            ]);

            return response()->json([
                'message' => 'Missing recorded filename.',
            ], 422);
        }

        $videoUrl = $this->buildPublicVideoUrlFromFilename($filename);
        $durationSeconds = $durationNs > 0
            ? (int) floor($durationNs / 1_000_000_000)
            : 0;

        DB::transaction(function () use ($stream, $egressId, $videoUrl, $durationSeconds, $sizeBytes) {
            $video = Video::updateOrCreate(
                [
                    'stream_id' => $stream->id,
                ],
                [
                    'user_id' => $stream->user_id,
                    'egress_id' => $egressId,
                    'title' => $stream->title,
                    'description' => $stream->description,
                    'url' => $videoUrl,
                    'duration' => $durationSeconds,
                    'recording_status' => 'completed',
                    'size_bytes' => $sizeBytes !== null ? (int) $sizeBytes : null,
                    'recorded_at' => now(),
                ]
            );

            if ($stream->categories->isNotEmpty()) {
                $video->categories()->sync(
                    $stream->categories->pluck('id')->toArray()
                );
            }

            Comment::where('stream_id', $stream->id)
                ->whereNull('video_id')
                ->update([
                    'video_id' => $video->id,
                ]);

            $stream->update([
                'recording_status' => 'completed',
                'recording_ended_at' => now(),
            ]);
        });

        Log::info('LiveKit egress finalized successfully', [
            'stream_id' => $stream->id,
            'egress_id' => $egressId,
            'video_url' => $videoUrl,
            'duration_seconds' => $durationSeconds,
            'size_bytes' => $sizeBytes,
        ]);

        return response()->json([
            'message' => 'Webhook processed successfully.',
        ]);
    }

   private function buildPublicVideoUrlFromFilename(string $filename): string
   {
       $basename = basename($filename);
       $relativeDir = trim(
           config('services.livekit.public_recordings_dir', 'videos/recordings'),
           '/'
       );
   
       return rtrim(config('app.url'), '/') . '/storage/' . $relativeDir . '/' . $basename;
   }
}
