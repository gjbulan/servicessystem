@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Item name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $item->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($statuses as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $item->status) === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div>
        <x-input-label for="item_category_id" :value="__('Category')" />
        <select id="item_category_id" name="item_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('No category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('item_category_id', $item->item_category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('item_category_id')" />
    </div>

    <div>
        <x-input-label for="item_brand_id" :value="__('Brand')" />
        <select id="item_brand_id" name="item_brand_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('No brand') }}</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected((int) old('item_brand_id', $item->item_brand_id) === $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('item_brand_id')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $item->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $buttonLabel }}</x-primary-button>
    <a href="{{ route('inventory.items.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
</div>
