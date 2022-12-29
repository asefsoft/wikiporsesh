<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function display(Article $article) {

        $assetManager = isAdmin() ? new \App\Article\AssetsManager\AssetsManager($article): null;

        return view('article.article-view', compact('article', 'assetManager'));

        dd($article->toArray(), $article->relationsToArray());

    }
}
