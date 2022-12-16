@extends('forms::components.text-input')
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
{{--    :label="$getLabel()"--}}
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
{{--    :hint="$getHint()"--}}
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}').defer }">
        <x-forms::button
            :wire:click="'dispatchFormEvent(\'advanced-text-input::doTranslate\', \'' . $getStatePath() . '\')'"
            size="sm"
            type="button"
            style="position: relative; top: 30px; float: inline-end; margin-right: 12px;"
        >
            ترجمه
        </x-forms::button>
    </div>
</x-dynamic-component>
