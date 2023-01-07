<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\View\ArticleCollectionData;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{
    public function display(Category $category) {

//        $articles = $category->articles()->with('categories')->simplePaginate(12);

        $allCategories = Category::getAllCategoriesAndSubCategories([$category->id], 'id');

        $allArticles = Article::whereRelation('categories', function (Builder $query) use ($allCategories) {
            return $query->whereIn('category_id', $allCategories);
        }, $allCategories)->with('categories')->simplePaginate(12);

        $collection = new ArticleCollectionData("دسته بندی " . $category->name_fa, $allArticles);

        return view('article.article-list', compact(['collection']));

    }

}
