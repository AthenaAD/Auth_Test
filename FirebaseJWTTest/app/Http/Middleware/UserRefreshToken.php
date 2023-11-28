<?php

namespace App\Http\Middleware;

use App\Http\Services\JWTService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserRefreshToken
{
    protected $JWTService;

    public function __construct(JWTService $JWTService)
    {
        $this->JWTService = $JWTService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $refresh_token = $request->cookie('user_refresh_token');

            if ($refresh_token === null || empty($refresh_token)) {
                // 未能从 cookie 中获取到 refresh token
                return response()->json(['message' => 'Error, refresh token not found.'], 401);
            }
    
            $tokenInfo = $this->JWTService->verify($refresh_token);

            if(Cache::get($tokenInfo->jti)){
                return response()->json(['message' => 'Error, refresh token is in blacklist.'], 401);
            }

            if ($tokenInfo->token_type !== 'refresh') {
                return response()->json(['message' => 'Error, not refresh token.'], 401);
            }

            if ($tokenInfo->member_type !== 'user') {
                return response()->json(['message' => 'Error, not user refresh token.'], 401);
            }

            return $next($request);
        } catch (Exception $e)
        {
            return response()->json([
                'error' => $e->getMessage(),
            ], 401);
        }
    }
}
