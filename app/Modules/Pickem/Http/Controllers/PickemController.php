<?php

namespace App\Modules\Pickem\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemGroup;

class PickemController extends Controller
{
    public function index()
    {
        $pickems = Pickem::with(['group', 'matchups', 'owner'])
            ->latest()
            ->paginate(20);

        $groups = PickemGroup::withCount('pickems')
            ->orderBy('name')
            ->get();

        return view('pickem.index', compact('pickems', 'groups'));
    }

    public function show(Pickem $pickem)
    {
        $pickem->load(['matchups.picks', 'comments.owner', 'group']);

        return view('pickem.show', compact('pickem'));
    }

    public function group(PickemGroup $group)
    {
        $pickems = $group->pickems()
            ->with(['matchups', 'owner'])
            ->latest()
            ->paginate(20);

        $leaderboard = $group->getLeaderboard();

        return view('pickem.group', compact('group', 'pickems', 'leaderboard'));
    }
}
