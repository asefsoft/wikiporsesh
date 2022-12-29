<?php
/** @var \App\Models\Article $article */
/** @var \App\Article\AssetsManager\AssetsManager $assetManager */
?>
<x-app-layout>
    <x-slot name="header">
        {{-- Article Title --}}
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $article->title_fa }}
            @can('manage')
                - <a class="text-sm" href="{{ $article->getArticleSourceUrl() }}" rel="noopener noreferer">منبع</a>
                <span class="text-sm text-gray-400">
                {{{  $assetManager->getAssetStatusText() }}}
                </span>
            @endcan
        </h1>
    </x-slot>

    {{-- Main Content of page --}}
    <section id="article-content" class="col-span-7 px-5 py-3">
        {{-- Article Desc --}}
        <p id="article-desc" class="mt-3 mb-4">
            {{$article->description_fa}}
        </p>

        {{-- Article Sections --}}
        @foreach($article->sections as $sectionIndex =>$section)
            @include("article.article-section", [$section, $sectionIndex])
        @endforeach
    </section>

    {{-- Sidebar --}}
    <aside class="bg-red-100 col-span-3 px-3 py-2">
        {{implode("<br>", fake()->sentences(15))}}
    </aside>
</x-app-layout>
