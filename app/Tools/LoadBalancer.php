<?php

namespace App\Tools;

//Balancer requests send for crawling and ...

class LoadBalancer {

    protected $balancers = [];
    protected $total_balancers = 0;

    public function __construct() {
        $this->initBalancers();
    }

    private function initBalancers() {
        $this->balancers = [];

        $allBalancers = config('load-balancers', []);

        // iterate on all type of balancers
        foreach ($allBalancers as $type => $balancersText) {
            $balancers = json_decode($balancersText ?? []);

            // iterate on all balancers in specific type
            foreach ($balancers as $key => $bl) {
                if(!empty($bl)) {
                    $this->balancers[] = new LoadBalancerItem($type,
                        [
                            'balancer_info' => $bl,
                            'balancer_id' => count($this->balancers),
                            'balancer_type_id' => $key
                        ]);
                }
            }
        }

        $this->total_balancers = count($this->balancers);
    }


    public function getNextBalancer(string $type = 'all', $request_id = '', $isRecursive = false) : LoadBalancerItem {

        // is requesting specific type balancer or all balancer
        $is_typed_balancer = $type != 'all';

        if ($is_typed_balancer & ! LoadBalancerItem::isValidBalancerType($type)) {
            throw new \Exception('Invalid Balancer type.');
        }

        $last_used_cache_id = "last_{$type}_balancer_used";

        $last_balancer_used = cache()->get($last_used_cache_id);
        $lbs                = $this->getBalancersByType($type);

        $lb_count = count($lbs);

        if ($lb_count == 0) {
            throw new \Exception("no $type balancer is set");
        }

        // is first time or there is only one balancer then select first one
        if (is_null($last_balancer_used) || $lb_count == 1) {
            $selected_bl_id = 0;
        } else { // goto next
            $selected_bl_id = $last_balancer_used + 1;
            if ($selected_bl_id >= $lb_count) {
                $selected_bl_id = 0;
            }// back to first item
        }

        /**
         * @var $balancer LoadBalancerItem
         */
        $balancer = $lbs[$selected_bl_id];

        // if balancer is blocked then skipped it and go for next one
        if($balancer->isFailedTooManyTimes() && ! $isRecursive) {
            //logError("Balancer skipped due to high rate of failed attempt. " .
            //         $balancer->getBalancerInfo() .  " => " . $balancer->getFailedAttemptsCount());
            return $this->getNextBalancer($type, $request_id, true);
        }

        $balancer->setRequestId($request_id);

        $used_balancer_id = $is_typed_balancer ? $balancer->getBalancerTypeId() : $balancer->getBalancerId();

        cache()->put($last_used_cache_id, $used_balancer_id); // save ID of last user balance

        // save total usage of each balancer in cache for 7 days
        $total_used_cache_id = sprintf("balancer_used_%s_%s", now()->toDateString(), $balancer->getBalancerInfo(true));
        cache()->put($total_used_cache_id, cache()->increment($total_used_cache_id), now()->addDays(7));

        return $balancer;
    }

    public function getLastBalancer(string $type = 'all') : LoadBalancerItem {
        $cache_id           = "last_{$type}_balancer_used";
        $last_balancer_used = cache()->get($cache_id);
        $lbs                = $this->getBalancersByType($type);
        $lb_count           = count($lbs);
        if ($lb_count == 0) {
            throw new \Exception("no $type balancer is set");
        }

        if (is_null($last_balancer_used) || $lb_count == 1 || $last_balancer_used >= $lb_count) {
            $last_balancer_used = 0;
        }

        return $lbs[$last_balancer_used];
    }


    public function getBalancersByType($type = 'all') : array {
        if ($type == 'all') {
            return $this->balancers;
        }

        $lbs = [];
        foreach ($this->balancers as $lbl) {
            if ($lbl->getType() == $type) {
                $lbs[] = $lbl;
            }
        }

        return $lbs;
    }


    public function getTotalBalancers() : int {
        return $this->total_balancers;
    }

    public function testAllBalancers($testUrl = null) {

        foreach ($this->balancers as $key => $bl) {

            if(!empty($testUrl))
                $bl->setTestUrl($testUrl);

            $start = now();
            list($result, $message) = $bl->testBalancer();
            $duration = now()->diffInSeconds($start);
            $todayFailedMsg = $bl->getFailedAttemptsCount() > 0 ? ", Today failed attempts => " . $bl->getFailedAttemptsCount() : "";
            $errMsg = !empty($message) ? ", msg: " . $message : "";
            $log = sprintf("%s => '%s' in %s sec%s%s<br>\n",
                $result, $bl->getBalancerInfo(), $duration, $todayFailedMsg,
                $errMsg
            );
            echo $log;
        }

        echo "\nAll balancers Info:\n";
        print_r($this->getAllBalancersInfo());
    }

    public function getAllBalancersInfo() : array {
        $info = [];
        /** @var LoadBalancerItem $bl */
        foreach ($this->balancers as $key => $bl) {
            $failed = $bl->getFailedAttemptsCount() > 0 ? ", Failed attempts => {$bl->getFailedAttemptsCount()}" : "";
            $info[] = sprintf("%s %s => %s%s",
                $bl->getStatus(),
                $bl->getType(),
                $bl->getBalancerInfo(), $failed,

            );
        }

        rsort($info);

        return $info;
    }

}
