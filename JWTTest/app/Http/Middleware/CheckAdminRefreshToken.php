<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class CheckAdminRefreshToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $refresh_token = $request->cookie('admin_refresh_token');
        $memberType = JWTAuth::decode(new Token($refresh_token))->get('member_type');

        if ($memberType !== 'admin') {
            return response()->json(['message' => 'Error, not admin refresh token.'], 401);
        }

        return $next($request);
    }
}
