<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $users = User::where('username', 'like', "{$query}%")
            ->where('id', '!=', auth()->id())
            ->orderBy('username')
            ->take(8)
            ->get(['id', 'username', 'avatar_path']);

        return response()->json($users->map(fn($user) => [
            'id' => $user->id,
            'username' => $user->username,
            'avatar' => $user->avatar_path,
            'url' => route('profile.show', $user),
        ]));
    }
}
