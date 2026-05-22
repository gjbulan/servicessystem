@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="customer_id" :value="__('Customer')" />
        <select id="customer_id" name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">{{ __('Select customer') }}</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected((int) old('customer_id', $customerAsset->customer_id) === $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('customer_id')" />
    </div>

    <div>
        <x-input-label for="asset_type_id" :value="__('Asset type')" />
        <select id="asset_type_id" name="asset_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('No asset type') }}</option>
            @foreach ($assetTypes as $assetType)
                <option value="{{ $assetType->id }}" @selected((int) old('asset_type_id', $customerAsset->asset_type_id) === $assetType->id)>{{ $assetType->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('asset_type_id')" />
    </div>

    @foreach (['name' => __('Asset name'), 'brand' => __('Brand'), 'model' => __('Model'), 'year' => __('Year'), 'serial_number' => __('Serial number'), 'plate_number' => __('Plate number'), 'color' => __('Color')] as $field => $label)
        <div>
            <x-input-label :for="$field" :value="$label" />
            <x-text-input :id="$field" :name="$field" type="text" class="mt-1 block w-full" :value="old($field, $customerAsset->{$field})" />
            <x-input-error class="mt-2" :messages="$errors->get($field)" />
        </div>
    @endforeach

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($statuses as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $customerAsset->status) === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notes" :value="__('Notes')" />
        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $customerAsset->notes) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $buttonLabel }}</x-primary-button>
    <a href="{{ route('customer-assets.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
</div>
