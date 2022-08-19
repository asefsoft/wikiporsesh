<?php


namespace App\Article\Url;


use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;

class WikiHowArticleUrl extends ArticleUrl
{
    protected string $name = 'WikiHow';

    protected array $validHosts = [
        'wikihow.com',
        'www.wikihow.com',
    ];

    protected array $ignoredPaths = [
        '/Course/*',
        '/Experts/*',
        '/wikiHow:*',
        '/*-Quiz',
        '/Quizzes',
        '/Pro',
        '/Experts',
        '/Newsletters',
        '/Tech-Help-Pro',
        '/Randomizer',
    ];


    protected string $ignoredPathPattern = '';

    public function __construct() {
        $grouped_patterns = [];
        foreach ($this->ignoredPaths as $pattern) {
            $grouped_patterns[] = "(" . str_replace("*",".*", $pattern) . ")";
        }

        $this->ignoredPathPattern = implode("|", $grouped_patterns);
    }


    public function isValidArticleUrl(UriInterface $url): bool {
        return $this->isValidArticleSite($url) &&
               ! $this->hasColon($url) &&
               ! str_contains(substr($url->getPath(), 1), "/") &&
               ! $this->isMainUrl($url);
    }

    private function hasColon(UriInterface $url) : bool {
        return str_contains($url->getPath(), ":");
    }

    // get wiki how section from it's url
    private function getSection(UriInterface $url): bool | string {
        if($this->hasColon($url)) {
            $parts = explode(":", $url->getPath());
            return $parts[0];
        }
        return false;
    }


    function isValidArticleSite(UriInterface $url): bool {
        return Str::is($this->validHosts, $url->getHost());
    }

    function isMainUrl(UriInterface $url): bool  {
        return $this->isValidArticleSite($url) &&
               in_array($url->getPath(),['','/','/Main-Page']);
    }

    public function isValidArticleSubUrl(UriInterface $url): bool {
        return Str::startsWith($url->getPath(), $this->subUrls);
    }

    public function getName(): string {
        return $this->name;
    }

    public function extractArticleId(UriInterface $url)
    {

        if(str($url->getPath())->startsWith("/"))
            return str($url->getPath())->substr(1);


        return $url->getPath();
    }

    function getCleanedUrl(UriInterface $url) : UriInterface {
        if($this->isValidArticleUrl($url)) {
            $id = $this->extractArticleId($url);
            $url = sprintf("https://www.wikihow.com/%s", $id);
            return new Uri($url);
        }

        return $url->withQuery('');
    }

    function getUrlUniqueID(UriInterface $url): UriInterface {
        return $url;
    }

    function isIgnoredPath(UriInterface $url) : bool {
        $path = $url->getPath();
        $founds = preg_match("~" . $this->ignoredPathPattern . "~", $path, $matches);

        return $founds > 0;
    }

    function isCategoryUrl(UriInterface $url) : bool {
        return str_starts_with($url->getPath(), "/Category:");

    }
}
