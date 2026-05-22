<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Expense Category') }}</h2></x-slot>
    <div class="py-12"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8"><section class="bg-white p-6 shadow-sm sm:rounded-lg"><form method="POST" action="{{ route('expense-categories.update', $category) }}">@method('PUT') @include('accounting.expense-categories._form', ['buttonLabel' => __('Save Category')])</form></section></div></div>
</x-app-layout>
