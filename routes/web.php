<?php

use App\Crawl\MyCrawler;
use App\Jobs\ProcessCrawledUrl;
use App\Models\Article;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/test', function () {
    //$url = \App\Models\Url::whereId(69)->first()->getFullUrl();
    //$validator = new \App\Article\Url\WikiHowArticleUrl();
    //$validator->isIgnoredPath(new \GuzzleHttp\Psr7\Uri($url));

    //MyCrawler::doCrawl("https://www.wikihow.com/Main-Page");
//exit;
    for ($i=150 ; $i<=400; $i++) {
        $url = \App\Models\Url::whereId($i)->first();
        dump('--- ' . $url->id);
        ProcessCrawledUrl::dispatch($url);
    }

    //    Article::factory()->make();
//    $seeder = new \Database\Seeders\DatabaseSeeder();
//    $seeder->run();
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
