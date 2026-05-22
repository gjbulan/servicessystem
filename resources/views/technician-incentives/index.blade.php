<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Technician Incentives') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <section class="bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('technician-incentives.index') }}" class="grid gap-4 md:grid-cols-6">
                    <div>
                        <x-input-label for="date_from" :value="__('From')" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$filters['date_from'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="date_to" :value="__('To')" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$filters['date_to'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="branch_id" :value="__('Branch')" />
                        <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="technician_id" :value="__('Technician')" />
                        <select id="technician_id" name="technician_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All technicians') }}</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected((int) ($filters['technician_id'] ?? 0) === $technician->id)>{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach ($statuses as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected(($filters['status'] ?? '') === $statusValue)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    </div>
                </form>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Branch') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Job Order') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Technician') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Service') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Default') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Override') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Final') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($incentives as $incentive)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $incentive->created_at?->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $incentive->branch?->name }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $incentive->jobOrder?->job_order_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $incentive->technician?->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $incentive->service_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $incentive->default_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ $incentive->override_amount !== null ? number_format((float) $incentive->override_amount, 2) : '-' }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format((float) $incentive->final_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $statuses[$incentive->status] ?? ucfirst($incentive->status) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium">
                                        <a href="{{ route('technician-incentives.show', $incentive) }}" class="text-gray-700 hover:text-gray-950">{{ __('View') }}</a>
                                        @if ($canManageIncentives && ! in_array($incentive->status, ['paid', 'cancelled'], true))
                                            <a href="{{ route('technician-incentives.edit', $incentive) }}" class="ms-3 text-gray-700 hover:text-gray-950">{{ __('Edit') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No technician incentives found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">{{ $incentives->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
