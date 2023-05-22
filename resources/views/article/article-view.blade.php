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
    <section id="article-content" class="col-span-7 pl-5 pr-5 md:pr-0 py-3 leading-loose">

        {{-- Categories Breadcrumb --}}
        @include("article.partials.categories-breadcrumb")

        {{-- Article Desc --}}
        <p id="article-desc" class="mt-3 mb-4">
            {{$article->description_fa}}
        </p>

        {{-- Sections Titles --}}
        @include("article.partials.sections")

        {{-- Article Sections --}}
        @foreach($article->sections as $sectionIndex => $section)
            @include("article.article-section", [$section, $sectionIndex])
        @endforeach
    </section>

    {{-- Sidebar --}}
    <aside class="col-span-3 px-3 py-2">
        {{--@include("article.partials.related-articles") --}}
        <x-related-articles :article="$article"/>

    </aside>
</x-app-layout>


