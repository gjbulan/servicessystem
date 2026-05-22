@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Service name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="service_category_id" :value="__('Category')" />
        <select id="service_category_id" name="service_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('No category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('service_category_id', $service->service_category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('service_category_id')" />
    </div>

    <div>
        <x-input-label for="default_price" :value="__('Default price')" />
        <x-text-input id="default_price" name="default_price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('default_price', $service->default_price ?? '0.00')" required />
        <x-input-error class="mt-2" :messages="$errors->get('default_price')" />
    </div>

    <div>
        <x-input-label for="estimated_duration_minutes" :value="__('Estimated duration minutes')" />
        <x-text-input id="estimated_duration_minutes" name="estimated_duration_minutes" type="number" min="1" class="mt-1 block w-full" :value="old('estimated_duration_minutes', $service->estimated_duration_minutes)" />
        <x-input-error class="mt-2" :messages="$errors->get('estimated_duration_minutes')" />
    </div>

    <div>
        <x-input-label for="default_incentive_amount" :value="__('Default incentive amount')" />
        <x-text-input id="default_incentive_amount" name="default_incentive_amount" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('default_incentive_amount', $service->default_incentive_amount)" />
        <x-input-error class="mt-2" :messages="$errors->get('default_incentive_amount')" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($statuses as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $service->status) === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $service->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $buttonLabel }}</x-primary-button>
    <a href="{{ route('services.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
</div>
