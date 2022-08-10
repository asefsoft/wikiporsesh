<?php


namespace App\Article\Url;

use Psr\Http\Message\UriInterface;

abstract class ArticleUrl
{
    protected string $name = 'Youtube';

    protected array $validHosts = [ ];

    // is url belongs to a page that contain video stream?
    abstract function isValidArticleUrl(UriInterface $url) : bool;

    // is a url of video site? like {aparat, youtube ...}
    abstract function isValidArticleSite(UriInterface $url) : bool;

    abstract function isMainUrl(UriInterface $url) : bool;

    abstract function isValidArticleSubUrl(UriInterface $url) : bool;

    // remove extra chars of url which does not affect in uniqueness of url
    abstract function getCleanedUrl(string $url) : string;

    abstract function getUrlUniqueID(string $url) : string; // get unique id of article

    public function stripParamFromUrl( $url ) : string {
        return strtok($url, '?');
    }

    function getName() : string {
        return $this->name;
    }
}
