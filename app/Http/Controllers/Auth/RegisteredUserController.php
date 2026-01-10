<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\VbulletinService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Modules\SpamProtection\Rules\NotSpammer;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:' . User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class, new NotSpammer()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the new user
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if (Schema::hasTable('vb_users')) {
            // Check if username AND email match an existing VB account
            $vbUser = VbulletinService::getUserWithUsernameAndEmail($request->username, $request->email);
//        dd($vbUser);
            // If username and email are associated with an existing vb account
            if ($vbUser->count()) {
                // Transfer VB user information
                $user->usertitle = $vbUser[0]->usertitle;
                $user->posts_old = $vbUser[0]->posts;
                $user->post_count = 0;
                $user->reputation = $vbUser[0]->reputation;
                $user->legacy_join_date = Carbon::createFromTimestamp($vbUser[0]->joindate);
                $user->is_banned = (in_array($vbUser[0]->usergroupid, [8, 13, 29]) ? 1 : 0);
                $user->is_admin = ($vbUser[0]->usergroupid == 6 ? 1 : 0);
                $user->is_moderator = (in_array($vbUser[0]->usergroupid, [5, 7]) ? 1 : 0);

                // After creating user, make sure their password matches
                $user->email_verified_at = now();
                $user->save();
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }
}
