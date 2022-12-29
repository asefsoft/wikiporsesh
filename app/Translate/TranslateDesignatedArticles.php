<?php

namespace App\Translate;

use App\Models\Article;
use Illuminate\Support\Collection;

class TranslateDesignatedArticles {

    protected Collection $articles;
    private int $failedTranslate = 0;
    private int $delayBetweenTranslate = 30; // in sec
    private int $articlesToBeTranslate = 0;
    private int $articlesTranslated = 0;

    private bool $isDebugging = false;

    public function __construct() {
        $this->delayBetweenTranslate = ifProduction(60, 10);
    }

    public function start() {

        // get not translated steps of article
        $this->articles              = Article::translateDesignated()->take(10)->get();
        $this->articlesToBeTranslate = count($this->articles);

        // do translate all designated articles
        foreach ($this->articles as $article) {

            $translator = new ArticleAutoTranslator($article);
            $translator->start(); // do translate all steps of article

            if ($translator->wasSuccessful()) {

                $this->articlesTranslated++;

                logError(sprintf("Designate article %s is fully auto translated.", $article->id), 'info');

                // delay
                sleep($this->getSleepTime());
            }
            else {
                $this->failedTranslate++;

                logError(sprintf("Could not translate designated article id %s. %s",
                    $article->id,
                    $translator->getStatusText()
                ));
            }

            if ( ! $this->shouldContinue()) {
                break;
            }
        }
    }

    private function getSleepTime() : int{

        if(!empty($this->isDebugging))
            return 0;

        return $this->delayBetweenTranslate + rand(1, 7);
    }

    private function shouldContinue() : bool {
        return $this->failedTranslate <= 2;
    }

    public function wasSuccessful() : bool {
        return $this->articlesToBeTranslate > 0 && $this->articlesTranslated == $this->articlesToBeTranslate;
    }

    public function getStatusText() : string {

        if ($this->articlesToBeTranslate == 0) {
            return "There was nothing to be translated.";
        }

        $percent = (int)(($this->articlesTranslated / $this->articlesToBeTranslate) * 100);

        return sprintf("Translated %s%% of articles. %s/%s articles.", $percent, $this->articlesTranslated,
            $this->articlesToBeTranslate);
    }
}
