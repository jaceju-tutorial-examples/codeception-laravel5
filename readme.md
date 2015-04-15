# Blog Example for Codeception Learning

## Create project

```bash
laravel new player
cd player
```

[edit] `composer.json`

```json
    "require": {
        "laravel/framework": "5.0.*",
        "illuminate/html": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "~4.0",
        "phpspec/phpspec": "~2.1",
        "codeception/codeception": "~2.0.12",
        "laracasts/testdummy": "~2.0"
    },
```

Note： 2.0.11+ Support Laravel5

```bash
composer update
```

## Package setup

[edit] `config/app.php`

```php
'providers' => [

    'Illuminate\Html\HtmlServiceProvider',
],

'aliases' => [

    'Form'      => 'Illuminate\Html\FormFacade',
],
```

## Codeception setup

```bash
alias c=./vendor/bin/codecept
```

```bash
c bootstrap
```

[edit] `tests/functional.suite.yml`

Add Laravel5 module

```yaml
class_name: FunctionalTester
modules:
    enabled: [Filesystem, FunctionalHelper, Laravel5]
```

```bash
c build
```

## Spec 說明

預設：曲庫中有 10 首歌

Spec 1: 搜尋歌曲，沒有任何歌曲

1. 在上方「搜尋列」搜尋 `foo` ，沒有任何符合的歌曲
2. 頁面出現「沒有找到任何歌曲」

Spec 2: 搜尋出有效的歌曲

1. 在上方「搜尋列」搜尋 `bar` ，可以搜尋出 3 首歌曲
2. 頁面出現 3 首歌曲的基本資訊 (曲名)

Spec 3: 搜尋歌曲，選擇歌曲，並新增到播放列表 (有空的話再介紹)

1. 在上方「搜尋列」搜尋 `bar` ，可以搜尋出 3 首歌曲
2. 點選第二首歌的「加入播放列表」，並導向「播放列表」
3. 在「播放列表」可以看到一首歌，即是剛剛加入的歌曲

## Create first spec

```bash
c generate:cest functional Player
```

[edit] `tests/PlayerCest.php`

```php
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

        $I->amOnPage('/player');

        $I->fillField('Search:', 'foo');
        $I->click('Search');

        $I->seeCurrentUrlEquals('/player?q=foo');
        $I->see('沒有找到任何歌曲');
    }
}
```

```bash
c run functional
```

[edit] `app/Http/routes.php`

```php
Route::get('player', [
    'as' => 'player',
    'uses' => 'PlayerController@index'
]);
```

```bash
php artisan make:controller --plain PlayerController
```

[edit] `app/Http/Controllers/PlayerController.php`

```php
namespace App\Http\Controllers;

use App\Http\Requests;

class PlayerController extends Controller
{
    public function index()
    {
        return view('player.index');
    }
}
```

[edit] `resources/views/player/index.blade.php`

```php
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Player</title>
</head>
<body>
{!! Form::open(['method' => 'get']) !!}
    <label for="search">Search:</label>
    <input type="text" name="q" id="search"/>
    <input type="submit" value="Search"/>
{!! Form::close() !!}
</body>
</html>
```

```bash
c run functional
```

[edit] `tests/PlayerCest.php`

```
$I->amOnPage('/player');
$I->dontSee('沒有找到任何歌曲');
```

[edit] `app/Http/Controllers/PlayerController.php`

```php
    public function index(Request $request)
    {
        $keyword = trim($request->get('q'));
        return view('player.index', compact('keyword'));
    }
```

[edit] `resources/views/player/index.blade.php`

```
@if (!empty($keyword))
<p>沒有找到任何歌曲</p>
@endif
```

```bash
c run functional
```

[edit] `tests/PlayerCest.php`

```
$I->see('沒有找到任何歌曲');
$I->seeInField('Search:', 'foo');
```

[edit] `resources/views/player/index.blade.php`

```
<input type="text" name="q" id="search" value="{{ @$keyword }}"/>
```

```bash
c run functional
```

## Refactor

[edit] `tests/_support/FunctionalHelper.php`

```php
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
```

```bash
c build
```

[edit] `tests/functional/PlayerCest.php`

```php
    public function searchInvalidSongs(FunctionalTester $I)
    {
        $I->am('a guest');
        $I->wantTo('search invalid songs');

        $I->searchSongWithKeyword('foo');

        $I->see('沒有找到任何歌曲');
    }
```

```bash
c run functional
```

## Database setup

[edit] `config/database.php`

```php
    'default' => 'sqlite',
```

```
touch storage/database.sqlite
```

```
php artisan make:model Song
```

[edit] `app/Song.php`

```php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{

    protected $fillable = ['name'];

    public $timestamps = false;

}
```

[edit] `database/migrations/2015_04_15_091522_create_songs_table.php`

```php
    public function up()
    {
        Schema::create('songs', function(Blueprint $table)
        {
            $table->increments('id');
            $table->text('name');
        });
    }
```

```bash
php artisan migrate
```

```bash
sqlite3 storage/database.sqlite
```

```
sqlite> .tables
migrations       password_resets  songs            users
sqlite> .exit
```

## Initialize testing data

[edit] `tests/functional/PlayerCest.php`

```php
    public function _before(FunctionalTester $I)
    {
        $this->seedTestingData();
        $I->seeRecord('songs', ['name' => 'Bar 1']);
        $I->seeRecord('songs', ['name' => 'Bar 4']);
        $I->seeRecord('songs', ['name' => 'Bar 7']);
    }

    protected function seedTestingData()
    {
        $names = ['Foo', 'Baz', 'Qoo'];
        foreach (range(1, 10) as $j) {
            $name = in_array($j, [1, 4, 7])
                ? 'Bar'
                : $names[array_rand($names)];
            $name .= " $j";
            Song::create(['name' => $name]);
        }
    }
```

```bash
c run functional
```

Note: transaction / rollback in Laravel5 module

```bash
sqlite3 storage/database.sqlite
```

```
sqlite> select * from songs;
sqlite> .exit
```

## Search song in database

[edit] `tests/functional/PlayerCest.php`

```php
    public function searchValidSongs(FunctionalTester $I)
    {
        $I->am('guest');
        $I->wantTo('search valid songs');

        $I->searchSongWithKeyword('bar');

        $I->see('Bar 1');
        $I->see('Bar 4');
        $I->see('Bar 7');
    }
```

```bash
c run functional
```

[edit] `app/Http/Controllers/PlayerController.php`

```php
use App\Song;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim($request->get('q'));

        $songs = Song::query()->where('name', 'LIKE', "%$keyword%")->get();

        return view('player.index', compact('keyword', 'songs'));
    }
}
```

[edit] `resources/views/player/index.blade.php`

```php
@if (!empty($keyword) && 0 === count($songs))
<p>沒有找到任何歌曲</p>
@else
    <ul>
    @foreach ($songs as $song)
        <li>{{ $song->name }}</li>
    @endforeach
    </ul>
@endif
```

```bash
c run functional
```
