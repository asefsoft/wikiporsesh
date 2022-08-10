<?php


namespace App\Article\Url;


use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;

class UnknownUrl extends ArticleUrl
{
    protected string $name = 'Unknown';

    function getCleanedUrl(string $url) : string {
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

    function getUrlUniqueID(string $url) : string {
        return false;
    }
}
