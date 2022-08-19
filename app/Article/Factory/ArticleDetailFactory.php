<?php


namespace App\Article\Factory;

use App\Article\CrawlDetail\ArticleDetail;
use App\Article\CrawlDetail\UnknownArticleDetail;
use App\Article\CrawlDetail\WikiHowArticleDetail;
use App\Article\Url\ArticleUrl;
use App\Models\Url;

class ArticleDetailFactory
{

    public static function make(ArticleUrl $articleUrl, Url $urlDb) : ArticleDetail {

        $videoHtml = urldecode($urlDb->content->content ?? '');

        switch ($articleUrl->getName()) {
            case 'WikiHow' :
                return new WikiHowArticleDetail($urlDb->content->content ?? '', $urlDb->getFullUrl());


            default:
                return new UnknownArticleDetail('','');

        }

    }

}
