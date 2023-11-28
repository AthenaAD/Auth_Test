<?php

namespace App\Http\Controllers;

use App\Http\Formatters\Formatter;
use App\Http\Services\JWTService;
use App\Models\BackendAdmin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class BackendAdminController extends Controller
{
    protected $JWTService;
    protected $formatter;

    public function __construct(JWTService $JWTService, Formatter $Formatter)
    {
        $this->JWTService = $JWTService;
        $this->formatter = $Formatter;
    }

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
        $access_token = $this->JWTService->customPayload(['user_name'=>$backendAdmin->name])->createToken('access', 'admin', $backendAdmin->id);
        $refresh_token = $this->JWTService->createToken('refresh', 'admin', $backendAdmin->id);

        $data = [
            'access_token'=>$access_token,
            'backendAdmin'=>$backendAdmin,
        ];
        return $this->formatter->formatResponse(true, '', '成功登入', $data, Response::HTTP_OK)->cookie('admin_refresh_token', $refresh_token);
    }

    public function refresh(Request $request)
    {   
        $refresh_token = $request->cookie('admin_refresh_token');
        $tokenInfo = $this->JWTService->verify($refresh_token);
        $new_access_token = $this->JWTService->createToken('access', 'admin', $tokenInfo->sub);
        $new_refresh_token = $this->JWTService->createToken('refresh', 'admin', $tokenInfo->sub);
        Cache::put($tokenInfo->jti, $tokenInfo->jti, env('JWT_REFRESH_TTL')*60);
        $data = [
            'access_token'=>$new_access_token,
        ];
        return $this->formatter->formatResponse(true, '', '成功換發', $data, Response::HTTP_OK)->cookie('admin_refresh_token', $new_refresh_token);
    }

    public function logout(Request $request)
    {
        $response = [
            'message' => 'user logged out'
        ];
        // 删除 refresh_token Cookie
        $refresh_token = $request->cookie('user_refresh_token');
        if ($refresh_token) {
            $tokenInfo = $this->JWTService->verify($refresh_token);
            Cache::put($tokenInfo->jti, $tokenInfo->jti, env('JWT_REFRESH_TTL')*60);
        }
        $response = response()->json($response)->withCookie(Cookie::forget('user_refresh_token'));
    
        return $response;
    }

    public function backendAdmin(Request $request)
    {
        $users = BackendAdmin::all();
        return $users;
    }
}
