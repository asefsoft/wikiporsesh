<?php


namespace App\Article\Url;


use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;

class UnknownUrl extends ArticleUrl
{
    protected string $name = 'Unknown';

    function getCleanedUrl() : UriInterface {
        return $this->url;
    }

    function isValidArticleUrl() : bool {
        return false;
    }

    function isValidArticleSite() : bool {
        return false;
    }

    function isMainUrl() : bool {
        return false;
    }

    function isValidArticleSubUrl() : bool {
        return false;
    }

    function getUrlUniqueID() : UriInterface {
        return $this->url;
    }

    function isIgnoredPath() : bool {
        return false;
    }

    function isCategoryUrl() : bool {
        return false;
    }

    protected function init() {
    }

    function getSlug() : string {
        return '';
    }
}
