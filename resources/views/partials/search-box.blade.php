{{--make a search box for use in top navbar with tailwindcss--}}

<div class="w-3/4 sm:w-80 md:w-96 flex flex-col justify-center">
    <label for="search" class="sr-only">جستجو</label>
    <div class="relative rounded-md  shadow-sm ">
        <form action="{{route('articles-search')}}" method="get">
            <input type="text" name="q"  autocomplete="off" class="placeholder-secondary-400 border-0 bo1rder b1order-secondary-300 focus:ring-primary-500
                    focus:b1order-primary-500 form-input block w-full sm:text-sm rounded-md transition ease-in-out duration-100 focus:outline-none shadow-sm flex pl-[62px]"
                   placeholder="جستجو"
            >

            <div class="absolute inset-y-0 left-0 flex items-center">
                <button type="submit" class="outline-none inline-flex justify-center items-center group transition-all ease-in duration-150 focus:ring-2 focus:ring-offset-2 hover:shadow-sm disabled:opacity-80 disabled:cursor-not-allowed gap-x-2 text-sm px-4 py-2     ring-primary-500 text-white bg-primary-500 hover:bg-primary-600 hover:ring-primary-600 h-full rounded-l-md" >
                    <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
