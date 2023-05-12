<?php
/** @var \App\Models\Article $article */
?>
<div class="flex flex-col justify-between rounded-t-xl shadow-md">
    @can('manage')
        {{-- actions menu--}}
        <div class="absolute mr-3 mt-3">
        @include('article.partials.admin-actions')
        </div>
    @endcan

    {{-- Title and Image --}}
    <a href="{{$article->getArticleDisplayUrl()}}">
    <figure class="flex justify-center">
        <img src="{{$article->image_url}}" alt="article poster" class="rounded-t-xl min-w-[4rem] min-h-[3rem]">
    </figure>
    <h2 class="text-lg font-semibold my-4 mx-3">{{ $article->title_fa }}</h2>
    </a>

    {{-- Text --}}
    <p class="text-justify flex-grow px-3">{{ strLimit($article->description_fa, 250) }}</p>

    {{-- Stats --}}
    @can('manage')
    <div class="flex mt-2 justify-end mx-3">
        <div>بازدید: {{$article->getViewsHumanReadable()}}</div>
    </div>
    @endcan

    {{-- Category --}}
    <div class="flex justify-center mt-3 mb-4">
        {!! $article->getCategoryLinks('text-amaranth-700 hover:text-white border border-amaranth-700 hover:bg-amaranth-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center') !!}
    </div>
</div>
