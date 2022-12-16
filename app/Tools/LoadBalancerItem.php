<?php

namespace App\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoadBalancerItem {

    // proxy, network interface, web proxy
    const VALID_TYPES = ['https_proxy', 'interface', 'web_proxy'];

    // in each day how many times balancer allows to be failed before disable it
    const MAX_ACCEPTABLE_FAILED_ATTEMPTS = 7;

    protected $type;
    protected $host; // for https proxy
    protected $port; // for https proxy
    protected $username; //for https proxy
    protected $password; //for https proxy
    protected $interface_ip; // interface ip
    protected $web_proxy_url; // for web proxy
    protected $https_proxy_url; // for https proxy

    protected $request_id; //
    private $balancer_id = - 1;
    private $test_url = "https://www.aparat.com/api/fa/v1/video/video/list/tagid/1";
//    private $test_url = "https://www.google.com";
    private mixed $balancer_type_id;


    public function __construct(string $type, array $options) {
        $this->setType($type);
        if (empty($options['balancer_info'])) {
            throw new \Exception('balancer info is not set');
        }
        $this->setOptions($options);
    }

    public static function isValidBalancerType($type) : bool {
        return in_array($type, self::VALID_TYPES);
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) : void {
        if ( ! self::isValidBalancerType($type)) {
            throw new \Exception('Invalid Balancer type.');
        }

        $this->type = $type;
    }

    protected function setOptions($options) {
        $balancer_info          = $options['balancer_info'];
        $this->balancer_id      = $options['balancer_id'] ?? - 1;
        $this->balancer_type_id = $options['balancer_type_id'] ?? - 1;
        switch ($this->type) {
            case 'interface':
                $this->interface_ip = $balancer_info;
                break;

            case 'https_proxy':
                $this->setHttpsProxy($balancer_info);
                break;

            case 'web_proxy':
                if ( ! Str::contains($balancer_info, 'url=')) {
                    throw new \Exception('web proxy url must have url parameter');
                }

                $this->web_proxy_url = $balancer_info;
                break;
        }
    }

    private function setHttpsProxy($https_proxy_info) {
        $info = parse_url($https_proxy_info);
        //        if (empty($info['scheme']) || $info['scheme'] !== 'https') {
        //            throw new \Exception('invalid schema is set for https proxy');
        //        }

        $this->https_proxy_url = $https_proxy_info;

        $this->host     = $info['host'] ?? null;
        $this->port     = $info['port'] ?? null;
        $this->username = $info['user'] ?? null;
        $this->password = $info['pass'] ?? null;

    }

    public function getBalancerInfo($removeBadCharacters = false) {
        $info = '';

        switch ($this->type) {
            case 'interface':
                $info = $this->getInterfaceIp();
                break;

            case 'https_proxy':
                $info = $this->getHttpsProxyUrl(true);
                break;

            case 'web_proxy':
                $info = $this->getWebProxyUrl('', true);
                break;
        }

        if($removeBadCharacters) {
            $info = str_replace(["http", "https"], "", $info);
            $info = str_replace(['.', '/', '\\', ":"], "_", $info);
        }

        return $info;
    }


    public function configGuzzleClient(&$request, array &$options, bool $skip_block = false) {

        $requested_url = $request instanceof Request ? (string)$request->getUri() : $request;

        if ($skip_block == false && $this->isFailedTooManyTimes()) {
            echo sprintf("Balancer was failed too many times and is blocked. %s", $this->getType());

            return;
        }

        if ( ! $this->isFailedTooManyTimes()) {
            $this->log($requested_url);
        }

        switch ($this->type) {
            case 'interface':
                $options['curl'] = [
                    CURLOPT_INTERFACE => $this->getInterfaceIp()
                ];
                break;

            case 'https_proxy':
                $options['proxy'] = $this->getHttpsProxyUrl();
                break;

            case 'web_proxy':
                if ($request instanceof Request) {
                    $request = $request->withUri(new Uri($this->getWebProxyUrl($requested_url)));
                }
                elseif (is_string($request)) {
                    $request = $this->getWebProxyUrl($requested_url);
                }
                else {
                    throw new \Exception('Invalid request url type in configGuzzleClient.');
                }
                break;
        }

        $this->saveLastUsedDate();

    }

    /**
     * @return mixed
     */
    public function getHttpsProxyUrl($safe = false) {
        $url = $this->https_proxy_url;

        // remove user password
        if ($safe) {
            $url = explode('@', $url);
            $url = $url[1] ?? $url[0];
        }

        return $url;
    }

