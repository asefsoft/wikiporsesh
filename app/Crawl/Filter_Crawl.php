<?php


namespace App\Crawl;


use App\Article\Factory\ArticleUrlFactory;
use App\Models\Url;
use App\Tools\Tools;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlProfiles\CrawlProfile;

class Filter_Crawl extends CrawlProfile {
    static int $validCount = 0;
    static int $totalCount = 0;
    static int $totalShould = 0;
    static int $validVideoCount = 0;
    static int $validSubUrlCount = 0;
    static bool $skipMsgPrinted = false;

    /**
     * Determine if the given url should be crawled.
     *
     * @param \Psr\Http\Message\UriInterface $url
     *
     * @return bool
     */
    public function shouldCrawl(UriInterface $url): bool
    {

        $should = true;

        // force to re crawl existing urls
        $forceCrawl = request()->has('force_crawl');

        Filter_Crawl::$totalCount++;

        $validator = ArticleUrlFactory::make($url);
        $isValidSubUrl = $validator->isValidArticleSubUrl();
        $isValidArticleUrl = $validator->isValidArticleUrl();
        $isMainPageUrl = $validator->isMainUrl();
        $isUrlIgnored = $validator->isIgnoredPath();
        $isCategoryUrl = $validator->isCategoryUrl();
        $isExtraValidPath = $validator->isExtraValidPath();

        $video = null;

        $isFirstPage = Filter_Crawl::$totalCount <= 20;// todo: change to 2
        $shouldCrawl = $isCategoryUrl || $isValidArticleUrl || $isValidSubUrl || $isExtraValidPath || $isFirstPage;

        $reasonToSkip = "";

        if ($shouldCrawl && ! $isUrlIgnored) {

//            Tools::echo("was valid");

            // is sub url?
            if($isValidSubUrl)
                Filter_Crawl::$validSubUrlCount++;

            Filter_Crawl::$validCount++;

            // dont crawl a  url twice
            if($isValidArticleUrl) {
                Filter_Crawl::$validVideoCount++;

                // check if url exist. only if force crawl is not enabled
                if(!$forceCrawl) {
                    if (Url::isUrlCrawled($url, "", false, $video) &&
                        Filter_Crawl::$totalCount > 2
                    ) {
                        $reasonToSkip = "Url Already Crawled";
                        $should = false;
                    }
                }
            }

        }
        else {
            $reasonToSkip = $isUrlIgnored ? "Url Ignored" : "Invalid Url";
            $should = false;
        }

        $status = $should ? "YES" : "NO >> " . $reasonToSkip;

        logMe('crawl_filters', sprintf("%s %s - %s, total: %s", Filter_Crawl::$validCount, $status, $url, Filter_Crawl::$totalCount), false);
            //$this->print_log($should, $url, $video);

        if($should)
            Filter_Crawl::$totalShould++;

        return $should;

    }

    private function print_log(bool $should, UriInterface $url, $video = null) {
        $log = '';

        $video_info = $video instanceof VideoInfo ? sprintf("> %s, vid: %s", Str::limit($video->getTitle(),40), $video->id) : '';
        $str_url = Str::limit(urldecode($url), 50);

        if(is_simple()) {
             $log = sprintf("Should? %s > %s %s <br>", $should ? "YES" : "NO", $str_url, $video_info);
        }
        else {
            $log = sprintf("<p style='color: %s'>Should? %s (total: %s, should: %s) > %s %s</p>",
                $should ? "#28a745" : "gray",$should ? "YES" : "NO",
                number_format(Filter_Crawl::$totalCount), number_format( Filter_Crawl::$totalShould ),
                $str_url, $video_info);
        }

        if($log!='') Tools::echo($log);

    }
}
