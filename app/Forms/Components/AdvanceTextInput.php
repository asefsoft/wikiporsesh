<?php

namespace App\Forms\Components;

use App\Translate\TranslatableComponent;
use Filament\Forms\Components\TextInput;


class AdvanceTextInput extends TextInput
{
    use TranslatableComponent;

    protected string $view = 'forms.components.advance-text-input';

}
