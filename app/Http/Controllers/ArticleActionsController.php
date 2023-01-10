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

    public function translateDesignate(Article $article) {

        if($article->is_translate_designated == 1) {
            $article->is_translate_designated = 0;
            $message = "مقاله از لیست منتخب ترجمه خارج شد.";
        }
        else {
            $article->is_translate_designated = 1;
            $message = "مقاله به لیست منتخب ترجمه اضافه شد.";
        }

        $article->save(['timestamps'=>false]);

        flashBanner($message, 'danger');

        return redirect()->back();

    }
    public function skip(Article $article) {

        if($article->is_skipped == 1) {
            $article->is_skipped = 0;
            $message = "مقاله از لیست نادیده گرفتن خارج شد.";
        }
        else {
            $article->is_skipped = 1;
            $message = "مقاله به لیست نادیده گرفتن اضافه شد.";
        }

        $article->save(['timestamps'=>false]);

        flashBanner($message, 'danger');

        return redirect()->back();

    }

    // download all assets of article to the local storage
    public function makeAssetsLocal(Article $article) {
        $asset = new AssetsManager($article);
        $asset->makeAllAssetsLocal();
        return dd($asset->getAssetStatusText(), $asset);
    }
}
