<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $customerAsset->name ?? $customerAsset->assetType?->name ?? __('Customer Asset') }}</h2>
            <a href="{{ route('customer-assets.edit', $customerAsset) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Edit') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 md:grid-cols-3">
                    <div><dt class="text-sm text-gray-500">{{ __('Customer') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->customer?->name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Asset type') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->assetType?->name ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Status') }}</dt><dd class="font-medium text-gray-900">{{ $statuses[$customerAsset->status] ?? ucfirst($customerAsset->status) }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Brand') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->brand ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Model') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->model ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Year') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->year ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Serial') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->serial_number ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Plate') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->plate_number ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Color') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->color ?? __('Not set') }}</dd></div>
                    <div class="md:col-span-3"><dt class="text-sm text-gray-500">{{ __('Notes') }}</dt><dd class="font-medium text-gray-900">{{ $customerAsset->notes ?? __('Not set') }}</dd></div>
                </dl>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Service History') }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($customerAsset->serviceHistories as $history)
                        <div class="p-6">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-medium text-gray-900">{{ $history->service_summary }}</p>
                                <p class="text-sm text-gray-500">{{ $history->service_date?->format('M d, Y') }}</p>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ $history->notes }}</p>
                        </div>
                    @empty
                        <div class="p-6 text-sm text-gray-500">{{ __('No service history yet.') }}</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
