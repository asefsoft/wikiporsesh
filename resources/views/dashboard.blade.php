<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- Main Content of page --}}
    <div class="bg-blue-100 col-span-9 px-3 py-2">
        {{implode("<br>", fake()->sentences(30))}}
    </div>

    {{-- Sidebar --}}
    <aside class="bg-red-100 col-span-3 px-3 py-2">
        {{implode("<br>", fake()->sentences(15))}}
    </aside>
</x-app-layout>
