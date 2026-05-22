<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $sale->sale_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $statuses[$sale->status] ?? ucfirst($sale->status) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ((float) $sale->amount_paid <= 0 && ! in_array($sale->status, ['paid', 'partial', 'void'], true))
                    <a href="{{ route('sales.edit', $sale) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                @endif
                @if ($sale->status !== 'void' && (float) $sale->balance_due > 0)
                    <a href="{{ route('sales.payments', $sale) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Payments') }}</a>
                @endif
                <a href="{{ route('sales.print', $sale) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">{{ __('Print') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="grid gap-6 p-6 md:grid-cols-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Sale date') }}</p>
                        <p class="font-medium text-gray-900">{{ $sale->sale_date?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Branch') }}</p>
                        <p class="font-medium text-gray-900">{{ $sale->branch?->name ?? __('Archived branch') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Customer') }}</p>
                        <p class="font-medium text-gray-900">{{ $sale->customer?->name ?? __('Walk-in') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Created by') }}</p>
                        <p class="font-medium text-gray-900">{{ $sale->creator?->name ?? __('System') }}</p>
                    </div>
                    <div class="md:col-span-4">
                        <p class="text-sm text-gray-500">{{ __('Notes') }}</p>
                        <p class="font-medium text-gray-900">{{ $sale->notes ?? __('Not set') }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Items') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Item') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('SKU') }}</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Quantity') }}</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Unit Price') }}</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Line Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $item->item_name_snapshot }}</div>
                                        @if ($item->variant_name_snapshot)
                                            <div class="text-sm text-gray-500">{{ $item->variant_name_snapshot }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->sku_snapshot ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Payments') }}</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($sale->payments as $payment)
                            <div class="p-6">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $payment->payment_method ?? __('Payment') }}</p>
                                        <p class="text-sm text-gray-500">{{ $payment->paid_at?->format('M d, Y h:i A') }}</p>
                                    </div>
                                    <p class="font-semibold text-gray-900">{{ number_format((float) $payment->amount, 2) }}</p>
                                </div>
                                @if ($payment->reference_number || $payment->notes)
                                    <p class="mt-2 text-sm text-gray-500">{{ $payment->reference_number }} {{ $payment->notes }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">{{ __('No payments recorded yet.') }}</div>
                        @endforelse
                    </div>
                </section>

                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="space-y-4 p-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('Subtotal') }}</span>
                            <span class="font-medium text-gray-900">{{ number_format((float) $sale->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('Discount') }}</span>
                            <span class="font-medium text-gray-900">{{ number_format((float) $sale->discount_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('Tax') }}</span>
                            <span class="font-medium text-gray-900">{{ number_format((float) $sale->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-4">
                            <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                            <span class="font-semibold text-gray-900">{{ number_format((float) $sale->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('Amount paid') }}</span>
                            <span class="font-medium text-gray-900">{{ number_format((float) $sale->amount_paid, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base">
                            <span class="font-semibold text-gray-900">{{ __('Balance due') }}</span>
                            <span class="font-semibold text-gray-900">{{ number_format((float) $sale->balance_due, 2) }}</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
