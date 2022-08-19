<?php


namespace App\Article\Url;


use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;

class UnknownUrl extends ArticleUrl
{
    protected string $name = 'Unknown';

    function getCleanedUrl(UriInterface $url) : UriInterface {
        return $url;
    }

    function getUrlID(string $url): string {
        return $url;
    }

    function isValidArticleUrl(UriInterface $url) : bool {
        return false;
    }

    function isValidArticleSite(UriInterface $url) : bool {
        return false;
    }

    function isMainUrl(UriInterface $url) : bool {
        return false;
    }

    function isValidArticleSubUrl(UriInterface $url) : bool {
        return false;
    }

    function getUrlUniqueID(UriInterface $url) : UriInterface {
        return false;
    }

    function isIgnoredPath(UriInterface $url) : bool {
        return false;
    }

    function isCategoryUrl(UriInterface $url) : bool {
        return false;
    }
}
