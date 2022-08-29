<?php


namespace App\Article\Factory;


use App\Article\Url\ArticleUrl;
use App\Article\Url\UnknownUrl;
use App\Article\Url\WikiHowArticleUrl;
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

    public static function getCleanedUrl($url) : UriInterface {
        $articleUrl = static::make($url);

        return $articleUrl->getCleanedUrl();
    }
}
