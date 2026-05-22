<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Public Booking') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <p class="text-sm text-gray-500">{{ __('Public booking URL') }}</p>
                <a href="{{ $publicBookingUrl }}" class="mt-2 block break-all font-medium text-gray-900 hover:text-gray-700">{{ $publicBookingUrl }}</a>
                <p class="mt-4 text-sm text-gray-600">{{ __('Customers can submit service booking requests without creating an account. Requests stay pending until your team confirms them.') }}</p>
            </section>
        </div>
    </div>
</x-app-layout>
