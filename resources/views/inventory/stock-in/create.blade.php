<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Stock In') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <section class="bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('inventory.stock-in.store') }}" class="grid gap-6 p-6 md:grid-cols-2">
                    @csrf

                    <div>
                        <x-input-label for="branch_id" :value="__('Branch')" />
                        <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">{{ __('Select branch') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((int) old('branch_id') === $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('branch_id')" />
                    </div>

                    <div>
                        <x-input-label for="item_variant_id" :value="__('Item variant')" />
                        <select id="item_variant_id" name="item_variant_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">{{ __('Select variant') }}</option>
                            @foreach ($variants as $variant)
                                <option value="{{ $variant->id }}" @selected((int) old('item_variant_id') === $variant->id)>
                                    {{ $variant->item?->brand?->name ? $variant->item->brand->name.' - ' : '' }}{{ $variant->item?->name }} - {{ $variant->variant_name }}{{ $variant->sku ? ' ('.$variant->sku.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('item_variant_id')" />
                    </div>

                    <div>
                        <x-input-label for="transaction_type" :value="__('Transaction type')" />
                        <select id="transaction_type" name="transaction_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @foreach ($transactionTypes as $typeValue => $typeLabel)
                                <option value="{{ $typeValue }}" @selected(old('transaction_type', 'stock_in') === $typeValue)>{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('transaction_type')" />
                    </div>

                    <div>
                        <x-input-label for="quantity" :value="__('Quantity')" />
                        <x-text-input id="quantity" name="quantity" type="number" min="0.01" step="0.01" class="mt-1 block w-full" :value="old('quantity')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-primary-button>{{ __('Record Transaction') }}</x-primary-button>
                    </div>
                </form>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent Inventory Transactions') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Latest stock movements recorded for your company.') }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Date') }}</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Branch') }}</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Item') }}</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Variant') }}</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('SKU') }}</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Type') }}</th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Quantity') }}</th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Previous') }}</th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('New') }}</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Notes') }}</th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Created by') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($recentTransactions as $transaction)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $transaction->created_at?->format('M d, Y h:i A') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $transaction->branch?->name ?? __('Archived branch') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $transaction->itemVariant?->item?->name ?? __('Archived item') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $transaction->itemVariant?->variant_name ?? __('Archived variant') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $transaction->itemVariant?->sku ?: '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $transactionTypes[$transaction->transaction_type] ?? str($transaction->transaction_type)->headline() }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">{{ number_format((float) $transaction->quantity, 2) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">{{ number_format((float) $transaction->previous_stock, 2) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700">{{ number_format((float) $transaction->new_stock, 2) }}</td>
                                    <td class="min-w-48 px-4 py-3 text-gray-700">{{ $transaction->notes ?: '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $transaction->creator?->name ?? __('System') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-8 text-center text-gray-500">{{ __('No inventory transactions have been recorded yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
