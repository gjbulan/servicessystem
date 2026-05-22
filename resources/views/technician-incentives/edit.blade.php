<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Technician Incentive') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="mb-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Job order') }}</p>
                        <p class="font-medium text-gray-900">{{ $incentive->jobOrder?->job_order_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Technician') }}</p>
                        <p class="font-medium text-gray-900">{{ $incentive->technician?->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Service') }}</p>
                        <p class="font-medium text-gray-900">{{ $incentive->service_name_snapshot }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Default amount') }}</p>
                        <p class="font-medium text-gray-900">{{ number_format((float) $incentive->default_amount, 2) }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('technician-incentives.update', $incentive) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="override_amount" :value="__('Override amount')" />
                        <x-text-input id="override_amount" name="override_amount" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('override_amount', $incentive->override_amount)" />
                        <x-input-error class="mt-2" :messages="$errors->get('override_amount')" />
                    </div>

                    <div>
                        <x-input-label for="override_reason" :value="__('Override reason')" />
                        <textarea id="override_reason" name="override_reason" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('override_reason', $incentive->override_reason) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('override_reason')" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ __('Save Incentive') }}</x-primary-button>
                        <a href="{{ route('technician-incentives.show', $incentive) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
