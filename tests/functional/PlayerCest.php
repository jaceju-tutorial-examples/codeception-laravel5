<?php

class PlayerCest
{
    public function _before(FunctionalTester $I)
    {

    }

    public function _after(FunctionalTester $I)
    {
    }

    public function searchInvalidSongs(FunctionalTester $I)
    {
        $I->am('a guest');
        $I->wantTo('search invalid songs');

        $I->searchSongWithKeyword('foo');

        $I->see('沒有找到任何歌曲');
    }
}
