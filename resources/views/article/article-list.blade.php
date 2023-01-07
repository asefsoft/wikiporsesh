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
        <x-article-collection :articleCollection="$collection">

        </x-article-collection>
    </section>

</x-app-layout>
