<?php

namespace App\Translate;

use App\Models\Article;
use Illuminate\Support\Collection;

class ArticleAutoTranslator {

    use TranslatableComponent;

    protected Collection $steps;
    private int $failedTranslate = 0;
    private int $delayBetweenTranslate = 30; // in sec
    private int $stepsToBeTranslate = 0;
    private int $stepsTranslated = 0;
    private int $stepsSkipped = 0;

    private bool $isDebugging = false;

    public function __construct(
        protected Article $article
    ) {
        $this->delayBetweenTranslate = ifProduction(30, 10);
    }

    public function start() {
        $this->translateDescription();
        $this->translateArticleSectionsNames();
        $this->translateArticleFields();
        $this->translateSteps();
    }

    // translate description of article
    private function translateDescription(): void {
        $textToBeTranslate = $this->article->description_fa;
        if(!empty($textToBeTranslate) && $textToBeTranslate == $this->article->description_en) {
            if ($this->translateText($textToBeTranslate)) {
                $this->article->description_fa = $textToBeTranslate;
                $this->article->save();

                logError(sprintf("article %s description is auto translated.",
                    $this->article->id
                ), 'info');
            }
            else {
                $this->failedTranslate ++;
            }
        }
    }

    // sections titles
    private function translateArticleSectionsNames() {
        $sections = $this->article->sections;
        foreach ($sections as $section) {
            // should translate?
            $textToBeTranslate = $section->title_fa;
            if(!empty($textToBeTranslate) && $textToBeTranslate == $section->title_en) {
                // do translate
                if ($this->translateText($textToBeTranslate)) {

                    $section->title_fa = $textToBeTranslate;
                    $section->save();

                    logError(sprintf("article %s section `%s` `%s` is auto translated.",
                        $this->article->id, $section->id, $section->title_en
                    ), 'info');

                    // delay
                    sleep($this->getSleepTime());
                }
                else {
                    $this->failedTranslate ++;
                }
        }   }
    }

        // translate article's title, desc ...
    private function translateArticleFields() {
        $fields = ['title', 'description', 'tips', 'warnings'];

        foreach ($fields as $field) {
            if($this->shouldTranslateField($field, $textToBeTranslate)) {

                // translate field
                if ($this->translateText($textToBeTranslate)) {

                    $this->article->setAttribute($field . "_fa", $textToBeTranslate);

                    logError(sprintf("article %s field `%s` is auto translated.",
                        $this->article->id, $field
                    ), 'info');

                    // delay
                    sleep($this->getSleepTime());
                }
                else {
                    $this->failedTranslate ++;
                    logError(sprintf("Could not translate article id %s field `%s`", $this->article->id, $field));
                }
            }
        }

        $this->article->save();
    }

    private function shouldTranslateField($field, &$textToBeTranslate) : bool {
        $enText = $this->article->getAttribute($field . "_en");
        $faText = $this->article->getAttribute($field . "_fa");
        $textToBeTranslate = $enText;
        return !empty($enText)  && $enText == $faText;
    }

    private function translateSteps() {

        // get not translated steps of article
        $this->steps = $this->article->steps()->notAutoTranslated()->orderBy('overall_step_order')->get();
        $this->stepsToBeTranslate = count($this->steps);

        // do translate all steps
        foreach ($this->steps as $step) {

            // probably already edited or translated, we skip it
            if(! $step->isFarsiAndEnglishContentSame()) {
                $this->stepsSkipped++;
                continue;
            }

            $text = $step->content_en;

            $this->doTranslateStep($text, $step);

            if(! $this->shouldContinue())
                break;
        }

        // update translate percent of article
        $this->article->updatePercentTranslated($this->stepsSkipped);

    }

    private function shouldContinue() : bool {
        return $this->failedTranslate <= 3;
    }

    public function wasSuccessful() : bool {
        return $this->failedTranslate == 0;
    }

    public function getStatusText() : string {

        if($this->stepsToBeTranslate == 0)
            return "There was nothing to be translated.";

        $percent = $this->getTranslatedPercent();

        $skippedSteps = $this->stepsSkipped > 0 ? sprintf(" %s skipped steps", $this->stepsSkipped) : "";

        return sprintf("Translated %s%% of steps. %s/%s steps.%s",
            $percent, $this->stepsTranslated, $this->stepsToBeTranslate,
            $skippedSteps
        );
    }

    private function getTranslatedPercent() : int {
        return (int)(($this->getTranslatedAndSkippedStepsCount() / $this->stepsToBeTranslate) * 100);
    }

    private function doTranslateStep($text, mixed $step) {
        if ($this->translateText($text)) {

            $step->storeTranslatedText($text, true);
            $this->stepsTranslated ++;

            logError(sprintf("article %s step %s is auto translated.",
                $this->article->id, $step->overall_step_order
            ), 'info');


            // delay
            sleep($this->getSleepTime());
        }
        else {
            $this->failedTranslate ++;
            logError(sprintf("Could not translate article id %s step id %s", $this->article->id, $step->id));
        }
    }

    private function getSleepTime() : int{

        if(!empty($this->isDebugging))
            return 0;

        return $this->delayBetweenTranslate + rand(1, 7);
    }

    /**
     * @return int
     */
    private function getTranslatedAndSkippedStepsCount() : int {
        return $this->stepsTranslated + $this->stepsSkipped;
    }



}
