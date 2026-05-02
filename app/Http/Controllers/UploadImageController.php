<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadImageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UploadImageController extends Controller
{
    public function __invoke(UploadImageRequest $request): JsonResponse
    {
        $path = $request->file('image')->store('images', 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
