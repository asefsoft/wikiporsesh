<?php

namespace App\Article\Persist;

use App\Models\Article;
use App\Models\ArticleSection;
use App\Models\ArticleStep;
use App\StructuredData\Types\SD_HowToStep;
use Illuminate\Support\Collection;

class StepManager {

    protected ArticleSection $articleSection;
    protected ?Collection $articleStepsDB;
    protected array $givenArticleSteps;
    protected bool $alreadyHasSteps;

    public function __construct(ArticleSection $articleSection, array $givenArticleSteps) {
        $this->articleSection    = $articleSection;
        $this->givenArticleSteps = $givenArticleSteps;

        //get current step from db
        $this->articleStepsDB = $articleSection->steps;
        $this->alreadyHasSteps = count($this->articleStepsDB ?? []) > 0;
    }

    public function persist() {

        $stepOrder = 1;
        /** @var SD_HowToStep $givenArticleStep */
        foreach ($this->givenArticleSteps as $givenArticleStep){

            $this->updateOrCreateStep($givenArticleStep, $stepOrder);
            $stepOrder++;
        }
    }

    private function updateOrCreateStep(SD_HowToStep $givenArticleStep, int $stepOrder) : void {
        $stepData = [
            'article_id' => $this->articleSection->article->id, 'section_id' => $this->articleSection->id,
            'order'      => $stepOrder, 'content_en' => $givenArticleStep->text,
            'image_url'  => $givenArticleStep->image, 'video_url' => $givenArticleStep->videoUrl,
            'overall_step_order' => $givenArticleStep->overallStepNumber
        ];

        // if exist update it
        if ($this->alreadyHasSteps) {
            $stepDB = $this->articleStepsDB[$stepOrder - 1];
            $stepDB->update($stepData);
        }
        // if not exist create it and add it to collection
        else {
            $stepData['content_fa'] = $givenArticleStep->text;
            $stepDB                 = ArticleStep::create($stepData);
            $this->articleStepsDB->add($stepDB);
        }
    }


}
