<?php


namespace App\Article\CrawlDetail;


class UnknownArticleDetail extends ArticleDetail
{
    public function parseArticleInfo() : bool {
        return false;
    }
}
