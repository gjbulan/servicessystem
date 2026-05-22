<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inventory Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 text-sm font-medium text-green-700">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            @forelse ($companies as $company)
                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $company->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $company->slug }}</p>
                            </div>

                            <span class="inline-flex w-fit items-center rounded-md bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
                                {{ ucfirst($company->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="max-w-3xl">
                            <h4 class="font-medium text-gray-900">{{ __('Enable Item Variants') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ __('ON: Items can have multiple variants like size, color, or specification.') }}
                            </p>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('OFF: The system hides variants from inventory forms and automatically creates one default variant for each item.') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('settings.inventory.update', $company->inventorySetting) }}" class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="enable_item_variants" value="0">

                            <label for="inventory-setting-{{ $company->inventorySetting->id }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                <input
                                    id="inventory-setting-{{ $company->inventorySetting->id }}"
                                    name="enable_item_variants"
                                    type="checkbox"
                                    value="1"
                                    @checked($company->inventorySetting->enable_item_variants)
                                    class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-500"
                                >
                                {{ $company->inventorySetting->enable_item_variants ? __('Yes') : __('No') }}
                            </label>

                            <x-primary-button>
                                {{ __('Save') }}
                            </x-primary-button>
                        </form>
                    </div>
                </section>
            @empty
                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-600">
                        {{ __('No companies are available yet.') }}
                    </div>
                </section>
            @endforelse
        </div>
    </div>
</x-app-layout>
