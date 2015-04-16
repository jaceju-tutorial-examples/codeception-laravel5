# Laravel5 development with Codeception

如何將 Codeception 整合至 Laravel 5 中

## 專案說明

* 主要功能為音樂播放器
* 搜尋歌曲後可以將歌曲放到播放清單
* 可以設定播放清單內的全部歌曲為已播放
* 可以對歌曲加 1~5 顆星

## 建立 Laravel 5 專案

```bash
composer global require "laravel/installer=~1.2"
laravel new player
cd player
```

編輯 `composer.json`

```json
    "require": {
        "laravel/framework": "5.0.*",
        "illuminate/html": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "~4.0",
        "phpspec/phpspec": "~2.1",
        "codeception/codeception": "~2.0.12",
        "laracasts/generators": "~1.1"
    },
```

* Laravel 5 預設不再加入 `illuminate\html` 套件
* Codeception 2.0.11 開始支援 Laravel5
* Generator 用來加強 artisan 在建立骨架檔案上的功能

```bash
composer update
```

## 套件設定

編輯 `config/app.php`

```php
'providers' => [

    'Illuminate\Html\HtmlServiceProvider',
],

'aliases' => [

    'Form' => 'Illuminate\Html\FormFacade',
],
```

* 將原來的 `Form` 類別加入支援

編輯 `app/Providers/AppServiceProvider.php`

```php
    public function register()
    {
        if ($this->app->environment() == 'local') {
            $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
        }

        // ... original code ...
    }
```

* 讓 generators 只在 local 開發環境有作用

## 設定 Codeception

```bash
alias c=./vendor/bin/codecept
```

* 方便後續指令操作

```bash
c bootstrap
```

* `bootstrap` 會建立 `tests` 資料夾及必要的測試設定檔與類別檔

編輯 `tests/functional.suite.yml`

加入 `Laravel5` 模組

```yaml
class_name: FunctionalTester
modules:
    enabled: [Filesystem, FunctionalHelper, Laravel5]
```

```bash
c build
```

* 每次新增模組後都要重新 `build`

```bash
npm install laravel-elixir-codeception --save-dev
```

編輯 `gulpfile.js`

```js
var elixir = require('laravel-elixir');
require('laravel-elixir-codeception');

elixir(function(mix) {
    mix.less('app.less')
        .codeception(null, { testSuite: 'functional' });
});
```

* 利用 Laravel 5 的 elixir 來做自動測試，就不需要一再輸入執行的指令

```bash
gulp tdd
```

* gulp 會監控規格檔案或主要 PHP 檔案是否有修改，如果有就會執行 Codeception 的測試

## Spec 說明

預設：曲庫中有 10 首歌

規格一：搜尋歌曲，沒有任何歌曲

1. 在上方「搜尋列」搜尋 `foo` ，沒有任何符合的歌曲
2. 頁面出現「沒有找到任何歌曲」

規格二：搜尋出有效的歌曲

1. 在上方「搜尋列」搜尋 `bar` ，可以搜尋出 3 首歌曲
2. 頁面出現 3 首歌曲的基本資訊 (曲名)

## 規格一：搜尋歌曲，沒有任何歌曲

```bash
c generate:cest functional Player
```

編輯 `tests/PlayerCest.php`

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

* 將上述規格先轉換成 Codeception 的程式碼

```bash
c run functional
```

* 不需要啟動測試用的伺服器，測試執行於 Codeception 的 process 中
* Codeception 會建立 Laravel 5 的 Application 來模擬 request 和 response

編輯 `app/Http/routes.php`

```php
Route::get('player', [
    'as' => 'player',
    'uses' => 'PlayerController@index'
]);
```

* 暫時不管預設的功能，加入新的 route

```bash
php artisan make:controller --plain PlayerController
```

編輯 `app/Http/Controllers/PlayerController.php`

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

* 基本的 controller 寫法

編輯 `resources/views/player/index.blade.php`

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

* 建立搜尋表單，使用 GET 方法

```bash
c run functional
```

編輯 `tests/PlayerCest.php`

```
$I->amOnPage('/player');
$I->dontSee('沒有找到任何歌曲');
```

* 該進入播放器頁面時，預設不應該出現「沒有找到任何歌曲」

編輯 `app/Http/Controllers/PlayerController.php`

```php
    public function index(Request $request)
    {
        $keyword = trim($request->get('q'));
        return view('player.index', compact('keyword'));
    }
```

編輯 `resources/views/player/index.blade.php`

