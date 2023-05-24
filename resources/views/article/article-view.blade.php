<?php
/** @var \App\Models\Article $article */
/** @var \App\Article\AssetsManager\AssetsManager $assetManager */
?>
<x-app-layout>
    <x-slot name="head">
        <meta name="description" content="{{ $article->getSummary() }}"/>
        <meta name="keywords" content="ویدئو، فیلم، سرگرمی، بازی، کلیپ، رایگان، دانلود, اینستاگرام  video, clip, stream, funny, instagram"/>
    </x-slot>

    <x-slot name="header">
        {{-- Article Title --}}
        <h1 class="font-semibold text-lg md:text-xl text-gray-800 leading-tight">
            {{ $article->title_fa }}
            @can('manage')
                <div class="absolute">
                @include('article.partials.admin-actions')
                </div>
                - <a class="text-sm" href="{{ $article->getArticleSourceUrl() }}" rel="noopener noreferer">منبع</a>
                <span class="text-sm text-gray-400">
                {{{  $assetManager->getAssetStatusText() }}}
                </span>
            @endcan
        </h1>
    </x-slot>

    {{-- Main Content of page --}}
    <section id="article-content" class="lg:col-span-9 md:col-span-8 pl-5 pr-5 md:pr-0 py-3 leading-loose">

        {{-- Categories Breadcrumb --}}
        @include("article.partials.categories-breadcrumb")

        {{-- Article Desc --}}
        <p id="article-desc" class="mt-3 mb-4 text-justify">
            {{$article->description_fa}}
        </p>

        {{-- Sections Titles --}}
        @include("article.partials.sections")

        {{-- All article Sections and Steps --}}
        @foreach($article->sections as $sectionIndex => $section)
            @include("article.article-section", [$section, $sectionIndex])
        @endforeach

        {{-- Article TIPS --}}
        @if($article->hasTips())
        <h3 class="inline-block text-xl font-bold">نکات:</h3>
        <p id="article-tips" class="mt-3 mb-4 text-justify">
            {!! str_replace("\n","<br/>", $article->tips_fa) !!}
        </p>
        @endif

        {{-- Article WARNINGS --}}
        @if($article->hasWarnings())
        <h3 class="inline-block text-xl font-bold">هشدار ها:</h3>
        <p id="article-warnings" class="mt-3 mb-4 text-justify">
            {!! str_replace("\n","<br/>", $article->warnings_fa) !!}
        </p>
        @endif
    </section>

    {{-- Sidebar --}}
    <aside class="lg:col-span-3 md:col-span-4 px-3 py-2">
        {{--@include("article.partials.related-articles") --}}
        <x-related-articles :article="$article"/>

    </aside>
</x-app-layout>


