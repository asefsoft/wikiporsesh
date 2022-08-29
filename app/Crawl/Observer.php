<?php


namespace App\Crawl;

use App\Article\Factory\ArticleUrlFactory;
use App\Jobs\ProcessCrawledUrl;
use App\Models\Url;
use App\Tools\Tools;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlUrl;


class Observer extends MyCrawlObserver {

    static int $count = 0;
    static int $videoCount = 0;
    static int $videoSaved = 0;

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


        // save url and its content into db
        $content = $response->getBody();
        $crawledUrlDB   = Url::saveNewUrl($url, $content);

        Observer::$count ++;

        // emit the 'url crawled' event
        if ($urlValidator->isValidArticleUrl()) {
            //event(new UrlCrawled($cr_url,$crawler,false,true,false,"Crawler"));
            ProcessCrawledUrl::dispatch($crawledUrlDB);
        }

        logMe('crawl_done', sprintf("%s - %s", $url, number_format_short(Str::length($content))));

        Tools::echo(sprintf("<p>%s article saved, %s crawled: %s, url was crawled: %s times</p>",
            Observer::$videoSaved, Observer::$count, Str::limit(urldecode($url), 70), $crawledUrlDB->total_crawled));

        //        print_r(request()->all());

        //        $pending_url = $crawler->getCrawlQueue()->getFirstPendingUrl();
        //        Tools::echo(sprintf("next pending url: %s\n<br>", Str::limit($pending_url ? $pending_url->getId(): 'Nothing',60)));


        // stop if max crawls reached
        //if(request()->has('maxcrawls') && Observer::$video_count + 1 > request()->get('maxcrawls')) {
        //    Tools::echo(sprintf("Max crawls reached: %s. we will stop crawling ...", request()->get('maxcrawls')));
        //    $crawler->setCrawlQueue(new ArrayCrawlQueue());
        //}

        // don't crawl anymore if memory limit reached
        //if(Tools::is_memory_limit_reached()) {
        //    Tools::echo(sprintf("<strong>Memory limit reached!! (%s%%) Crawling stopped.</strong>", config('app.memory_limit_percent')));
        //    $crawler->setCrawlQueue(new ArrayCrawlQueue());
        //}

    }


    public function crawlFailed(
        \Psr\Http\Message\UriInterface $url, \GuzzleHttp\Exception\RequestException $requestException,
        ?\Psr\Http\Message\UriInterface $foundOnUrl = null
    ) : void {

        $url = urldecode($url);
        echo "<br/>url crawl failed: $url <br/>---------------------------<br/>\n";
        echo Str::limit(urldecode($requestException->getMessage()), 200);
        echo "\n<br/>------------------------------><br/>\n";
    }

    public function getCrawler() {
        return $this->crawler;
    }

    public function setCrawler($crawler) : void {
        $this->crawler = $crawler;
    }
}

