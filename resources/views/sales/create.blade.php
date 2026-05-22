<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Sale') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('sales.store') }}" class="p-6">
                    @include('sales._form', ['buttonLabel' => __('Save Sale')])
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
