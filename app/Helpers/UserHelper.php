<?php


namespace App\Helpers;


class UserHelper
{
    public static function user_object($user,$data)
    {
        $data_user['status']   =   $data['status'];
        $data_user['messages']   =   $data['messages'];
        $data_user['id']   =   $user->id;
        $data_user['name']   =   $user->name;
        $data_user['email']   =   $user->email;
        $data_user['profile_image']  =   !empty($user->profile_image)?url($user->profile_image):'';
        $data_user['forget_code']   =   $user->forget_code;
        $data_user['confirmation_code']   =   $user->confirmation_code;
        $data_user['confirmed']   =   $user->confirmed;
        $data_user['access_token']   =   $data['access_token'];

        return $data_user;
    }
}
