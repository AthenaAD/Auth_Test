<?php

namespace App\Http\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    protected $key;
    protected $customPayload = [];

    public function __construct()
    {
        $this->key = env('JWT_SECRET');
    }

    public function createToken(string $tokenType, string $memberType, string $sub){

        $payload = $this->generateDefaultPayload($tokenType, $memberType, $sub);
  
        $jwt = JWT::encode($payload, $this->key, 'HS256');

        return $jwt;
    }

    public function verify(string $token){

        $info = JWT::decode($token, new Key($this->key, 'HS256'));

        return $info;
    }

    public function customPayload(array $customPayload)
    {
        $this->customPayload = $customPayload;

        return $this;
    }

    private function generateDefaultPayload(string $tokenType, string $memberType, string $sub)
    {
        $ttlOptions = [
            'access' => env('JWT_TTL') * 60,
            'refresh' => env('JWT_REFRESH_TTL') * 60,
        ];
        
        $ttl = $ttlOptions[$tokenType] ?? 0;

        $defaultPayload = [
            'iss' => env('APP_URL'),
            'sub' => $sub,
            'iat' => time(),
            'nbf' => time(),
            'exp' => time()+$ttl,
            'jti' => uniqid(),
            'token_type' => $tokenType,
            'member_type' => $memberType,
        ];
        
        return array_merge($defaultPayload, $this->customPayload);
    }
}