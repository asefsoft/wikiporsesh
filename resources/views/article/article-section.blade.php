{{--Section Number and title--}}
<div id="section-{{$sectionIndex + 1}}" class="flex mt-7 mb-5 rounded-sm">
    <div class="bg-primary-800 text-sm absolute w-[60px] h-[60px] flex flex-col justify-center text-center text-white">
        @if(! $article->hasEqualSectionAndSteps())
        {{$article->getStepType()}}
        @endif
        <div class="text-lg font-semibold">{{$sectionIndex + 1 }}</div>
    </div>
    <h2 class="md:text-lg min-h-[60px] text-white flex items-center grow bg-gradient-to-r to-cyan-500 from-primary-600 pl-[70px] md:pl-20 pr-3">
        {{$section->title_fa}}
    </h2>
</div>

{{-- Sections Steps --}}
<div class="space-y-7 mb-5">
@foreach($section->steps as $stepIndex => $step)
    @include("article.article-step", [$step, $stepIndex])
@endforeach
</div>
