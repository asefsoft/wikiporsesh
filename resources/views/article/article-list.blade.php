<?php
/** @var \App\Models\Article $article */
/** @var \App\Article\AssetsManager\AssetsManager $assetManager */
?>
<x-app-layout>
    <x-slot name="header">
        {{-- Article Title --}}
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{$collection->getTitle()}}
            @can('manage')

            @endcan
        </h1>
    </x-slot>

    {{-- Main Content of page --}}
    <section id="article-collection" class="col-span-10 px-5 py-3">

        @if(!empty($categoriesBreadcrumb))
            <div class="mb-2 mr-2">
            @include('article.partials.categories-breadcrumb')
            </div>
        @endif

        @include('article.partials.filter-articles')

        <x-article-collection :articleCollection="$collection"></x-article-collection>
    </section>

</x-app-layout>
