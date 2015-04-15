<?php

use App\Song;

class PlayerCest
{
    public function _before(FunctionalTester $I)
    {
        $this->seedTestingData($I);
        $I->seeRecord('songs', ['name' => 'Bar 1']);
        $I->seeRecord('songs', ['name' => 'Bar 4']);
        $I->seeRecord('songs', ['name' => 'Bar 7']);
    }

    protected function seedTestingData(FunctionalTester $I)
    {
        $app = $I->getApplication();
        $seeder = $app->make('DatabaseSeeder');
        $seeder->run();
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

    public function searchValidSongs(FunctionalTester $I)
    {
        $I->am('guest');
        $I->wantTo('search valid songs');

        $I->searchSongWithKeyword('bar');

        $I->see('Bar 1');
        $I->see('Bar 4');
        $I->see('Bar 7');
    }
}
