<x-jet-dropdown align="right" >
    <x-slot name="trigger">
            <span class="inline-flex rounded-md">
                <button type="button"
                        class="inline-flex border bg-primary-200 items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                    مدیریت
                    <svg class="mr-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                              clip-rule="evenodd"/>
                    </svg>
                </button>
            </span>
    </x-slot>

    <x-slot name="content">

        <x-jet-dropdown-link href="{{ route('filament.resources.articles.edit', $article->id) }}" >
            ویرایش
        </x-jet-dropdown-link>

        <x-jet-dropdown-link href="{{ route('translate-article', $article->id) }}" target='_blank'>
             ترجمه اتوماتیک
        </x-jet-dropdown-link>

        @php
            $designatedText = $article->is_translate_designated == 1 ? 'حذف از منتخب ترجمه' : 'منتخب ترجمه';
            $skipText = $article->is_skipped == 1 ? 'حذف از نادیده گرفتن' : 'نادیده گرفتن';
        @endphp
        <x-jet-dropdown-link href="{{ route('translate-designate-article', $article->id) }}">
            {{$designatedText}}
        </x-jet-dropdown-link>

        <x-jet-dropdown-link href="{{ route('skip-article', $article->id) }}">
            {{$skipText}}
        </x-jet-dropdown-link>

        <x-jet-dropdown-link href="{{ route('make-assets-local', $article->id) }}" target='_blank'>
            دانلود دارایی ها
        </x-jet-dropdown-link>


        <div class="border-t border-gray-100"></div>

    </x-slot>
</x-jet-dropdown>
