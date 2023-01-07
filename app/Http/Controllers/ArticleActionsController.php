<?php

namespace App\Http\Controllers;

use App\Article\AssetsManager\AssetsManager;
use App\Models\Article;
use App\Translate\ArticleAutoTranslator;

class ArticleActionsController extends Controller
{

    public function translate(Article $article) {

        $translator = new ArticleAutoTranslator($article);
        $translator->start(); // do translate all steps of article

        return $translator->getStatusText();

    }

    public function translate_designate(Article $article) {

        if($article->is_translate_designated == 1) {
            $article->is_translate_designated = 0;
            $message = "مقاله از لیست منتخب ترجمه خارج شد.";
        }
        else {
            $article->is_translate_designated = 1;
            $message = "مقاله به لیست منتخب ترجمه اضافه شد.";
        }

        $article->save();

        flashBanner($message, 'danger');

        return redirect()->back();

    }
}
