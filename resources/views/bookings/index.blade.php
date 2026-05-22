<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Bookings') }}</h2>
            <a href="{{ route('bookings.public-info') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Public Booking') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif
            <section class="bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('bookings.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Pending and confirmed') }}</option>
                            @foreach ($statuses as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>
            </section>
            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Reference') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Customer') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Branch') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Preferred') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($bookings as $booking)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $booking->booking_reference }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $booking->customer_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking->phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->branch?->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->preferred_datetime?->format('M d, Y h:i A') ?? __('Not set') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $statuses[$booking->status] ?? ucfirst($booking->status) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium"><a href="{{ route('bookings.show', $booking) }}" class="text-gray-700 hover:text-gray-950">{{ __('View') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No bookings found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">{{ $bookings->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
