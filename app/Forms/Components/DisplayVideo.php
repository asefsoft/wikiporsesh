<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class DisplayVideo extends Field
{
    protected string $view = 'forms.components.display-video';

    public string | int | null $displayWidth = 180;
    public string | null $displayAlign = null;
    public $posterUrl = null;

    public function setDisplayWidth(string | int | null $with): static
    {
        $this->displayWidth = $with;

        return $this;
    }

    public function setDisplayAlign(string | null $align): static
    {
        $this->displayAlign = $align;

        return $this;
    }

    public function setPosterUrl($url): static
    {
//        $url->call($this);
        $this->posterUrl = $url;
//        $this->evaluate($url);
//        $url(['ddd']);
//        $this->setExtraAttributes([
//            'poster' => $url
//        ]);

        return $this;
    }

    public function getPosterUrl(): ?string
    {
        return $this->evaluate($this->posterUrl);
    }


}
