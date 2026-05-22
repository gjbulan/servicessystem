<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $booking->booking_reference }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $statuses[$booking->status] ?? ucfirst($booking->status) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($booking->status === 'pending')
                    <form method="POST" action="{{ route('bookings.confirm', $booking) }}">@csrf <x-primary-button>{{ __('Confirm') }}</x-primary-button></form>
                    <form method="POST" action="{{ route('bookings.no-show', $booking) }}">@csrf <button class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">{{ __('No Show') }}</button></form>
                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}">@csrf <button class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 hover:bg-red-50">{{ __('Cancel') }}</button></form>
                @endif
                @if ($booking->jobOrder)
                    <a href="{{ route('job-orders.show', $booking->jobOrder) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Job Order') }}</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 md:grid-cols-4">
                    <div><dt class="text-sm text-gray-500">{{ __('Customer') }}</dt><dd class="font-medium text-gray-900">{{ $booking->customer_name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Phone') }}</dt><dd class="font-medium text-gray-900">{{ $booking->phone }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Email') }}</dt><dd class="font-medium text-gray-900">{{ $booking->email ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Branch') }}</dt><dd class="font-medium text-gray-900">{{ $booking->branch?->name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Preferred') }}</dt><dd class="font-medium text-gray-900">{{ $booking->preferred_datetime?->format('M d, Y h:i A') ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Asset type') }}</dt><dd class="font-medium text-gray-900">{{ $booking->asset_type_name ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Lead source') }}</dt><dd class="font-medium text-gray-900">{{ $booking->lead_source ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Customer record') }}</dt><dd class="font-medium text-gray-900">{{ $booking->customer?->name ?? __('Not confirmed') }}</dd></div>
                    <div class="md:col-span-4"><dt class="text-sm text-gray-500">{{ __('Issue') }}</dt><dd class="font-medium text-gray-900">{{ $booking->issue_description ?? __('Not set') }}</dd></div>
                    <div class="md:col-span-4"><dt class="text-sm text-gray-500">{{ __('Internal notes') }}</dt><dd class="font-medium text-gray-900">{{ $booking->internal_notes ?? __('Not set') }}</dd></div>
                </dl>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Requested Services') }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($booking->services as $service)
                        <div class="flex items-center justify-between gap-4 p-6">
                            <p class="font-medium text-gray-900">{{ $service->service_name_snapshot }}</p>
                            <p class="text-sm text-gray-600">{{ number_format((float) $service->price_snapshot, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
