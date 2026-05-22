<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Customer Asset') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('customer-assets.update', $customerAsset) }}">
                    @method('PATCH')
                    @include('services.customer-assets._form', ['buttonLabel' => __('Save Asset')])
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
