{{--Section Number and title--}}
<div class="flex mt-7 mb-3 rounded-sm">
    <div class="bg-blue-500 text-sm absolute w-[60px] h-[60px] flex flex-col justify-center text-center">
        @if(! $article->hasEqualSectionAndSteps())
        {{$article->getStepType()}}
        @endif
        <div class="text-lg font-semibold">{{$sectionIndex + 1 }}</div>
    </div>
    <h2 class="text-xl text-white bg-primary-dark grow" style="padding: 16px 75px 16px 16px;">
        {{$section->title_fa}}
    </h2>
</div>

{{-- Sections Steps --}}
<div class="space-y-7 mb-5">
@foreach($section->steps as $stepIndex => $step)
    @include("article.article-step", [$step, $stepIndex])
@endforeach
</div>
