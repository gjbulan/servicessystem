<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Job Orders') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif
            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Number') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Customer') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Branch') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($jobOrders as $jobOrder)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $jobOrder->job_order_number }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $jobOrder->customer?->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $jobOrder->customerAsset?->name ?? $jobOrder->customerAsset?->assetType?->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $jobOrder->branch?->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $statuses[$jobOrder->status] ?? ucfirst($jobOrder->status) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium"><a href="{{ route('job-orders.show', $jobOrder) }}" class="text-gray-700 hover:text-gray-950">{{ __('View') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No job orders found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">{{ $jobOrders->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
