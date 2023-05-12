<div>
    @if($hasRelatedArticles())
        <div class="mt-2">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                مطالب مرتبط
            </h3>
        </div>

        <div class="grid grid-cols-1 gap-4 mt-3 ">
            @foreach($getRelatedArticles as $related)
                <div class="border border-gray-100 rounded-lg shadow-md p-4">
                    {{-- image --}}
                    <div class="flex justify-center">
                        <a href="{{ $related->getArticleDisplayUrl() }}">
                        <img data-src="{{ $related->image_url }}" alt="{{ $related->title }}" class="rounded-md w-full max-w-[500px] max-h-[245px] lazyload">
                        </a>
                    </div>
                    <div class="mt-2">
                        <a href="{{ $related->getArticleDisplayUrl() }}" class="text-lg font-bold text-gray-800">
                            {{ $related->title_fa }}
                        </a>
                        <p class="text-gray-600 mt-2">
                            {{ $getRelatedSmallDescription($related) }}
                        </p>
                        <div class="border border-gray-100 rounded-lg shadow-md p-2 mt-2 bg-gray-100">
                            <div class="">
                                <a href="{{ $getCategoryUrl }}"
                                   class="text-gray-800  text-center block">
                                    {{$getCategoryName}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

    @endif

</div>
