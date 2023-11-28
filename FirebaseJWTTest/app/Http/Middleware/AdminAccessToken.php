<?php

namespace App\Http\Middleware;

use App\Http\Services\JWTService;
use Closure;
use Exception;
use Illuminate\Http\Request;

class AdminAccessToken
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
            $access_token = $request->bearerToken();

            if ($access_token === null || empty($access_token)) {
                return response()->json(['message' => 'Error, access_token not found.'], 401);
            }
    
            $tokenInfo = $this->JWTService->verify($access_token);

            if ($tokenInfo->token_type !== 'access') {
                return response()->json(['message' => 'Error, not access token.'], 401);
            }

            if ($tokenInfo->member_type !== 'admin') {
                return response()->json(['message' => 'Error, not admin access token.'], 401);
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
