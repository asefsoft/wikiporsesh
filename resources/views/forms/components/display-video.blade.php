<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}').defer }" class="flex" style="justify-content: {{$displayAlign}}">
        <video  controls controlslist="nofullscreen nodownload noremoteplayback noplaybackspeed"
                preload='metadata' fluid="true"
                poster='{{$getPosterUrl()}}'>

            <source :src="state"
                    type='video/mp4'
                    label='step-video'>
        </video>
    </div>
</x-dynamic-component>
