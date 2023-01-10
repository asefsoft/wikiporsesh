<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\View\ArticleCollectionData;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{
    // display articles of specific category
    public function display(Category $category) {

//        $articles = $category->articles()->with('categories')->simplePaginate(12);

        $allCategories = Category::getAllCategoriesAndSubCategories([$category->id], 'id');

        $allArticles = Article::whereRelation('categories', function (Builder $query) use ($allCategories) {
            return $query->whereIn('category_id', $allCategories);
        }, $allCategories)->with('categories')->simplePaginate(12);

        $collection = new ArticleCollectionData("دسته بندی " . $category->name_fa, $allArticles);

        $categoriesBreadcrumb[] = $category->getAllParentCategories('Object');

        return view('article.article-list', compact(['collection','categoriesBreadcrumb']));

    }

    // list all categories
    public function list() {

        $allCategories = [];
        $rootCategories = Category::whereNull('parent_category_id')->with('childrenRecursive')->pluck('id')->toArray();

        foreach ($rootCategories as $rootCategory) {
            $allCategories[] = Category::getAllCategoriesAndSubCategories([$rootCategory], 'Object');
        }

        return view('pages.categories', compact(['allCategories']));

    }

}
