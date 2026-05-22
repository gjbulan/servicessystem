<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $service->name }}</h2>
            <a href="{{ route('services.edit', $service) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Edit') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 md:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Category') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $service->category?->name ?? __('Uncategorized') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Status') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $statuses[$service->status] ?? ucfirst($service->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Default price') }}</dt>
                        <dd class="font-medium text-gray-900">{{ number_format((float) $service->default_price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Estimated duration') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $service->estimated_duration_minutes ? $service->estimated_duration_minutes.' minutes' : __('Not set') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Default incentive amount') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $service->default_incentive_amount !== null ? number_format((float) $service->default_incentive_amount, 2) : __('Not set') }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-500">{{ __('Description') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $service->description ?? __('Not set') }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</x-app-layout>
