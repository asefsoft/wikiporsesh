<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

function logMe($fileName, $log, $addDate = true){
    try {
        $date = $addDate ? now()->format("Y/m/d H:i:s") . ' ' : '';
        File::append(storage_path() . "/logs/$fileName-" . now()->toDateString() . '.log', $date . $log . "\n");
    } catch (Exception $e) {
        echo sprintf("Error on log_me func: %s, log: %s<br>/n", $e->getMessage(), $log);
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