    /**
     * @return mixed
     */
    public function testBalancer() {
        try {

            $this->request_id = "TEST_BALANCER";
            $crawl_url        = $this->getTestlUrl();

            $client = new Client();

            $message = $result = "";

            $options = [
                'timeout' => 10, 'verify' => false, 'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/88.0',
                ]
            ];

            $this->configGuzzleClient($crawl_url, $options, true);

            $response = $client->request('GET', $crawl_url, $options);

            $content = $response->getBody()->getContents();

            if ($response->getStatusCode() == 200) {
                $result = 'OK';

                // if balancer was blocked but now is working then unblock it
                if ($this->isFailedTooManyTimes()) {
                    $this->unblockBalancer();
                    //todo
//                    SendEmailNotification::dispatch(new LoadBalancerStateChangedNotification("Online", $this))
//                                         ->onQueue('emails');
                }

            }
            else {
                $result = $response->getStatusCode();
            }
        } catch (\Exception $exception) {

            $message = $exception->getMessage();

            if (Str::contains($message, '410 Gone')) {
                $result = "410";

                return;
            }
            elseif (Str::contains($message, '404 Not Found')) {
                $result = "404";

                return;
            }
            else {
                $result = "Failed";
            }
        } finally {
            if ($result !== 'OK') {
                $this->logFailedAttempt();
            }

            return [$result, $message];
        }
    }


    public function getWebProxyUrl($target_url, $simple = false) {
        if ($simple) {
            $url = parse_url($this->web_proxy_url);

            return ($url['host'] ?? '') . ($url['path'] ?? '');
        }

        return $this->web_proxy_url . urlencode($target_url);
    }


    public function getInterfaceIp() : string {
        return $this->interface_ip;
    }

    public function getRequestId() {
        return $this->request_id;
    }

    public function setRequestId($request_id) : void {
        $this->request_id = $request_id;
    }


    private function log($url) : void {
        $log = sprintf("%s %s rq_id: %s used for %s last bl used %s sec ago",
            str_pad($this->getType(), 11, " "),
            str_pad(Str::limit($this->getBalancerInfo(), 15, ''), 15, " "),
            str_pad($this->getRequestId(), 17), Str::limit($url, 50),
            $this->GetLastUsedDateOfBalancerInSec());

        logMe('load_balance', $log, true, false);
    }


    public function getBalancerId() {
        return $this->balancer_id;
    }

    public function GetLastUsedDateOfBalancer() {
        $cache_id = "last_all_balancer_used_{$this->getBalancerId()}_date";

        return cache()->get($cache_id);
    }

    public function GetLastUsedDateOfBalancerInSec() : string {
        $last = $this->GetLastUsedDateOfBalancer();
        if ($last instanceof Carbon) {
            return now()->diffInSeconds($last);
        }

        return 'Never';
    }

    // save date of last use of selected balancer
    private function saveLastUsedDate() : void {
        $cache_id = "last_all_balancer_used_{$this->getBalancerId()}_date";
        cache()->put($cache_id, now());
    }

    // if failed to use balancer then increase counter
    public function logFailedAttempt() {
        $cache_id     = $this->getBalancerFailedCountCacheID();
        $failed_count = cache()->increment($cache_id);
        cache()->put($cache_id, $failed_count, now()->addDays(7));

        // too many failed?
        if ($failed_count == self::MAX_ACCEPTABLE_FAILED_ATTEMPTS) {
            Log::error('Load balancer failed too many times: ' . $this->getBalancerInfo());
            //todo
//            SendEmailNotification::dispatch(new LoadBalancerStateChangedNotification("Offline", $this))
//                                 ->onQueue('emails')
//            ;
        }
    }

    public function getFailedAttemptsCount() {
        return cache()->get($this->getBalancerFailedCountCacheID(), 0);
    }

    public function isFailedTooManyTimes() : bool {
        return $this->getFailedAttemptsCount() >= self::MAX_ACCEPTABLE_FAILED_ATTEMPTS;
    }

    private function getTestlUrl() {
        return $this->test_url;
    }

    private function unblockBalancer() {
        cache()->put($this->getBalancerFailedCountCacheID(), self::MAX_ACCEPTABLE_FAILED_ATTEMPTS - 3, now()->addDays(7));
        Log::info('Load balancer is back online: ' . $this->getBalancerInfo());
    }


    public function getBalancerTypeId() : mixed {
        return $this->balancer_type_id;
    }

    public function getStatus() : string {
        return $this->isFailedTooManyTimes() ? "STOPPED" : "RUNNING";
    }

    private function getBalancerFailedCountCacheID() : string {
        return "balancer_failed_count_" . now()->toDateString() . "_" . $this->getBalancerId();
    }

    /**
     * @param string $test_url
     */
    public function setTestUrl(string $test_url): void {
        $this->test_url = $test_url;
    }
}
