<?php


namespace App\Crawl;

use App\Article\Factory\ArticleUrlFactory;
use App\Jobs\ProcessCrawledUrl;
use App\Models\Url;
use App\Tools\Tools;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlUrl;


class Observer extends MyCrawlObserver {

    static int $count = 0;
    static int $articleCount = 0;
    static int $articleSaved = 0;
    static int $willCrawl = 0;
    static int $errorsCount = 0;

    protected $crawler = null;

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null) : void {

        // redirect
        if ($response->getStatusCode() == 301 || $response->getStatusCode() == 302) {
            $redirectUrl = $response->getHeaderLine("Location");
            Tools::echo(sprintf("Redirecting to: %s", $redirectUrl));
            request()->request->add(['force_crawl' => true]);
            $this->crawler?->addToCrawlQueue(CrawlUrl::create(new Uri($redirectUrl)));

            return;
        }
        // not ok response
        elseif ($response->getStatusCode() != 200) {
            Tools::echo(sprintf("Invalid response code: %s, url: %s", $response->getStatusCode(), $url));

            return;
        }

        $urlValidator = ArticleUrlFactory::make($url);
        $isValidArticleUrl = $urlValidator->isValidArticleUrl();

        // save url and its content into db
        $content = $response->getBody();
        $crawledUrlDB  = Url::saveNewUrl($url, $content,'', $isValidArticleUrl?1:0);

        Observer::$count ++;

        // emit the 'url crawled' event
        if ($isValidArticleUrl) {
            ProcessCrawledUrl::dispatch($crawledUrlDB);
            Observer::$articleSaved++;
        }

        logMe('crawl_done', sprintf("%s %s - %s%s",
            self::$articleSaved,
            $url,
            number_format_short(Str::length($content)),
            $isValidArticleUrl ? ' IS-ARTICLE' : ''
        ));

        Tools::echo(sprintf("<p>%s article saved, %s crawled: %s, url was crawled: %s times</p>",
            Observer::$articleSaved, Observer::$count, Str::limit(urldecode($url), 70), $crawledUrlDB->total_crawled));

    }

    public function willCrawl(UriInterface $url) : void
    {
        static::$willCrawl++;
        $urlValidator = ArticleUrlFactory::make($url);

        logMe('will_crawl', sprintf("%s %s queries: %s %s", static::$willCrawl, $url,
            number_format($GLOBALS["STAT_QUERY_COUNT"]),
            $urlValidator->isValidArticleUrl() ? ' IS-ARTICLE' : ''
        ));
    }

    public function crawlFailed(
        UriInterface $url, RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ) : void {

        self::$errorsCount++;

        logMe('crawl_done',
            sprintf("%s Error on %s, '%s'",
                self::$errorsCount,
                $url, Str::limit(urldecode($requestException->getMessage()), 200)
        ));

        $url2 = urldecode($url);
        echo "url crawl failed: $url2 \n";
        echo Str::limit(urldecode($requestException->getMessage()), 200);
        echo "\n";

        $sleepDuration = max(50, 5 + (self::$errorsCount / 10));
        sleep($sleepDuration);
    }

    public function getCrawler() {
        return $this->crawler;
    }

    public function setCrawler($crawler) : void {
        $this->crawler = $crawler;
    }
}

