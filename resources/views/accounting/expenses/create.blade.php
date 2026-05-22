<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Record Expense') }}</h2></x-slot>
    <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8"><section class="bg-white p-6 shadow-sm sm:rounded-lg"><form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">@include('accounting.expenses._form', ['buttonLabel' => __('Record Expense')])</form></section></div></div>
</x-app-layout>
