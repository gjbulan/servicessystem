<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $company->name }} {{ __('Booking') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans text-gray-900 antialiased">
        <main class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-gray-950">{{ $company->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ __('Service booking request') }}</p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-md bg-white p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('public-bookings.store', ['company' => $company->slug]) }}" class="space-y-6 rounded-md bg-white p-6 shadow-sm">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
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
                        <x-input-label for="preferred_datetime" :value="__('Preferred date and time')" />
                        <x-text-input id="preferred_datetime" name="preferred_datetime" type="datetime-local" class="mt-1 block w-full" :value="old('preferred_datetime')" />
                        <x-input-error class="mt-2" :messages="$errors->get('preferred_datetime')" />
                    </div>

                    <div>
                        <x-input-label for="customer_name" :value="__('Customer name')" />
                        <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" :value="old('customer_name')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('customer_name')" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="asset_type_id" :value="__('Asset type')" />
                        <select id="asset_type_id" name="asset_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select asset type') }}</option>
                            @foreach ($assetTypes as $assetType)
                                <option value="{{ $assetType->id }}" @selected((int) old('asset_type_id') === $assetType->id)>{{ $assetType->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('asset_type_id')" />
                    </div>
                </div>

                <section>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ __('Services') }}</h2>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        @foreach ($services as $service)
                            <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(in_array((string) $service->id, old('services', []), true))>
                                <span>
                                    <span class="block font-medium text-gray-900">{{ $service->name }}</span>
                                    <span class="block text-sm text-gray-500">{{ number_format((float) $service->default_price, 2) }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('services')" />
                    <x-input-error class="mt-2" :messages="$errors->get('services.*')" />
                </section>

                <section class="grid gap-6 md:grid-cols-2">
                    @foreach (['asset_name' => __('Asset name'), 'brand' => __('Brand'), 'model' => __('Model'), 'year' => __('Year'), 'serial_number' => __('Serial number'), 'plate_number' => __('Plate number'), 'color' => __('Color'), 'lead_source' => __('Lead source')] as $field => $label)
                        <div>
                            <x-input-label :for="$field" :value="$label" />
                            <x-text-input :id="$field" :name="$field" type="text" class="mt-1 block w-full" :value="old($field)" />
                            <x-input-error class="mt-2" :messages="$errors->get($field)" />
                        </div>
                    @endforeach
                </section>

                <div>
                    <x-input-label for="issue_description" :value="__('Issue description')" />
                    <textarea id="issue_description" name="issue_description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('issue_description') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('issue_description')" />
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Notes')" />
                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                </div>

                <x-primary-button>{{ __('Submit Booking Request') }}</x-primary-button>
            </form>
        </main>
    </body>
</html>
