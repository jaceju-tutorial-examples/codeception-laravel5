<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Song;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim($request->get('q'));

        $songs = Song::query()->where('name', 'LIKE', "%$keyword%")->get();

        return view('player.index', compact('keyword', 'songs'));
    }

}
