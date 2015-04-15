<?php

use App\Song;

class PlayerCest
{
    public function _before(FunctionalTester $I)
    {
        $this->seedTestingData();
        $I->seeRecord('songs', ['name' => 'Bar 1']);
        $I->seeRecord('songs', ['name' => 'Bar 4']);
        $I->seeRecord('songs', ['name' => 'Bar 7']);
    }

    protected function seedTestingData()
    {
        $names = ['Baz', 'Qoo'];
        foreach (range(1, 10) as $j) {
            $name = in_array($j, [1, 4, 7])
                ? 'Bar'
                : $names[array_rand($names)];
            $name .= " $j";
            Song::create(['name' => $name]);
        }
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function searchInvalidSongs(FunctionalTester $I)
    {
        $I->am('guest');
        $I->wantTo('search invalid songs');

        $I->searchSongWithKeyword('foo');

        $I->see('沒有找到任何歌曲');
    }
}
