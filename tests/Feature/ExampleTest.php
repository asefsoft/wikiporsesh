<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Crawl\MyCrawler;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {

        $queueUrls = extractFailedAndPendingCrawlUrls(0);
        MyCrawler::doCrawl("https://www.wikihow.com/Category:Psychological-Health", $queueUrls);

//        $response = $this->get('/test');
//
//        $response->assertStatus(200);
    }
}
