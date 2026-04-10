<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Stream;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StreamResource;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Stream\StoreStreamRequest;
use App\Http\Requests\Stream\UpdateStreamRequest;

class StreamController extends Controller
{
    public function index()
    {
        $streams = Stream::with(['user', 'categories'])
            ->withCount(['comments', 'reactions'])
            ->latest()
            ->paginate(10);

        return StreamResource::collection($streams);
    }

  






}
