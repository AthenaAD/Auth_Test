<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthenticateBackendAdmin extends Middleware
{
    // protected function authenticate($request, array $guards)
    // {
    //     if (in_array('backend', $guards)) {
    //         return $this->auth->guard('backend')->check();
    //     }

    //     return parent::authenticate($request, $guards);
    // }

    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json([
            'msg'=> '後台未登入'
        ]));
    }
}
