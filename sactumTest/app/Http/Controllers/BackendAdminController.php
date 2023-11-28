<?php

namespace App\Http\Controllers;

use App\Models\BackendAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BackendAdminController extends Controller
{
    public function register(Request $request){
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $backendAdmin = BackendAdmin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        $token = $backendAdmin->createToken('apiToken')->plainTextToken;

        $res = [
            'backendAdmin' => $backendAdmin,
            'token' => $token
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
        $token = $backendAdmin->createToken('apiToken')->plainTextToken;

        $res = [
            'user' => $backendAdmin,
            'token' => $token
        ];

        return response($res, 201);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'user logged out'
        ];
    }

    public function backendAdmin(Request $request)
    {
        $backendAdmin = BackendAdmin::all();
        return $backendAdmin;
    }
}
