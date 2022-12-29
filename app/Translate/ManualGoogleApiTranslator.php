<?php

namespace App\Translate;

class ManualGoogleApiTranslator extends BaseTranslator
{
    protected string $name = 'ManualGoogleApi';
    protected string $targetUrl = 'https://translate.googleapis.com/translate_a/single';

    private array $postParams = [
        'client' => 'gtx',
        'dt' => 't',
        'dj' => '1',
        'source' => 'input',
        'tl' => 'fa',
        'hl' => 'en',
        'sl' => 'en',
    ];

    function translate(string $originalText): string {
        $translatedText = '';
        $this->originalText = $originalText;

        // set text to be translated
        $this->postParams['q'] = $originalText;

        $result = $this->sendRequest($this->postParams);

        if($result) {
            try {
                $trans = $this->getTranslatedSentences();
                $this->translatedText = $trans ?? '';
                $this->wasSuccessful = ! is_null($trans);
            }
            catch (\Exception $exception) {
                $this->wasSuccessful = false;
            }

        }

        return $this->translatedText;
    }

    private function getTranslatedSentences(): string {
        $trans = json_decode($this->requestContent, true);
        $trans = collect(\Arr::get($trans, 'sentences'))->pluck('trans')->join(" ");
        return $trans;
    }

    /*
     Sample output:
{
    "sentences": [
        {
            "trans": "سلام مرد",
            "orig": "hello man",
            "backend": 3,
            "model_specification": [
                {}
            ],
            "translation_engine_debug_info": [
                {
                    "model_tracking": {
                        "checkpoint_md5": "982c75c78c6c8e6005ec3a4021a7f785",
                        "launch_doc": "tea_GrecoIndoEuropeA_en2elfahykakumksq_2021q3.md"
                    }
                }
            ]
        }
    ],
    "src": "en",
    "spell": {}
}
     */
}
