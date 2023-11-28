<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class CheckUserRefreshToken
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
        $refresh_token = $request->cookie('user_refresh_token');
        $memberType = JWTAuth::decode(new Token($refresh_token))->get('member_type');

        if ($memberType !== 'user') {
            return response()->json(['message' => 'Error, not user refresh token.'], 401);
        }

        return $next($request);
    }
}
