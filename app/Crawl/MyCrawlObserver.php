<?php


namespace App\Crawl;


use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class MyCrawlObserver extends CrawlObserver
{

    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null) : void {
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null) : void {
    }

    public function willCrawl(UriInterface $url) : void
    {
    }

    public function finishedCrawling() : void
    {
    }
}
