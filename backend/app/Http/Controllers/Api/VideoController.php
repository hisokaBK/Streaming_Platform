<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Video;
use App\Models\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Http\Requests\Video\StoreVideoRequest;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::with(['user', 'stream', 'categories'])
            ->latest()
            ->paginate(10);

        return VideoResource::collection($videos);
    }

    public function show(Video $video): JsonResponse
    {
        $video->load(['user', 'stream', 'categories']);

        return response()->json([
            'data' => new VideoResource($video),
        ]);
    }




}
