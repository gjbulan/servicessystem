<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Category') }}</h2></x-slot>
    <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><section class="bg-white shadow-sm sm:rounded-lg"><form method="POST" action="{{ route('inventory.categories.update', $category) }}" class="p-6">@method('PUT')@include('inventory.categories._form', ['buttonLabel' => __('Save Changes')])</form></section></div></div>
</x-app-layout>
