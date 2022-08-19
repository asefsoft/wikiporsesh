<?php

namespace App\Crawl;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;

use GuzzleHttp\Promise as P;

class MyPool extends Pool {

    public function __construct(ClientInterface $client, $requests, array $config = [])
    {
        if (!isset($config['concurrency'])) {
            $config['concurrency'] = 25;
        }

        if (isset($config['options'])) {
            $opts = $config['options'];
            unset($config['options']);
        } else {
            $opts = [];
        }

        $enable_balancer = isset($config['enable_balancer']) && $config['enable_balancer'] == true;
        $request_id = empty($config['request_id']) ? Str::random(5) : $config['request_id'];

        $iterable = P\Create::iterFor($requests);
        $requests = static function () use ($request_id, $iterable, $client, $opts, $config, $enable_balancer) {

            $orig_opts = $opts;

            foreach ($iterable as $key => $rfn) {

                // load balance
                if($enable_balancer) {
                    $opts     = $orig_opts;
                    $balancer = balancer()->getNextBalancer('all', $request_id);
                    $balancer->configGuzzleClient($rfn, $opts);
                }

                if ($rfn instanceof RequestInterface) {
                    yield $key => $client->sendAsync($rfn, $opts);
                } elseif (\is_callable($rfn)) {
                    yield $key => $rfn($opts);
                } else {
                    throw new \InvalidArgumentException('Each value yielded by the iterator must be a Psr7\Http\Message\RequestInterface or a callable that returns a promise that fulfills with a Psr7\Message\Http\ResponseInterface object.');
                }
            }
        };

        $this->each = new EachPromise($requests(), $config);
    }

    public function promise(): PromiseInterface
    {
        return $this->each->promise();
    }
}
