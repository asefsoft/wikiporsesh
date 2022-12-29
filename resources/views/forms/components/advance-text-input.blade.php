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
    <x-translate-button :getStatePath="$getStatePath"/>
</x-dynamic-component>
