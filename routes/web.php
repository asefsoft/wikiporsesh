<?php

use App\Crawl\MyCrawler;
use App\Http\Controllers\ArticleController;
use App\Jobs\ProcessCrawledUrl;
use App\Models\Category;
use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

// asset routes to just allow us make route urls in the app
Route::post('/static/images/{file}', function () {
    abort(403);
})->name('static.images');

Route::post('/static/videos/{file}', function () {
    abort(403);
})->name('static.videos');


Route::get('/article/{article}', [ArticleController::class, 'display'])->name('article-display');

Route::get('/test', function () {
    $article = \App\Models\Article::inRandomOrder()->whereId(35)->first();

    $translator = new \App\Translate\TranslateDesignatedArticles();
    $translator->start();
    dd($translator->getStatusText());

    $cat = Category::whereId(133)->first()->getAllSubCategories('name_fa');
    $all = Category::getAllCategoriesAndSubCategories([88], 'name_fa');
    dd($cat->pluck('name_fa'));

    $asset = new \App\Article\AssetsManager\AssetsManager($article);
    $asset->makeAllAssetsLocal();
    dd($asset);
    exit;

    //$url = \App\Models\Url::whereId(69)->first()->getFullUrl();
    //$validator = new \App\Article\Url\WikiHowArticleUrl();
    //$validator->isIgnoredPath(new \GuzzleHttp\Psr7\Uri($url));

//    $t= new \App\Translate\ManualGoogleApiTranslator();
//    $t->translate('this book was aesome');
//    exit;

    //$queueUrls = extractFailedCrawlUrls(0);
    //MyCrawler::doCrawl("https://www.wikihow.com/Category:Internet", $queueUrls);
//exit;
    for ($i=1 ; $i<=99999; $i++) {

        $url = \App\Article\AssetsManager\Url::whereId($i)->first();

        if(!empty($url)){
            echo '--- url id: ' , $url->id, "<br>\n";

            ProcessCrawledUrl::dispatch($url, true);
        }

    }

    //    Article::factory()->make();
//    $seeder = new \Database\Seeders\DatabaseSeeder();
//    $seeder->run();
});

function extractFailedCrawlUrls($ago = 4){
    $foundUrls = [];
    for($ago; $ago >= 0; $ago--) {
        $filePath = storage_path("logs/crawl_done-" . now()->subDays($ago)->format("Y-m-d") . ".log");
        if(file_exists($filePath)) {
            $content = File::get($filePath);
            preg_match_all("#Error on (.*), 'cURL error#", $content, $urls);
            $foundUrls = array_merge($foundUrls, $urls[1] ?? []);
        }
    }
    $foundUrls = array_unique($foundUrls);
//    dump($foundUrls);
    return $foundUrls;
//    File::append(storage_path() . "/logs/$fileName" . $fileNameDate . '.log',
}

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
        $GLOBALS['STAT_QUERIES'] = [];
    }

    if(! isProduction())
        $GLOBALS['STAT_QUERIES'][] = $query->sql;

    if ($GLOBALS['auth_checking'] ?? false) {
        return;
    }

    if (request()->has('log_queries') || $query->time > 500) {

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
