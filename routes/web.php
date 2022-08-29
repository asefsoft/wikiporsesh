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

DB::listen(function ($query) {
    if(isset($GLOBALS['STAT_QUERY_COUNT']) && isset($GLOBALS['STAT_QUERY_TIME'])) {
        $GLOBALS['STAT_QUERY_COUNT'] ++;
        $GLOBALS['STAT_QUERY_TIME'] += $query->time;
    }
    else {
        $GLOBALS['STAT_QUERY_COUNT'] = 0;
        $GLOBALS['STAT_QUERY_TIME'] = 0;
    }

    if ($GLOBALS['auth_checking'] ?? false) {
        return;
    }

    if (request()->has('log_queries') || $query->time > 1500) {

        $GLOBALS['auth_checking'] = true;
        $user                     = auth()->check() ? 'user: ' . auth()->user()->name . ', ' : '';
        //todo add this
        $bot_name                 = "";//Tools::is_bot() ? sprintf("Bot: %s ", Tools::user_agent()) : '';
        $GLOBALS['auth_checking'] = false;
        $GLOBALS['STAT_QUERY_COUNT_SLOW'] ++;

        $total_binding = count($query->bindings) > 10 ? sprintf("total bindings: %s\n",
            count($query->bindings)) : '';

        $q = sprintf("Slow Query: %s%s%s\n%s%s --- %s \n--- time: %s ms", $bot_name, $user,
            rawurldecode(request()->fullUrl()), $total_binding, strLimit($query->sql, 300),
            strLimit(print_r(array_slice($query->bindings, 0, 30), true), 300), $query->time);

        Log::warning($q);
    }
});
