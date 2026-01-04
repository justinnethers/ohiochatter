<?php

namespace App\Modules\Pickem\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemGroup;

class PickemAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->user()?->is_admin) {
                abort(403, 'Admin access required');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $pickems = Pickem::with(['group', 'matchups'])
            ->latest()
            ->paginate(20);

        return view('pickem.admin.index', compact('pickems'));
    }

    public function groups()
    {
        $groups = PickemGroup::withCount('pickems')
            ->orderBy('name')
            ->get();

        return view('pickem.admin.groups', compact('groups'));
    }

    public function create()
    {
        $groups = PickemGroup::orderBy('name')->get();

        return view('pickem.admin.create', compact('groups'));
    }

    public function edit(Pickem $pickem)
    {
        $pickem->load('matchups');
        $groups = PickemGroup::orderBy('name')->get();

        return view('pickem.admin.edit', compact('pickem', 'groups'));
    }
}
