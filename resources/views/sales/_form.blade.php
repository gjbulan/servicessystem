@csrf

@php
    $rows = old('items', $lineItems);
    $rows = $rows !== [] ? $rows : [['item_variant_id' => '', 'quantity' => '1', 'unit_price' => '']];
@endphp

<div data-sale-form class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="branch_id" :value="__('Branch')" />
            <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">{{ __('Select branch') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((int) old('branch_id', $sale->branch_id) === $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('branch_id')" />
        </div>

        <div>
            <x-input-label for="customer_id" :value="__('Customer')" />
            <select id="customer_id" name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('Walk-in customer') }}</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((int) old('customer_id', $sale->customer_id) === $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('customer_id')" />
        </div>

        <div>
            <x-input-label for="sale_date" :value="__('Sale date')" />
            <x-text-input id="sale_date" name="sale_date" type="date" class="mt-1 block w-full" :value="old('sale_date', optional($sale->sale_date)->format('Y-m-d') ?? $sale->sale_date)" required />
            <x-input-error class="mt-2" :messages="$errors->get('sale_date')" />
        </div>

        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ($allowedStatuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $sale->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('status')" />
        </div>
    </div>

    <section class="overflow-hidden rounded-md border border-gray-200">
        <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
            <h3 class="font-semibold text-gray-900">{{ __('Sale Items') }}</h3>
            <button type="button" data-add-line class="inline-flex items-center rounded-md bg-gray-800 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Add Item') }}</button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">{{ $usesItemVariants ? __('Item / Variant') : __('Item') }}</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Quantity') }}</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Unit Price') }}</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Line Total') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="sale-line-items" class="divide-y divide-gray-200 bg-white">
                    @foreach ($rows as $index => $row)
                        <tr data-sale-line>
                            <td class="min-w-72 px-4 py-3">
                                <select name="items[{{ $index }}][item_variant_id]" class="sale-item-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">{{ $usesItemVariants ? __('Select variant') : __('Select item') }}</option>
                                    @foreach ($variants as $variant)
                                        <option
                                            value="{{ $variant->id }}"
                                            data-price="{{ $variant->selling_price }}"
                                            data-cost="{{ $variant->cost_price }}"
                                            @selected((int) ($row['item_variant_id'] ?? '') === $variant->id)
                                        >
                                            @if ($usesItemVariants)
                                                {{ $variant->item?->brand?->name ? $variant->item->brand->name.' - ' : '' }}{{ $variant->item?->name }} - {{ $variant->variant_name }}{{ $variant->sku ? ' ('.$variant->sku.')' : '' }}
                                            @else
                                                {{ $variant->item?->brand?->name ? $variant->item->brand->name.' - ' : '' }}{{ $variant->item?->name }}{{ $variant->sku ? ' ('.$variant->sku.')' : '' }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('items.'.$index.'.item_variant_id')" />
                            </td>
                            <td class="px-4 py-3">
                                <x-text-input name="items[{{ $index }}][quantity]" type="number" min="0.01" step="0.01" class="sale-quantity block w-28 text-right" :value="$row['quantity'] ?? '1'" required />
                                <x-input-error class="mt-2" :messages="$errors->get('items.'.$index.'.quantity')" />
                            </td>
                            <td class="px-4 py-3">
                                <x-text-input name="items[{{ $index }}][unit_price]" type="number" min="0" step="0.01" class="sale-unit-price block w-32 text-right" :value="$row['unit_price'] ?? ''" required />
                                <x-input-error class="mt-2" :messages="$errors->get('items.'.$index.'.unit_price')" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-900">
                                <span data-line-total>0.00</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" data-remove-line class="text-sm font-medium text-red-600 hover:text-red-800">{{ __('Remove') }}</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="notes" :value="__('Notes')" />
            <textarea id="notes" name="notes" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $sale->notes) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
        </div>

        <div class="space-y-4 rounded-md border border-gray-200 p-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">{{ __('Subtotal') }}</span>
                <span class="font-medium text-gray-900" data-sale-subtotal>0.00</span>
            </div>

            <div>
                <x-input-label for="discount_amount" :value="__('Discount amount')" />
                <x-text-input id="discount_amount" name="discount_amount" type="number" min="0" step="0.01" class="sale-total-input mt-1 block w-full text-right" :value="old('discount_amount', $sale->discount_amount ?? '0.00')" />
                <x-input-error class="mt-2" :messages="$errors->get('discount_amount')" />
            </div>

            <div>
                <x-input-label for="tax_amount" :value="__('Tax amount')" />
                <x-text-input id="tax_amount" name="tax_amount" type="number" min="0" step="0.01" class="sale-total-input mt-1 block w-full text-right" :value="old('tax_amount', $sale->tax_amount ?? '0.00')" />
                <x-input-error class="mt-2" :messages="$errors->get('tax_amount')" />
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 pt-4 text-base">
                <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                <span class="font-semibold text-gray-900" data-sale-total>0.00</span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $buttonLabel }}</x-primary-button>
        <a href="{{ route('sales.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
    </div>
