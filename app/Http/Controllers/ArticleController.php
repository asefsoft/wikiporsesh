<?php

namespace App\Http\Controllers;

use App\Article\AssetsManager\AssetsManager;
use App\Models\Article;
use App\Translate\ArticleAutoTranslator;

class ArticleController extends Controller
{
    public function display(Article $article) {

        $assetManager = isAdmin() ? new AssetsManager($article) : null;

        $categoriesBreadcrumb = $article->getCategoriesBreadcrumb();

        return view('article.article-view', compact('article', 'assetManager', 'categoriesBreadcrumb'));

    }

}
