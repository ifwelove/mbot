<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{

    public function __construct()
    {
    }

    public function ping(Request $request)
    {
        dd([123,456,789]);
        return response('', 200);
    }
}
