<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/callback',
        'alert',
        'alert2',
        'check/token',
        'olin/check/token',
        'delete-machine',
        'heroku',
        'notify',
    ];
}
