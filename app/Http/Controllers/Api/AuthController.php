<?php

namespace App\Http\Controllers\Api;

use App\Helpers\UserHelper;
use App\Http\Controllers\Controller;
use App\Models\OauthAccessToken;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validation_fields  =   [
            'email'         => 'required|email|unique:users',
            'name'          => 'required',
            'password'      => ['required', 'string', 'min:6', 'confirmed'],
        ];
        $customMessages = [
            'unique' => 'This :attribute is already taken.'
        ];
        $validator     =  $this->getValidationFactory()->make($request->all(),$validation_fields,$customMessages);
        if($validator->fails()) {
            $messages   =   [];
            foreach ($validator->messages()->getMessages() as $key =>   $message){
                $messages[]  =
                    $message[0];
            }
            $custom =   implode(" ",$messages);
            return response()->json([
                'status'     =>  false,
                'messages'   =>  __('messages.pls_resolve_errors'),
                'errors'     =>  $custom
            ], 200);
        }

        $user   =   User::create([
            'name'        =>   $request['name'],
            'email'             =>   $request['email'],
            'password'          =>  Hash::make($request['password']),
            'confirmed'         =>  1
        ]);
        $credentials =   [
            'email'         =>  $user->email,
            'password'      =>  $request['password'],
        ];
        auth()->attempt($credentials);
        $tokens =   OauthAccessToken::where('user_id','=',$user->id)->get();
        foreach ($tokens as $token){
            $token->delete();
        }
        $tokenResult=  $user->createToken('myApp')-> accessToken;
        $data['status']   =   true;
        $data['messages']   =   'User Successfully Registered';
        $data['access_token']   =   $tokenResult;
        $data_user = UserHelper::user_object($user,$data);


        return response()->json($data_user, 200);
    }

    public function login(Request $request)
    {
        $validation_fields  =   [
            'email'        => 'required|string',
            'password'     => 'required|string'
        ];
        $validator     =  $this->getValidationFactory()->make($request->all(),$validation_fields);
        if($validator->fails()) {
            $messages   =   [];
            foreach ($validator->messages()->getMessages() as $key =>   $message){
                $messages[]    =
                    $message[0];
            }

            $custom =   implode(" ",$messages);
            return response()->json([
                'status'     =>  false,
                'messages'   =>  __('messages.pls_resolve_errors'),
                'errors'    =>  $custom
            ], 200);
        }

        $credentials =   [
            'email'         =>  $request->email,
            'password'      =>  $request->password,
        ];
        $user       =   User::where('email','=',$request->email)->first();
        auth()->attempt($credentials);
        /*if(!empty($user) && !$user->confirmed){
            $data_user['user']  =   $user;
            $data_user['user']['profile_image']  =   !empty($user->profile_image)?url($user->profile_image):'';
            $data_user['user']['remove_ad']  =   !empty($user->remove_ad)?true:false;
            return response()->json([
                'status'     =>  false,
                'messages'   =>  __('messages.pls_confirm_email'),
                'data'       =>  $data_user
            ], 200);
        }*/
        $user   =   Auth::user();

        if (!$user){
            return response()->json([
                'status'     =>  false,
                'messages'   =>  __('messages.incorrect_credentials_entered')
            ], 200);
        }

        $tokens =   OauthAccessToken::where('user_id','=',$user->id)->get();
        foreach ($tokens as $token){
            $token->delete();
        }
        $tokenResult=  $user->createToken('myApp')-> accessToken;
        $data['status']   =   true;
        $data['messages']   =   'success';
        $data['access_token']   =   $tokenResult;
        $data_user = UserHelper::user_object($user,$data);
        return response()->json($data_user, 200);
    }


}
