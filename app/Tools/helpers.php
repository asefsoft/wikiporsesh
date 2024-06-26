<?php

use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;

function logMe($fileName, $log, $addDateToLog = true, $addDateToFileName = true){
    try {
        $prependDate = $addDateToLog ? now()->format("Y/m/d H:i:s") . ' ' : '';
        $fileNameDate = $addDateToFileName ? "-" . now()->toDateString() : '';
        File::append(storage_path() . "/logs/$fileName" . $fileNameDate . '.log',
            $prependDate . $log . "\n");
    } catch (Exception $e) {
        echo sprintf("Error on log_me func: %s, log: %s<br>\n", $e->getMessage(), $log);
    }
}

function flashBanner($message, $type = 'success') {
    request()->session()->flash('flash.banner', $message);
    request()->session()->flash('flash.bannerStyle', $type);
}

function isAdmin(): bool {
    static $isAdmin;

    $booted = app()->isBooted();

    if ( !empty($isAdmin) && $booted) {
        return $isAdmin;
    } else {
        return $isAdmin = auth()->user() != null && auth()->user()->can('manage');
    }
}

function isLocal() : bool {
    return app()->environment('local');
}

function isProduction() : bool {
    return app()->environment('production');
}

function ifProduction($productionValue, $notProductionValue) {
    return isProduction() ? $productionValue : $notProductionValue;
}

// return our load balancer for balance crawl request
function balancer() {
    return app()->make('balancer');
}

function getLeftOrderHtmlString($text): HtmlString {
    return new HtmlString(sprintf("<div style='direction: ltr; text-align: left'>%s</div>", $text));
}

function number_format_short( $n , $add_plus = true, $rtl = false) {
    $n_format = floor($n);
    $suffix = '';
    $plus = '';
    if ($n > 0 && $n < 1000) {
        // 1 - 999
        $n_format = floor($n);
        $suffix = '';
        $plus = '';
    } else if ($n >= 1000 && $n < 1000000) {
        // 1k-999k
        $n_format = floor($n / 1000);
        $suffix = 'K';
        $plus = '+';
    } else if ($n >= 1000000 && $n < 1000000000) {
        // 1m-999m
        $n_format = floor($n / 1000000);
        $suffix = 'M';
        $plus = '+';
    } else if ($n >= 1000000000 && $n < 1000000000000) {
        // 1b-999b
        $n_format = floor($n / 1000000000);
        $suffix = 'G';
        $plus = '+';
    } else if ($n >= 1000000000000) {
        // 1t+
        $n_format = floor($n / 1000000000000);
        $suffix = 'T';
        $plus = '+';
    }

    if(!$add_plus)
        $plus = '';

    $r = $rtl ? $plus . $n_format . $suffix : $n_format . $suffix . $plus;

    return !empty($n_format . $suffix) ? $r : 0;
}

function getDateString($date, $type = "remaining", $format = "d F, Y H:i:s") {
    if (empty($date))
        return "";

    if(!$date instanceof Carbon)
        $date = Carbon::parse($date);

    switch ($type) {
        default:
        case "remaining":
            return $date->diffForHumans();
        case "jalali":
        case "persian":
            return jalalianDate($date, $format);
        case "miladi":
        case "jeorgian":
            return $date->format($format);

    }
}

function jalalianDate($date , $format = 'd F, Y', $default = '') : string {
    if(empty($date))
        return $default;

    return Jalalian::forge($date)->format($format);
}

function only_fields ($models, array $fields, $limit_string = 80, array $headers = [], array $footers = []) {
    //        $d=$models->pluck('video.local_video.file_path');
    $strings = $models->map(function ($model) use ( $limit_string, $fields ) {
        $string = [];

        foreach ($fields as $field){

            // nested relation value
            $segments = explode('.', $field);

            if(count($segments) > 1) {
                //                    $relation_value = $model->getAttribute($segments[0]);
                //                    $value = $relation_value instanceof Model ? $relation_value->getAttribute($segments[1]) : null;
                $value = data_get($model, $segments);
            }
            else
                $value = $model->$field;

            if($value instanceof Carbon && false)
                $value = $value->diffForHumans();

            $string[] = sprintf("%s: '%s'", array_reverse($segments)[0], Str::limit($value, $limit_string));
        }

        return implode(', ', $string);
    });

    $strings = collect($headers)->merge($strings);
    $strings = $strings->merge($footers);

    return $strings;
}

function unescape_unicode($string) {
    $string = str_replace('"','\\"', $string);
    return json_decode('"'.$string.'"', JSON_UNESCAPED_SLASHES);
}

function logException($exception, $methodName, $extra = ''){
    logError(sprintf("Error on %s, %s, %s", $methodName, $extra, $exception->getMessage()));
}

function logError($error, $level = 'warning'){
    Log::log($level,$error);
}

function strLimit($text, $limit = 100, $end = '...') {
    return Str::limit($text, $limit, $end);
}

function isDebug(): bool {
    return request()->has( 'debug' );
}

function loadTime() {
    $load_time = getTook();
    echo sprintf("<took style='display: none'>%.2f sec</took>\n<query style='display: none'>%s ms, count: %s, slow: %s</query>", $load_time,
        number_format($GLOBALS['STAT_QUERY_TIME'] ?? -1),
        number_format($GLOBALS['STAT_QUERY_COUNT'] ?? -1),
        number_format($GLOBALS['STAT_QUERY_COUNT_SLOW'] ?? -1)
    );

    // slow?
    if($load_time >= 2) {
        $user = auth()->check() ? 'user: ' . auth()->user()->name . ', ' : '';

        $q = sprintf("Slow Page Load, time: %.2f sec, %s%s\nSLOW_QUERY_COUNT: %s\nQUERY_TIME: %s ms.\n",
            $load_time,
            $user,
            rawurldecode(request()->fullUrl()),
            $GLOBALS['STAT_QUERY_COUNT_SLOW'] ?? -1,
            $GLOBALS['STAT_QUERY_TIME'] ?? -1
        );

        Log::warning($q);
    }
}

function needLivewireScripts(): bool {
    $route = Route::getCurrentRoute()->uri();
    return str_starts_with($route, "user/");
}

function getTook() : float {
    return microtime(true) - LARAVEL_START;
}

