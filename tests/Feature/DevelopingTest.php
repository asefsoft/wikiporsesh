<?php

namespace Tests\Feature;

use App\Jobs\ProcessCrawledUrl;
use App\Models\Url;

use Tests\TestCase;

class DevelopingTest extends TestCase
{

    public function test_process_crawl()
    {
        if(isProduction())
            return self::markTestSkipped('');

        for ($i=150 ; $i<=152; $i++) {
            $url = Url::whereId($i)->first();
            dump('--- ' . $url->id);
            ProcessCrawledUrl::dispatch($url);
        }
    }
}
