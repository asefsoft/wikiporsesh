<?php
/** @var \App\Models\Article $article */
?>
<div class="flex flex-col p-1 justify-between">
    @can('manage')
        {{-- actions menu--}}
        <div class="absolute mr-3 mt-3">
        @include('article.partials.admin-actions')
        </div>
    @endcan
    <a href="{{$article->getArticleDisplayUrl()}}">
    <figure class="flex justify-center">
        <img src="{{$article->image_url}}" alt="article poster" class="rounded-t-xl min-w-[4rem] min-h-[3rem]">
    </figure>
    <h2 class="text-lg font-bold my-4">{{ $article->title_fa }}</h2>
    </a>

    <p class="text-justify flex-grow">{{ strLimit($article->description_fa, 250) }}</p>

    <div class="flex justify-center my-3">
        {!! $article->getCategoryLinks('ring-2 ring-blue-500 ') !!}
    </div>
</div>
