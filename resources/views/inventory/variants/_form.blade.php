@csrf

@php
    $attributesText = old('attributes_json', $variant->attributes_json ? json_encode($variant->attributes_json, JSON_PRETTY_PRINT) : null);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="item_id" :value="__('Item')" />
        <select id="item_id" name="item_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">{{ __('Select item') }}</option>
            @foreach ($items as $item)
                <option value="{{ $item->id }}" @selected((int) old('item_id', $variant->item_id) === $item->id)>
                    {{ $item->brand?->name ? $item->brand->name.' - ' : '' }}{{ $item->name }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('item_id')" />
    </div>

    <div>
        <x-input-label for="variant_name" :value="__('Variant name')" />
        <x-text-input id="variant_name" name="variant_name" type="text" class="mt-1 block w-full" :value="old('variant_name', $variant->variant_name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('variant_name')" />
    </div>

    <div>
        <x-input-label for="sku" :value="__('SKU')" />
        <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku', $variant->sku)" />
        <x-input-error class="mt-2" :messages="$errors->get('sku')" />
    </div>

    <div>
        <x-input-label for="barcode" :value="__('Barcode')" />
        <x-text-input id="barcode" name="barcode" type="text" class="mt-1 block w-full" :value="old('barcode', $variant->barcode)" />
        <x-input-error class="mt-2" :messages="$errors->get('barcode')" />
    </div>

    <div>
        <x-input-label for="cost_price" :value="__('Cost price')" />
        <x-text-input id="cost_price" name="cost_price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('cost_price', $variant->cost_price)" required />
        <x-input-error class="mt-2" :messages="$errors->get('cost_price')" />
    </div>

    <div>
        <x-input-label for="selling_price" :value="__('Selling price')" />
        <x-text-input id="selling_price" name="selling_price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('selling_price', $variant->selling_price)" required />
        <x-input-error class="mt-2" :messages="$errors->get('selling_price')" />
    </div>

    <div>
        <x-input-label for="unit_type" :value="__('Unit type')" />
        <x-text-input id="unit_type" name="unit_type" type="text" class="mt-1 block w-full" :value="old('unit_type', $variant->unit_type)" placeholder="pcs, liter, set" />
        <x-input-error class="mt-2" :messages="$errors->get('unit_type')" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($statuses as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $variant->status) === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="attributes_json" :value="__('Attributes JSON')" />
        <textarea id="attributes_json" name="attributes_json" rows="5" class="mt-1 block w-full font-mono text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $attributesText }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('attributes_json')" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $buttonLabel }}</x-primary-button>
    <a href="{{ route('inventory.variants.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
</div>
