<?php

namespace App\Tools;

class Tools {
    public static function init_output_flushing(): void
    {
        if(!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            //            header('Content-Encoding: none; ');//disable apache compressed
        }
        //        ini_set('zlib.output_compression', 0);
        //        ini_set('implicit_flush', 1);
        //        ob_start();
        ini_set('output_buffering ', 0);
        ob_implicit_flush(1);
        set_time_limit(0);
    }

    public static function echo($str, $pad_len = 4500, $add_date = false){
        $flush_str='';
        $date = "";
        if(request()->has('flush')) {
            $flush_str = str_repeat(' ', $pad_len);
        }
        if($add_date)
            $date = now() . ": ";
        echo $date . $str . $flush_str . "\n";

    }
}
