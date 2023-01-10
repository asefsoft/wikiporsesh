<?php
/** @var \Illuminate\Support\Collection $allCategories */
/** @var \App\Models\Category $category */
?>
<x-app-layout>
    <x-slot name="header">
        {{-- Article Title --}}
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            دسته بندی ها
            @can('manage')

            @endcan
        </h1>
    </x-slot>

    {{-- Main Content of page --}}
    <section id="all-categories" class="col-span-10 px-3 sm:px-5 py-3">

        {{--  Root Categories--}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 mb-5">
            @foreach($allCategories as $rootCategory)
                <div class="p-3 m-2 bg-primary-200 rounded">
                    <a href="{{$rootCategory->first()->getCategoryUrl()}}" class="text-sm font-medium text-gray-700">
                        {{ $rootCategory->first()->name_fa }}
                    </a>
                </div>
            @endforeach
        </div>

        {{--  All Nested Categories--}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($allCategories as $rootCategory)
            <div class="p-3 m-2 bg-primary-200 rounded">
                @foreach($rootCategory as $category)
                    @if($category->depth <= 2)
                    <div style="margin-right: {{$category->depth * 20}}px;" class="{{ $category->depth == 1 ? 'mt-2' : '' }}">
        {{--                {{ $category->name_fa }} {{ $category->depth }}<br>--}}
                        <a href="{{$category->getCategoryUrl()}}" class="text-sm font-medium text-gray-700 leading-relaxed"
                           style="font-size: {{17 - $category->depth * 2}}px"
                        >
                            {{ $category->name_fa }}
                        </a>
                    </div>
                    @endif
            @endforeach
            </div>
        @endforeach
        </div>
    </section>

</x-app-layout>
