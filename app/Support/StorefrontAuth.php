<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;

class StorefrontAuth
{
    public const GUARD = 'web';

    public static function guard(): Guard|StatefulGuard
    {
        return Auth::guard(self::GUARD);
    }
}