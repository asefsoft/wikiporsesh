{{-- Step Image & Video--}}
@if($step->hasVideo())
    <video  controls controlslist="nofullscreen nodownload noremoteplayback noplaybackspeed"
            preload='metadata' fluid="true" width="100%"
            poster='{{ $step->image_url }}'>

        <source src='{{ $step->getVideoUrl() }}'
                type='video/mp4'
                label='step-video'>
    </video>
{{--    <img src="{{ $step->image_url }}" alt="step image" width="100%">--}}
@elseif($step->hasImage())
    <figure class="flex justify-center">
        <img src="{{ $step->image_url }}" alt="step image" width="100%" class="max-w-[500px]">
    </figure>
@endif()

{{-- Step number--}}
@if(! $section->isSingleStep())
<span class="text-3xl font-bold pl-4 float-right" style="font-family: arial; font-size: 3.5em; line-height: 1em; margin-right: 13px;">
    {{ $stepIndex + 1 }}
</span>
@endif

{{-- Step Text--}}
<p id="article-step" class="pb-3 text-justify leading-loose">
    {{ $step->content_fa }}
</p>
