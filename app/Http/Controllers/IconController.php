<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IconController extends Controller
{
    public function index()
    {
        $files = Storage::disk('local')->files('tools');
        return view('icons.index', compact('files'));
    }
}
