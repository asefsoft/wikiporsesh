<?php

namespace App\Translate;

use App\Forms\Components\AdvanceTextInput;
use Illuminate\Support\Arr;

trait TranslatableComponent
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerListeners([
            'advanced-text-input::doTranslate' => [
                function ( $component, string $statePath): void {
                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }
//
                    $fieldValue = Arr::get((array)$component->getLivewire(), $component->getStatePath());

                    if($this->translateText($fieldValue)) {
                        $component->state($fieldValue);
                    }else
                        $component->getLivewire()->notify("failed", "ترجمه با خطا مواجه شد.", true);

//                    data_set($livewire, "{$statePath}.{$newUuid}", []);
                },
            ]]);
    }

    protected function translateText(&$text): bool {
        $translator= new ManualGoogleApiTranslator();

        $translator->translate($text);

        if($translator->wasSuccessful())
            $text = $translator->getTranslatedText();

        return $translator->wasSuccessful();
    }
}
