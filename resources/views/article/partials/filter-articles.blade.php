@can('manage')
    {{-- actions menu--}}
    <div class="px-3 py-2 mr-3 my-4 border round-md">
        <div x-data="{
            filters: ['is_featured', 'is_translate_designated', 'is_popular', 'is_auto_translated', 'is_edited'],
            is_featured: false,
            is_translate_designated: false,
            is_popular: false,
            is_auto_translated: false,
            is_edited: false,

            init() {
                // set state of filters from url query string
                const urlParams = new URLSearchParams(window.location.search);
                let that = this;

                this.filters.forEach(function (filter){
                    let filterValue = urlParams.get(filter);
                    if(urlParams.has(filter) && filterValue !== '' && filterValue !== undefined) {
                        that[filter] = filterValue === 'true';
                    }
{{--                    console.log(filter, that[filter], filterValue);--}}
                });
            },

            get filteredItemsQuery() {
                let that = this;
                return this.filters.map(function (filter){
                    return filter + '=' + that[filter];
                }).join('&');
            },

            doFilter(){
                window.location.search = this.filteredItemsQuery;
            }
        }" class="flex">
            <div class="flex items-center ml-4">
                <input x-model="is_featured" id="is_featured" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_featured" class="mr-3 font-medium text-gray-900">مقالات ویژه</label>
            </div>

            <div class="flex items-center ml-4">
                <input x-model="is_translate_designated" id="is_translate_designated" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_translate_designated" class="mr-2 font-medium text-gray-900">منتخب ترجمه</label>
            </div>

            <div class="flex items-center ml-4">
                <input x-model="is_popular" id="is_popular" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_popular" class="mr-2 font-medium text-gray-900">پر بازدید</label>
            </div>

            <div class="flex items-center ml-4">
                <input x-model="is_auto_translated" id="is_auto_translated" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_auto_translated" class="mr-2 font-medium text-gray-900">ترجمه اتوماتیک شده</label>
            </div>

            <div class="flex items-center ml-4">
                <input x-model="is_edited" id="is_edited" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_edited" class="mr-2 font-medium text-gray-900">ویرایش شده</label>
            </div>

            <button @click="doFilter()" type="button" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 mr-2">برو</button>

        </div>

    </div>
@endcan