</div>

<template id="sale-line-template">
    <tr data-sale-line>
        <td class="min-w-72 px-4 py-3">
            <select name="items[__INDEX__][item_variant_id]" class="sale-item-select block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">{{ $usesItemVariants ? __('Select variant') : __('Select item') }}</option>
                @foreach ($variants as $variant)
                    <option value="{{ $variant->id }}" data-price="{{ $variant->selling_price }}" data-cost="{{ $variant->cost_price }}">
                        @if ($usesItemVariants)
                            {{ $variant->item?->brand?->name ? $variant->item->brand->name.' - ' : '' }}{{ $variant->item?->name }} - {{ $variant->variant_name }}{{ $variant->sku ? ' ('.$variant->sku.')' : '' }}
                        @else
                            {{ $variant->item?->brand?->name ? $variant->item->brand->name.' - ' : '' }}{{ $variant->item?->name }}{{ $variant->sku ? ' ('.$variant->sku.')' : '' }}
                        @endif
                    </option>
                @endforeach
            </select>
        </td>
        <td class="px-4 py-3">
            <input name="items[__INDEX__][quantity]" type="number" min="0.01" step="0.01" value="1" class="sale-quantity block w-28 rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </td>
        <td class="px-4 py-3">
            <input name="items[__INDEX__][unit_price]" type="number" min="0" step="0.01" class="sale-unit-price block w-32 rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </td>
        <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-900">
            <span data-line-total>0.00</span>
        </td>
        <td class="px-4 py-3 text-right">
            <button type="button" data-remove-line class="text-sm font-medium text-red-600 hover:text-red-800">{{ __('Remove') }}</button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-sale-form]');

        if (!form) {
            return;
        }

        const tbody = document.getElementById('sale-line-items');
        const template = document.getElementById('sale-line-template');
        const addButton = form.querySelector('[data-add-line]');
        const subtotalTarget = form.querySelector('[data-sale-subtotal]');
        const totalTarget = form.querySelector('[data-sale-total]');
        const discountInput = form.querySelector('[name="discount_amount"]');
        const taxInput = form.querySelector('[name="tax_amount"]');

        const amount = (value) => Number.parseFloat(value || '0') || 0;
        const money = (value) => amount(value).toFixed(2);

        const recalculate = () => {
            let subtotal = 0;

            tbody.querySelectorAll('[data-sale-line]').forEach((line) => {
                const quantity = amount(line.querySelector('.sale-quantity')?.value);
                const unitPrice = amount(line.querySelector('.sale-unit-price')?.value);
                const lineTotal = quantity * unitPrice;

                subtotal += lineTotal;
                line.querySelector('[data-line-total]').textContent = money(lineTotal);
            });

            subtotalTarget.textContent = money(subtotal);
            totalTarget.textContent = money(Math.max(subtotal - amount(discountInput.value) + amount(taxInput.value), 0));
        };

        const bindLine = (line) => {
            const select = line.querySelector('.sale-item-select');
            const quantity = line.querySelector('.sale-quantity');
            const unitPrice = line.querySelector('.sale-unit-price');
            const removeButton = line.querySelector('[data-remove-line]');

            select.addEventListener('change', () => {
                const selected = select.selectedOptions[0];

                if (selected?.dataset.price !== undefined) {
                    unitPrice.value = money(selected.dataset.price);
                }

                recalculate();
            });

            quantity.addEventListener('input', recalculate);
            unitPrice.addEventListener('input', recalculate);
            removeButton.addEventListener('click', () => {
                if (tbody.querySelectorAll('[data-sale-line]').length > 1) {
                    line.remove();
                    recalculate();
                }
            });
        };

        tbody.querySelectorAll('[data-sale-line]').forEach(bindLine);
        form.querySelectorAll('.sale-total-input').forEach((input) => input.addEventListener('input', recalculate));

        addButton.addEventListener('click', () => {
            const index = tbody.querySelectorAll('[data-sale-line]').length;
            const wrapper = document.createElement('tbody');
            wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', index);
            const line = wrapper.firstElementChild;

            tbody.appendChild(line);
            bindLine(line);
            recalculate();
        });

        recalculate();
    });
</script>
