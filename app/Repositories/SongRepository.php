<?php
namespace App\Repositories;

use App\Song;

class SongRepository
{
    /**
     * @var Song
     */
    private $song;

    function __construct(Song $song)
    {
        $this->song = $song;
    }

    public function search($keyword)
    {
        return $this->song->query()->where('name', 'LIKE', "%$keyword%")->get();
    }
}
