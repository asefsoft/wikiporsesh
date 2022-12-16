<?php

namespace App\Forms\Components;

use App\Translate\TranslatableComponent;
use Filament\Forms\Components\Textarea;

class AdvanceTextArea extends Textarea
{
    use TranslatableComponent;

    protected string $view = 'forms.components.advance-text-area';
}
