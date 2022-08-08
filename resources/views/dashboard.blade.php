<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="md:grid md:grid-cols-6 md:gap-6">
                    <div class="bg-blue-100 col-span-4">1st col</div>
                    <div class="bg-red-100 col-span-2">2nd col</div>
                </div>
                <x-jet-welcome />
            </div>
        </div>
    </div>
</x-app-layout>
