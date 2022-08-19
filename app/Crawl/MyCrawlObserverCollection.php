<?php


namespace App\Crawl;


use Psr\Http\Message\ResponseInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserverCollection;
use Spatie\Crawler\CrawlUrl;

class MyCrawlObserverCollection extends CrawlObserverCollection
{
    /**
     * @var MyCrawler
     */
    private $crawler;

    public function __construct(MyCrawler $crawler, array $observers = [])
    {
        parent::__construct($observers);

        $this->crawler = $crawler;
    }

    public function crawled(CrawlUrl $crawlUrl, ResponseInterface $response) :void
    {
        foreach ($this->observers as $crawlObserver) {
            $crawlObserver->setCrawler($this->crawler);

            $crawlObserver->crawled(
                $crawlUrl->url,
                $response,
                $crawlUrl->foundOnUrl
            );
        }
    }

}
