<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class FunctionalHelper extends \Codeception\Module
{
    public function searchSongWithKeyword($keyword)
    {
        $I = $this->getModule('Laravel5');
        /* @var $I \FunctionalTester */

        $I->amOnPage('/player');
        $I->dontSee('沒有找到任何歌曲');

        $I->fillField('Search:', $keyword);
        $I->click('Search');

        $I->seeCurrentUrlEquals('/player?q=' . $keyword);
        $I->seeInField('Search:', $keyword);
    }
}
