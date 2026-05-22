<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Record Payment') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $sale->sale_number }} · {{ $statuses[$sale->status] ?? ucfirst($sale->status) }}</p>
            </div>
            <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">{{ __('Back to Sale') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="bg-white p-4 text-sm font-medium text-red-700 shadow-sm sm:rounded-lg">{{ __('Please review the payment details.') }}</div>
            @endif

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="grid gap-6 p-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Total') }}</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format((float) $sale->total, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Amount paid') }}</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format((float) $sale->amount_paid, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Balance due') }}</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format((float) $sale->balance_due, 2) }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('sales.payments.store', $sale) }}" class="grid gap-6 p-6 md:grid-cols-2">
                    @csrf

                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <x-text-input id="amount" name="amount" type="number" min="0.01" step="0.01" max="{{ $sale->balance_due }}" class="mt-1 block w-full" :value="old('amount', $sale->balance_due)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                    </div>

                    <div>
                        <x-input-label for="paid_at" :value="__('Paid at')" />
                        <x-text-input id="paid_at" name="paid_at" type="datetime-local" class="mt-1 block w-full" :value="old('paid_at', now()->format('Y-m-d\\TH:i'))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('paid_at')" />
                    </div>

                    <div>
                        <x-input-label for="payment_method" :value="__('Payment method')" />
                        <x-text-input id="payment_method" name="payment_method" type="text" class="mt-1 block w-full" :value="old('payment_method')" placeholder="Cash, GCash, Card" />
                        <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                    </div>

                    <div>
                        <x-input-label for="reference_number" :value="__('Reference number')" />
                        <x-text-input id="reference_number" name="reference_number" type="text" class="mt-1 block w-full" :value="old('reference_number')" />
                        <x-input-error class="mt-2" :messages="$errors->get('reference_number')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-primary-button>{{ __('Record Payment') }}</x-primary-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