```
@if (!empty($keyword))
<p>沒有找到任何歌曲</p>
@endif
```

* 判斷是否有輸入關鍵字來決定是否顯示「沒有找到任何歌曲」

```bash
c run functional
```

編輯 `tests/PlayerCest.php`

```
$I->see('沒有找到任何歌曲');
$I->seeInField('Search:', 'foo');
```

* 輸入欄位應該保留原輸入值

編輯 `resources/views/player/index.blade.php`

```
<input type="text" name="q" id="search" value="{{ @$keyword }}"/>
```

```bash
c run functional
```

## 重構代碼

編輯 `tests/_support/FunctionalHelper.php`

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

* 將 `tests/functional/PlayerCest.php` 中可獨立之搜尋程式碼片段，加到 `FunctionalHelper` 的 `searchSongWithKeyword` 方法
* 在 helper 中可以用 `$I = $this->getModule('Laravel5');` 來取得 tester

```bash
c build
```

* 要記得重新 `build`

編輯 `tests/functional/PlayerCest.php`

```php
    public function searchInvalidSongs(FunctionalTester $I)
    {
        $I->am('a guest');
        $I->wantTo('search invalid songs');

        $I->searchSongWithKeyword('foo');

        $I->see('沒有找到任何歌曲');
    }
```

* 以 tester 的 `searchSongWithKeyword` 方法取代原來的搜尋片段

```bash
c run functional
```

## 資料庫設定

* 以 sqlite 做為測試用資料庫

編輯 `config/database.php`

```php
    'default' => 'sqlite',
```

```
touch storage/database.sqlite
```

```
php artisan make:model Song
```

* 建立 model 時，會順便建立 migration

編輯 `app/Song.php`

```php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{

    protected $fillable = ['name'];

    public $timestamps = false;

}
```

* 在 `create` 時會需要 `fillable`
* 暫時不需要 `timestamps`

編輯 `database/migrations/2015_04_15_091522_create_songs_table.php`

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

* 只新增 `name` 欄位

```bash
php artisan migrate
```

* 查看 sqlite 中是否有建立 tables

```bash
sqlite3 storage/database.sqlite
```

```
sqlite> .tables
migrations       password_resets  songs            users
sqlite> .exit
```

## 初始化測試資料

```bash
php artisan make:seed Song
```

編輯 `database/seeds/SongTableSeeder.php`

```php
    public function run()
    {
        DB::table('songs')->truncate();
        $names = ['Baz', 'Qoo'];
        foreach (range(1, 10) as $j) {
            $name = in_array($j, [1, 4, 7])
                ? 'Bar'
                : $names[ array_rand($names) ];
            $name .= " $j";
            DB::table('songs')->insert(['name' => $name]);
        }
    }
```

* 除指定的資料列外，其他列為隨時名稱

編輯 `database/seeds/DatabaseSeeder.php`

```php
    public function run()
    {
        Model::unguard();

        $this->call('SongTableSeeder');
    }
```

* 透過 `DatabaseSeeder` 類別來呼叫新建立的 `SongTableSeeder`

編輯 `tests/functional/PlayerCest.php`

```php
    public function _before(FunctionalTester $I)
    {
        $this->seedTestingData();
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
```

* 透過 Application 物件來建立 `DatabaseSeeder` ，解決相依問題

```bash
c run functional
```

```bash
sqlite3 storage/database.sqlite
```

```
sqlite> select * from songs;
sqlite> .exit
```

* 因為是在 Codeception 的 process 中執行 Laravel 5 模組
* 所以利用了 transaction / rollback 避免測試資料寫入資料庫中

## 規格二：搜尋出有效的歌曲

編輯 `tests/functional/PlayerCest.php`

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

編輯 `app/Http/Controllers/PlayerController.php`

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

* 簡單地利用 `LIKE` 來做搜尋

編輯 `resources/views/player/index.blade.php`

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

## 用 repository pattern 來重構

編輯 `app/Repositories/SongRepository.php`

```php
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
```

編輯 `app/Http/Controllers/PlayerController.php`

```php
use App\Repositories\SongRepository;

class PlayerController extends Controller
{
    public function index(Request $request, SongRepository $repository)
    {
        $keyword = trim($request->get('q'));
        $songs = $repository->search($keyword);
        return view('player.index', compact('keyword', 'songs'));
    }
}
```

```bash
c run functional
```

## 在瀏覽器上實測

```bash
php artisan db:seed
```

[browse] `http://localhost:8000/player`

