@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="name" :value="__('Branch name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $branch->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $branch->code)" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $branch->email)" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $branch->phone)" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="manager_name" :value="__('Manager name')" />
        <x-text-input id="manager_name" name="manager_name" type="text" class="mt-1 block w-full" :value="old('manager_name', $branch->manager_name)" />
        <x-input-error class="mt-2" :messages="$errors->get('manager_name')" />
    </div>

    <div>
        <x-input-label for="operating_hours" :value="__('Operating hours')" />
        <x-text-input id="operating_hours" name="operating_hours" type="text" class="mt-1 block w-full" :value="old('operating_hours', $branch->operating_hours)" />
        <x-input-error class="mt-2" :messages="$errors->get('operating_hours')" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($statuses as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $branch->status) === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="address" :value="__('Address')" />
        <textarea id="address" name="address" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $branch->address) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $buttonLabel }}</x-primary-button>
    <a href="{{ route('branches.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
</div>
