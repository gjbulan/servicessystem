<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Job Order') }} {{ $jobOrder->job_order_number }}</h2>
    </x-slot>

    @php
        $serviceRows = old('services', $serviceRows);
        $itemRows = old('items', $itemRows);

        while (count($serviceRows) < 5) {
            $serviceRows[] = ['service_id' => '', 'service_name_snapshot' => '', 'price_snapshot' => '', 'status' => '', 'notes' => ''];
        }

        while (count($itemRows) < 5) {
            $itemRows[] = ['item_variant_id' => '', 'item_name_snapshot' => '', 'quantity' => '', 'cost_price_snapshot' => '', 'selling_price_snapshot' => '', 'notes' => ''];
        }
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('job-orders.update', $jobOrder) }}" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($statuses as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" @selected(old('status', $jobOrder->status) === $statusValue)>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>

                        <div>
                            <x-input-label for="started_at" :value="__('Started at')" />
                            <x-text-input id="started_at" name="started_at" type="datetime-local" class="mt-1 block w-full" :value="old('started_at', $jobOrder->started_at?->format('Y-m-d\TH:i'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                        </div>

                        <div>
                            <x-input-label for="approval_status" :value="__('Approval status')" />
                            <x-text-input id="approval_status" name="approval_status" type="text" class="mt-1 block w-full" :value="old('approval_status', $jobOrder->approval_status)" />
                            <x-input-error class="mt-2" :messages="$errors->get('approval_status')" />
                        </div>

                        <div>
                            <x-input-label for="approval_notes" :value="__('Approval notes')" />
                            <x-text-input id="approval_notes" name="approval_notes" type="text" class="mt-1 block w-full" :value="old('approval_notes', $jobOrder->approval_notes)" />
                            <x-input-error class="mt-2" :messages="$errors->get('approval_notes')" />
                        </div>

                        <div>
                            <x-input-label for="customer_complaint" :value="__('Customer complaint')" />
                            <textarea id="customer_complaint" name="customer_complaint" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('customer_complaint', $jobOrder->customer_complaint) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('customer_complaint')" />
                        </div>

                        <div>
                            <x-input-label for="inspection_notes" :value="__('Inspection notes')" />
                            <textarea id="inspection_notes" name="inspection_notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('inspection_notes', $jobOrder->inspection_notes) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('inspection_notes')" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="internal_notes" :value="__('Internal notes')" />
                            <textarea id="internal_notes" name="internal_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('internal_notes', $jobOrder->internal_notes) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('internal_notes')" />
                        </div>
                    </div>

                    <section class="overflow-hidden rounded-md border border-gray-200">
                        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                            <h3 class="font-semibold text-gray-900">{{ __('Services Performed') }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">{{ __('Service') }}</th><th class="px-4 py-3 text-left">{{ __('Manual name') }}</th><th class="px-4 py-3 text-right">{{ __('Price') }}</th><th class="px-4 py-3 text-left">{{ __('Status') }}</th><th class="px-4 py-3 text-left">{{ __('Notes') }}</th></tr></thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($serviceRows as $index => $row)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <select name="services[{{ $index }}][service_id]" class="block w-56 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">{{ __('Select service') }}</option>
                                                    @foreach ($services as $service)
                                                        <option value="{{ $service->id }}" @selected((int) ($row['service_id'] ?? '') === $service->id)>{{ $service->name }}</option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('services.'.$index.'.service_id')" />
                                            </td>
                                            <td class="px-4 py-3"><input name="services[{{ $index }}][service_name_snapshot]" value="{{ $row['service_name_snapshot'] ?? '' }}" class="block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                            <td class="px-4 py-3"><input name="services[{{ $index }}][price_snapshot]" type="number" min="0" step="0.01" value="{{ $row['price_snapshot'] ?? '' }}" class="block w-28 rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                            <td class="px-4 py-3"><input name="services[{{ $index }}][status]" value="{{ $row['status'] ?? '' }}" class="block w-36 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                            <td class="px-4 py-3"><input name="services[{{ $index }}][notes]" value="{{ $row['notes'] ?? '' }}" class="block w-56 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-md border border-gray-200">
                        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                            <h3 class="font-semibold text-gray-900">{{ __('Items Used') }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">{{ __('Inventory item') }}</th><th class="px-4 py-3 text-left">{{ __('Manual name') }}</th><th class="px-4 py-3 text-right">{{ __('Qty') }}</th><th class="px-4 py-3 text-right">{{ __('Cost') }}</th><th class="px-4 py-3 text-right">{{ __('Selling') }}</th><th class="px-4 py-3 text-left">{{ __('Notes') }}</th></tr></thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($itemRows as $index => $row)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <select name="items[{{ $index }}][item_variant_id]" class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">{{ __('Select item') }}</option>
                                                    @foreach ($itemVariants as $variant)
                                                        <option value="{{ $variant->id }}" @selected((int) ($row['item_variant_id'] ?? '') === $variant->id)>
                                                            {{ $variant->item?->brand?->name ? $variant->item->brand->name.' - ' : '' }}{{ $variant->item?->name }}{{ $usesItemVariants ? ' - '.$variant->variant_name : '' }}{{ $variant->sku ? ' ('.$variant->sku.')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('items.'.$index.'.item_variant_id')" />
                                            </td>
                                            <td class="px-4 py-3"><input name="items[{{ $index }}][item_name_snapshot]" value="{{ $row['item_name_snapshot'] ?? '' }}" class="block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                            <td class="px-4 py-3"><input name="items[{{ $index }}][quantity]" type="number" min="0.01" step="0.01" value="{{ $row['quantity'] ?? '' }}" class="block w-24 rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                            <td class="px-4 py-3"><input name="items[{{ $index }}][cost_price_snapshot]" type="number" min="0" step="0.01" value="{{ $row['cost_price_snapshot'] ?? '' }}" class="block w-28 rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                            <td class="px-4 py-3"><input name="items[{{ $index }}][selling_price_snapshot]" type="number" min="0" step="0.01" value="{{ $row['selling_price_snapshot'] ?? '' }}" class="block w-28 rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                            <td class="px-4 py-3"><input name="items[{{ $index }}][notes]" value="{{ $row['notes'] ?? '' }}" class="block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ __('Save Job Order') }}</x-primary-button>
                        <a href="{{ route('job-orders.show', $jobOrder) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
