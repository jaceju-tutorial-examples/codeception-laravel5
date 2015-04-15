<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Repositories\SongRepository;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index(Request $request, SongRepository $repository)
    {
        $keyword = trim($request->get('q'));
        $songs = $repository->search($keyword);
        return view('player.index', compact('keyword', 'songs'));
    }

}
