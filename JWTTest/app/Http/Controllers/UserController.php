<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class UserController extends Controller
{
    public function register(Request $request){
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        $res = [
            'user' => $user,
        ];
        return response($res, 201);
    }

    /**
     * Refresh a token. (更新 JWT token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {   
        $refresh_token = $request->cookie('user_refresh_token');
        $userId = JWTAuth::decode(new Token($refresh_token))->get('sub');
        $new_access_token = auth('userGuard')->claims(['token' => 'access', 'member_type' => 'user'])->tokenById($userId);
        $new_refresh_token = JWTAuth::setToken($refresh_token)->claims(['token' => 'refresh', 'member_type' => 'user', 'exp' => Carbon::now()->addMinutes(env('JWT_Real_REFRESH_TTL'))->timestamp])->refresh(false,true);
        Cache::get('key');
        return $this->createNewToken($new_access_token, $new_refresh_token);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $data['email'])->first();

        if(!$user || !Hash::check($data['password'], $user->password)){
            return response([
                'msg' => 'incorrect username or password'
            ], 401);
        }
        $access_token = auth('userGuard')->claims(['token' => 'access', 'member_type' => 'user'])->attempt($data);
        $refresh_token = auth('userGuard')->claims(['token' => 'refresh', 'member_type' => 'user'])->setTTL(env('JWT_Real_REFRESH_TTL'))->tokenById($user->id);;

        return $this->createNewToken($access_token, $refresh_token, $user);
    }

    public function logout(Request $request)
    {
        auth('userGuard')->logout();

        $response = [
            'message' => 'user logged out'
        ];
    
        // 删除 refresh_token Cookie
        $response = response()->json($response)->withCookie(Cookie::forget('user_refresh_token'));
    
        return $response;
    }


    public function user(Request $request)
    {
        $users = User::all();
        return $users;
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($access_token, $refresh_token, $user=null)
    {
        $response = response()->json([
            'access_token' => $access_token,
            'user' => $user
        ], 201);
        $response->cookie('user_refresh_token', $refresh_token);

        return $response;
    }
}
