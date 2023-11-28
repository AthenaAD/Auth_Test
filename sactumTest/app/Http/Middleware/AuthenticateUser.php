<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthenticateUser extends Middleware
{
    // protected function authenticate($request, array $guards)
    // {
    //     if (in_array('user', $guards)) {
    //         return $this->auth->guard('user')->check();
    //     }

    //     return parent::authenticate($request, $guards);
    // }

    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json([
            'msg'=> '前台未登入'
        ]));

    }
}
