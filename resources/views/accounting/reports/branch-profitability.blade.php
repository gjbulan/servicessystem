<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Branch Profitability') }}</h2></x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <section class="bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('reports.branch-profitability') }}" class="grid gap-4 md:grid-cols-3">
                    <div><x-input-label for="date_from" :value="__('From')" /><x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$filters['date_from'] ?? ''" /></div>
                    <div><x-input-label for="date_to" :value="__('To')" /><x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$filters['date_to'] ?? ''" /></div>
                    <div class="flex items-end"><x-primary-button>{{ __('Filter') }}</x-primary-button></div>
                </form>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Branch') }}</th><th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Revenue') }}</th><th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('COGS') }}</th><th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Gross Profit') }}</th><th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Expenses') }}</th><th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Incentives Paid') }}</th><th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Net Profit') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($branches as $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['branch']->name }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $row['summary']['revenue'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $row['summary']['cogs'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $row['summary']['gross_profit'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $row['summary']['expenses'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $row['summary']['technician_incentives_paid'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format((float) $row['summary']['net_profit'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No branches found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white p-6 text-sm text-gray-600 shadow-sm sm:rounded-lg">
                {{ __('Company-wide expenses without a branch are included in company financial reports but are not allocated to branch profitability in Phase 5.') }}
            </section>
        </div>
    </div>
</x-app-layout>
