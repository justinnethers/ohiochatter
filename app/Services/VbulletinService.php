<?php

namespace App\Services;

use App\Models\User;
use App\Models\VbUser;
use Carbon\Carbon;

class VbulletinService
{
    public static function getUserWithLogin($login)
    {
        return VbUser::query()->where([
            'username' => $login
        ])->orWhere([
            'email' => $login
        ])->get();

//        return DB::connection('vbulletin')
//            ->select('select u.*, a.filedata
//                        from vb_user u
//                        left join vb_customavatar a on u.userid = a.userid
//                        where username = ? or email = ?', [$login, $login]);
    }

    public static function getUserWithUsernameAndEmail($username, $email)
    {
        return VbUser::query()->where([
            'username' => $username,
            'email' => $email
        ])->get();
//
//        return DB::connection('vbulletin')
//            ->select('select u.*, a.filedata
//                        from vb_user u
//                        left join vb_customavatar a on u.userid = a.userid
//                        where username = ? and email = ?', [$username, $email]);
    }

    public static function createUserFromVbulletin($password, $vbUser, $isVerified = true)
    {
        $user = new User();
        $user->password = \Hash::make($password);
        $user->username = $vbUser[0]->username;
        $user->email = $vbUser[0]->email;
        $user->usertitle = $vbUser[0]->usertitle;
        $user->posts_old = $vbUser[0]->posts;
        $user->post_count = 0;
        $user->reputation = $vbUser[0]->reputation;
        $user->legacy_join_date = Carbon::createFromTimestamp($vbUser[0]->joindate);
        $user->is_banned = (in_array($vbUser[0]->usergroupid, [8, 13, 29]) ? 1 : 0);
        $user->is_admin = ($vbUser[0]->usergroupid == 6 ? 1 : 0);
        $user->is_moderator = (in_array($vbUser[0]->usergroupid, [5, 7]) ? 1 : 0);

//        if ($vbUser[0]->filedata) {
//            VbulletinService::saveAvatarFromVbulletin($user, $vbUser[0]);
//        }

        if ($isVerified) {
            $user->verified = true;
            $user->token = null;
        }

        $user->save();
    }

//    public static function saveAvatarFromVbulletin(User $user, $vbUser)
//    {
//        $path = 'avatars/' . str_random(40) . '.jpg';
//        $image = Image::make($vbUser->filedata)->fit(75)->sharpen(25);
//        Storage::put('public/' . $path, (string)$image->encode());
//        $user->avatar_path = 'storage/' . $path;
//
//        $user->save();
//    }
}
