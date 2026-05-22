<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sales') }}</h2>
            <a href="{{ route('sales.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Create Sale') }}</a>
        </div>
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
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Sale') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Branch') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Customer') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Total') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Balance') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($sales as $sale)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $sale->sale_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $sale->sale_date?->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->branch?->name ?? __('Archived branch') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->customer?->name ?? __('Walk-in') }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-600">{{ number_format((float) $sale->total, 2) }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-600">{{ number_format((float) $sale->balance_due, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $statuses[$sale->status] ?? ucfirst($sale->status) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('sales.show', $sale) }}" class="text-gray-700 hover:text-gray-950">{{ __('View') }}</a>
                                        @if ((float) $sale->amount_paid <= 0 && ! in_array($sale->status, ['paid', 'partial', 'void'], true))
                                            <a href="{{ route('sales.edit', $sale) }}" class="ms-3 text-gray-700 hover:text-gray-950">{{ __('Edit') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No sales found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">{{ $sales->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
