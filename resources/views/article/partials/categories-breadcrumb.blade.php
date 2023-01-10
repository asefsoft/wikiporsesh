@foreach($categoriesBreadcrumb as $breadcrumb)
<nav class="flex mb-3" aria-label="Breadcrumb">
    <ol class="inline-flex items-center">
        @foreach($breadcrumb as $category)
        <li>
            <div class="flex items-center">
                <a href="{{$category->getCategoryUrl()}}" class="text-sm font-medium text-gray-700 ml-1 md:ml-2 ">
                    {{ $category->name_fa }}
                </a>
{{--                <img src="{{asset('static/stuff/angle-left.svg')}}" width="24" height="24">--}}
                @if(!$loop->last)
                    <svg aria-hidden="true" class="w-6 h-6 rotate-180 text-gray-400 ml-1 md:ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                @endif

            </div>
        </li>
        @endforeach
    </ol>
</nav>
@endforeach

