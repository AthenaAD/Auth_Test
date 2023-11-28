<?php

namespace App\Http\Controllers;

use App\Models\BackendAdmin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class BackendAdminController extends Controller
{
    public function register(Request $request){
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:backend_admins,email',
            'password' => 'required|string|confirmed'
        ]);

        $backendAdmin = BackendAdmin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        $res = [
            'backendAdmin' => $backendAdmin,
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
        $refresh_token = $request->cookie('admin_refresh_token');
        $userId = JWTAuth::decode(new Token($refresh_token))->get('sub');
        $new_access_token = auth('backendGuard')->claims(['token' => 'access', 'member_type' => 'admin'])->tokenById($userId);
        $new_refresh_token = JWTAuth::setToken($refresh_token)->claims(['token' => 'refresh', 'member_type' => 'admin', 'exp' => Carbon::now()->addMinutes(env('JWT_Real_REFRESH_TTL'))->timestamp])->refresh(false,true);
        return $this->createNewToken($new_access_token, $new_refresh_token);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $backendAdmin = BackendAdmin::where('email', $data['email'])->first();

        if(!$backendAdmin || !Hash::check($data['password'], $backendAdmin->password)){
            return response([
                'msg' => 'incorrect username or password'
            ], 401);
        }
        $access_token = auth('backendGuard')->claims(['token' => 'access', 'member_type' => 'admin'])->attempt($data);
        $refresh_token = auth('backendGuard')->claims(['token' => 'refresh', 'member_type' => 'admin'])->setTTL(env('JWT_Real_REFRESH_TTL'))->tokenById($backendAdmin->id);;

        return $this->createNewToken($access_token, $refresh_token, $backendAdmin);
    }

    public function logout(Request $request)
    {
        auth('backendGuard')->logout();

        $response = [
            'message' => 'admin logged out'
        ];
    
        // 删除 refresh_token Cookie
        $response = response()->json($response)->withCookie(Cookie::forget('admin_refresh_token'));
    
        return $response;
    }

    public function backendAdmin(Request $request)
    {
        $backendAdmin = BackendAdmin::all();
        return $backendAdmin;
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($access_token, $refresh_token, $admin=null)
    {
        $response = response()->json([
            'access_token' => $access_token,
            'admin' => $admin
        ], 201);
        $response->cookie('admin_refresh_token', $refresh_token);

        return $response;
    }
}
