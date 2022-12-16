<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class DisplayImage extends Field
{
    protected string $view = 'forms.components.display-image';

    public string | int | null $displayWidth = 140;
    public string | null $displayAlign = null;

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

}
