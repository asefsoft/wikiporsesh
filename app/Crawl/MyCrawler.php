<?php


namespace App\Crawl;


use App\Tools\Tools;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlUrl;

class MyCrawler extends Crawler
{

    protected bool $enableBalancer = false;
    protected string $requestId = '';


    public static array $CRAWLED_ARTICLES = [];

    public function __construct(Client $client, int $concurrency = 10)
    {
        parent::__construct( $client,  $concurrency = 10);

        $this->crawlObservers = new MyCrawlObserverCollection($this);

    }

    public static function doCrawl(string $url) {
        Tools::init_output_flushing();

        $myobs = new Observer();
        $myFilter = new Filter_Crawl();

        $time = new Carbon();
        $request = request();

        $maxDepth = $request->has('just_requested_url') ? 0 : ifProduction(2, 3);

        $options = $request->has('cookies') ? ['cookies'=>$request->get('cookies')]  : [];


        $crawler = MyCrawler::create($options)
                            ->setCrawlObserver($myobs)
                            ->setConcurrency(1)
                            ->setMaximumDepth($maxDepth)
                            ->setCrawlProfile($myFilter)
                            ->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0')
                            ->setDelayBetweenRequests(ifProduction(2000 , 400))
                            ->ignoreRobots()
                            //->enableBalancer()
                            ->setTotalCrawlLimit(\request()->get('maxcrawls', ifProduction(100,5000)));

//        dump($crawler);

        $hasContent = false;
        if($request->has('content') && $request->get('content')!=null) {
            $hasContent = true;
            static::crawl_by_content($crawler, $request->get('content'));
        }

//        Tools::echo("starting... " . $url);

        $crawler->requestId = Str::random(5);
        $log = sprintf("Crawl with rq_id: %s started for %s %s",
            $crawler->requestId, $url, $hasContent ? 'content' : ''
        );

        logMe('load_balance', $log);

        $crawler->startCrawling($url);

        $diff = $time->diffInSeconds(now());

        if ($diff>60)
            $diff = CarbonInterval::seconds($diff)->cascade()->forHumans();
        else
            $diff =sprintf("%s seconds.",$diff);


        Tools::echo(sprintf("<br>\n%s article saved, %s total urls found, %s urls was valid, %s urls was article, %s url was sub url, and <strong>%s</strong> urls crawled<br>\n
<p style='direction: rtl;text-align: left'>total crawl time %s</p>", Observer::$videoSaved,
            Filter_Crawl::$totalCount, Filter_Crawl::$validCount, Filter_Crawl::$validVideoCount,
            Filter_Crawl::$validSubUrlCount, Observer::$count, $diff));

        if($request->has('is_google_search'))
            Log::info(sprintf("google_search_result: %s article saved, %s urls was article, and %s urls crawled for '%s' in %s",
                Observer::$videoSaved,
                Filter_Crawl::$validVideoCount,
                Observer::$count, $request->get('term'), $diff));

        static::reset_stats();
//        return view('crawl.index');
    }

    private static function crawl_by_content(MyCrawler $crawler, $content) {

            preg_match_all('(https:'.
                           '((//)|(\\\\))+[\w\d:#@%/;$()~_?\+-=\\\.&]*)',
                $content, $matches, PREG_PATTERN_ORDER);

            if($matches[0] ?? count($matches[0])) {
                foreach ($matches[0] as $url) {
                    $crawler->addToCrawlQueue(CrawlUrl::create(new Uri($url)));
                }
            }

    }

    protected static function reset_stats(): void {
        Filter_Crawl::$totalCount = 0;
        Filter_Crawl::$validCount = 0;
        Filter_Crawl::$totalShould = 0;
        Filter_Crawl::$validVideoCount = 0;
        Filter_Crawl::$validSubUrlCount = 0;
        Observer::$videoSaved = 0;
        Observer::$videoCount = 0;
        Observer::$count = 0;
        MyCrawler::$CRAWLED_ARTICLES = [];
    }

    public function isEnableBalancer() : bool {
        return $this->enableBalancer;
    }

    // enable load balancer to use it for dispatch requests
    public function enableBalancer() : static {
        $this->enableBalancer = true;
        return $this;
    }

    public function disableBalancer() : static {
        $this->enableBalancer = false;
        return $this;
    }

    protected function startCrawlingQueue(): void
    {

        while ($this->crawlQueue->hasPendingUrls() && $this->reachedCrawlLimits() === false) {

            $pool = new MyPool($this->client, $this->getCrawlRequests(), [
                'concurrency' => $this->concurrency,
                'options' => $this->client->getConfig(),
                'fulfilled' => new $this->crawlRequestFulfilledClass($this),
                'rejected' => new $this->crawlRequestFailedClass($this),
                'enable_balancer' => $this->isEnableBalancer(),
                'request_id' => $this->requestId
            ]);

            $promise = $pool->promise();

            $promise->wait();
        }
    }

    public function setCrawlObservers(array $crawlObservers): Crawler
    {
        $this->crawlObservers = new MyCrawlObserverCollection($this, $crawlObservers);

        return $this;
    }
}