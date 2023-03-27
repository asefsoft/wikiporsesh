<?php

namespace App\Http\Controllers;

use App\Article\AssetsManager\AssetsManager;
use App\Article\SearchArticle;
use App\Models\Article;
use App\Models\Category;
use App\Translate\ArticleAutoTranslator;
use App\View\ArticleCollectionData;

class ArticleController extends Controller
{

    public function index() {

        $allArticles = Article::with('categories')->simplePaginate(12);

        $collection = new ArticleCollectionData("ویکی پرسش", $allArticles);

        return view('article.article-list', compact(['collection']));

    }

    public function display(Article $article) {

        $assetManager = isAdmin() ? new AssetsManager($article) : null;

        $categoriesBreadcrumb = $article->getCategoriesBreadcrumb();

        return view('article.article-view', compact('article', 'assetManager', 'categoriesBreadcrumb'));

    }

    public function search() {

        $query = request()->get('q');

        $searcher = new SearchArticle($query);

        $allArticles = $searcher->fullTextSearch(12);

        $collection = new ArticleCollectionData("جستجوی: $query", $allArticles);

        return view('article.article-list', compact(['collection']));

    }



}
