<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim($request->get('q'));
        return view('player.index', compact('keyword'));
    }

}
