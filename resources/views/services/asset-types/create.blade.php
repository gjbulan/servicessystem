<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Asset Type') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('asset-types.store') }}">
                    @include('services.asset-types._form', ['buttonLabel' => __('Create Asset Type')])
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
