<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadImageRequest;
use Illuminate\Http\JsonResponse;

class UploadImageController extends Controller
{
    public function __invoke(UploadImageRequest $request): JsonResponse
    {
        $path = $request->file('image')->store('images', 'public');

        return response()->json([
            'success' => true,
            'url' => url('/storage').'/'.$path,
        ]);
    }
}
