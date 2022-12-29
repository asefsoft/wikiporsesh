<?php


namespace App\Article\Factory;


use App\Article\Url\ArticleUrl;
use App\Article\Url\UnknownUrl;
use App\Article\Url\WikiHowArticleUrl;
use App\Models\Site;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

class ArticleUrlFactory
{

    public static function make(string $url) : ArticleUrl {
        $url = new Uri($url);

        // WikiPorsesh
        $wikiPorsesh = new WikiHowArticleUrl($url);
        if($wikiPorsesh->isValidArticleSite()) {
            return $wikiPorsesh;
        }
        unset($wikiPorsesh);

        // no valid
        return new UnknownUrl($url);
    }

    // sometimes we need to force using specific site url
    // for instance when asset urls are in relative format and does not have full url
    public static function makeBySite(string $url, Site $site) : ArticleUrl {
        $url = new Uri($url);

        return match ($site->name) {
            'WikiHow' => new WikiHowArticleUrl($url),
            default => new UnknownUrl($url),
        };

    }

    public static function getCleanedUrl($url) : UriInterface {
        $articleUrl = static::make($url);

        return $articleUrl->getCleanedUrl();
    }
}
