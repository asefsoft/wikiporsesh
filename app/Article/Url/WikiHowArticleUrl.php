<?php


namespace App\Article\Url;


use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;

class WikiHowArticleUrl extends ArticleUrl
{
    protected string $name = 'Youtube';

    protected array $validHosts = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
        'youtu.be',
        'www.youtu.be',
    ];

    protected $short_video_urls = ['youtu.be'];

    protected $sub_urls = [''];

    public function isValidArticleUrl(UriInterface $url): bool {
//        dd($url->getHost());
        return $this->isValidArticleSite($url) && ($this->is_full_yt_url($url) || $this->is_short_yt_url($url));
    }

    private function is_full_yt_url(UriInterface $url): bool {
        return Str::startsWith($url->getPath(), "/watch") &&
            Str::contains($url->getQuery(),'v=');
    }

    private function is_short_yt_url(UriInterface $url): bool {
        return Str::is($this->short_video_urls, $url->getHost()) &&
            preg_match('/[a-z0-9_-]{11}/i', $url->getPath(), $matches) == 1;
    }


    function isValidArticleSite(UriInterface $url): bool {
        return Str::is($this->validHosts, $url->getHost());
    }

    function isMainUrl(UriInterface $url): bool  {
        return $this->isValidArticleSite($url) &&
               in_array($url->getPath(),['','/']);
    }

    public function isValidArticleSubUrl(UriInterface $url): bool {
        return Str::startsWith($url->getPath(), $this->sub_urls);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    public function extractVideoId($str)
    {
        if (preg_match('/[a-z0-9_-]{11}/i', $str, $matches)) {
            return $matches[0];
        }

        return false;
    }

    function getCleanedUrl(string $url) : string {
        if($this->isValidArticleUrl( new Uri($url))) {
            $video_id = $this->extractVideoId($url);
            $url = sprintf("https://www.youtube.com/watch?v=%s", $video_id);
        }
        return $url;
    }

    function getUrlUniqueID(string $url): string {
        return $url;
    }
}
