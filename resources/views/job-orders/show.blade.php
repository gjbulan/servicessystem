<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $jobOrder->job_order_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $statuses[$jobOrder->status] ?? ucfirst($jobOrder->status) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($jobOrder->status !== 'completed')
                    <a href="{{ route('job-orders.edit', $jobOrder) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                    <a href="{{ route('job-orders.assign-technicians', $jobOrder) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">{{ __('Technicians') }}</a>
                    <form method="POST" action="{{ route('job-orders.complete', $jobOrder) }}">@csrf <x-primary-button>{{ __('Complete') }}</x-primary-button></form>
                    <form method="POST" action="{{ route('job-orders.cancel', $jobOrder) }}">@csrf <button class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 hover:bg-red-50">{{ __('Cancel') }}</button></form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-white p-4 text-sm font-medium text-red-700 shadow-sm sm:rounded-lg">{{ $errors->first() }}</div>
            @endif

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 md:grid-cols-4">
                    <div><dt class="text-sm text-gray-500">{{ __('Customer') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->customer?->name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Asset') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->customerAsset?->name ?? $jobOrder->customerAsset?->assetType?->name ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Branch') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->branch?->name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Created by') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->creator?->name ?? __('System') }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-sm text-gray-500">{{ __('Complaint') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->customer_complaint ?? __('Not set') }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-sm text-gray-500">{{ __('Inspection notes') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->inspection_notes ?? __('Not set') }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-sm text-gray-500">{{ __('Internal notes') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->internal_notes ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Started') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->started_at?->format('M d, Y h:i A') ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Completed') }}</dt><dd class="font-medium text-gray-900">{{ $jobOrder->completed_at?->format('M d, Y h:i A') ?? __('Not set') }}</dd></div>
                </dl>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6"><h3 class="text-lg font-semibold text-gray-900">{{ __('Technicians') }}</h3></div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($jobOrder->technicians as $technician)
                            <div class="p-6">
                                <p class="font-medium text-gray-900">{{ $technician->technician?->name }}{{ $technician->is_primary ? ' - '.__('Primary') : '' }}</p>
                                <p class="text-sm text-gray-500">{{ $technician->role }} {{ $technician->notes }}</p>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">{{ __('No technicians assigned.') }}</div>
                        @endforelse
                    </div>
                </section>

                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6"><h3 class="text-lg font-semibold text-gray-900">{{ __('Service History') }}</h3></div>
                    <div class="p-6 text-sm text-gray-600">
                        @if ($jobOrder->serviceHistory)
                            <p class="font-medium text-gray-900">{{ $jobOrder->serviceHistory->service_summary }}</p>
                            <p class="mt-1">{{ $jobOrder->serviceHistory->service_date?->format('M d, Y') }}</p>
                        @else
                            {{ __('Service history is created when the job order is completed.') }}
                        @endif
                    </div>
                </section>
            </div>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6"><h3 class="text-lg font-semibold text-gray-900">{{ __('Services') }}</h3></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($jobOrder->services as $service)
                                <tr><td class="px-6 py-4 font-medium text-gray-900">{{ $service->service_name_snapshot }}</td><td class="px-6 py-4 text-right text-gray-600">{{ number_format((float) $service->price_snapshot, 2) }}</td></tr>
                            @empty
                                <tr><td class="px-6 py-6 text-sm text-gray-500">{{ __('No services added.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6"><h3 class="text-lg font-semibold text-gray-900">{{ __('Items Used') }}</h3></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left font-semibold text-gray-600">{{ __('Item') }}</th><th class="px-6 py-3 text-right font-semibold text-gray-600">{{ __('Quantity') }}</th><th class="px-6 py-3 text-right font-semibold text-gray-600">{{ __('Selling Price') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($jobOrder->items as $item)
                                <tr>
                                    <td class="px-6 py-4"><div class="font-medium text-gray-900">{{ $item->item_name_snapshot }}</div><div class="text-sm text-gray-500">{{ $item->variant_name_snapshot }} {{ $item->sku_snapshot }}</div></td>
                                    <td class="px-6 py-4 text-right text-gray-600">{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td class="px-6 py-4 text-right text-gray-600">{{ number_format((float) $item->selling_price_snapshot, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-6 text-sm text-gray-500">{{ __('No items added.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
