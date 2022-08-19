<?php

namespace App\Jobs;

use App\Article\CrawlDetail\ProcessArticleDetail;
use App\Models\Url;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCrawledUrl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Url $crawledUrl;

    public function __construct(Url $crawledUrl) {
        $this->crawledUrl = $crawledUrl;
    }

    public function handle()
    {
        $processor = new ProcessArticleDetail($this->crawledUrl);
        $processor->process();
    }
}
