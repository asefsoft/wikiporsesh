<?php

use App\Crawl\MyCrawler;
use App\Jobs\ProcessCrawledUrl;
use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/test', function () {
    //$url = \App\Models\Url::whereId(69)->first()->getFullUrl();
    //$validator = new \App\Article\Url\WikiHowArticleUrl();
    //$validator->isIgnoredPath(new \GuzzleHttp\Psr7\Uri($url));

//    $t= new \App\Translate\ManualGoogleApiTranslator();
//    $t->translate('this book was aesome');
//    exit;

    $queueUrls = extractFailedCrawlUrls(0);
    MyCrawler::doCrawl("https://www.wikihow.com/Category:Internet", $queueUrls);
exit;
    for ($i=1380 ; $i<=3000; $i++) {

        $url = \App\Models\Url::whereId($i)->first();

        if(!empty($url)){
            echo '--- url id: ' , $url->id, "<br>\n";

            ProcessCrawledUrl::dispatch($url, true);
        }

    }

    //    Article::factory()->make();
//    $seeder = new \Database\Seeders\DatabaseSeeder();
//    $seeder->run();
});

function testTranslate(){
    $translate = new TranslateClient([
        'keyFile' => json_decode(file_get_contents('c:\Users\Admin\AppData\Roaming\gcloud\application_default_credentials.json'), true)
    ]);

// Translate text from english to french.
    $result = $translate->translate('Hello world!', [
        'target' => 'fr'
    ]);

    echo $result['text'] . "\n";

// Detect the language of a string.
    $result = $translate->detectLanguage('Greetings from Michigan!');

    echo $result['languageCode'] . "\n";

// Get the languages supported for translation specifically for your target language.
    $languages = $translate->localizedLanguages([
        'target' => 'en'
    ]);

    foreach ($languages as $language) {
        echo $language['name'] . "\n";
        echo $language['code'] . "\n";
    }

// Get all languages supported for translation.
    $languages = $translate->languages();

    foreach ($languages as $language) {
        echo $language . "\n";
    }
}

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
