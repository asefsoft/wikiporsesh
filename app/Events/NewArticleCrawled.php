<?php

namespace App\Events;

use App\Article\CrawlDetail\ProcessArticleDetail;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewArticleCrawled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private ProcessArticleDetail $processor;

    public function __construct(ProcessArticleDetail $processor) {
        $this->processor = $processor;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
