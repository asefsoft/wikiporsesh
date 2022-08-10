<?php


namespace App\Article\Factory;



use App\Article\Url\ArticleUrl;
use App\Models\Url;

class ArticleDetailFactory
{

    public static function make(ArticleUrl $video_url, Url $urlDb) : ArticleDetail {

        $video_html = urldecode($urlDb->content->content ?? '');

        switch ($video_url->getName()) {
            case 'Aparat' :
                return new AparatVideoDetail($urlDb->content->content ?? '', $urlDb->getFullUrl());

            case 'Instagram' :
                return new InstagramVideoDetail($video_html, $urlDb->getFullUrl());

            case 'Youtube' :
                return new YoutubeVideoDetail($video_html, $urlDb->getFullUrl());

            default:
                return new UnknownVideoDetail('','');

        }

    }

}
