<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Expense') }}</h2></x-slot>
    <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><section class="bg-white p-6 shadow-sm sm:rounded-lg"><form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">@method('PUT') @include('accounting.expenses._form', ['buttonLabel' => __('Save Expense')])</form></section></div></div>
</x-app-layout>
