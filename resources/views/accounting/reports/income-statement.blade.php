<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Income Statement') }}</h2></x-slot>
    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <section class="bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('reports.income-statement') }}" class="grid gap-4 md:grid-cols-4">
                    <div><x-input-label for="date_from" :value="__('From')" /><x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$filters['date_from'] ?? ''" /></div>
                    <div><x-input-label for="date_to" :value="__('To')" /><x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$filters['date_to'] ?? ''" /></div>
                    <div><x-input-label for="branch_id" :value="__('Branch')" /><select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">{{ __('All branches') }}</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
                    <div class="flex items-end"><x-primary-button>{{ __('Filter') }}</x-primary-button></div>
                </form>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr><td class="px-6 py-4 text-sm font-medium text-gray-900">{{ __('Revenue') }}</td><td class="px-6 py-4 text-right text-sm font-medium text-gray-900">{{ number_format((float) $summary['revenue'], 2) }}</td></tr>
                            <tr><td class="px-6 py-4 text-sm text-gray-600">{{ __('COGS') }}</td><td class="px-6 py-4 text-right text-sm text-gray-600">{{ number_format((float) $summary['cogs'], 2) }}</td></tr>
                            <tr><td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ __('Gross Profit') }}</td><td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">{{ number_format((float) $summary['gross_profit'], 2) }}</td></tr>
                            <tr><td class="px-6 py-4 text-sm text-gray-600">{{ __('Operating Expenses') }}</td><td class="px-6 py-4 text-right text-sm text-gray-600">{{ number_format((float) $summary['expenses'], 2) }}</td></tr>
                            <tr><td class="px-6 py-4 text-sm text-gray-600">{{ __('Technician Incentives Paid') }}</td><td class="px-6 py-4 text-right text-sm text-gray-600">{{ number_format((float) $summary['technician_incentives_paid'], 2) }}</td></tr>
                            <tr><td class="px-6 py-4 text-base font-semibold text-gray-900">{{ __('Net Profit') }}</td><td class="px-6 py-4 text-right text-base font-semibold text-gray-900">{{ number_format((float) $summary['net_profit'], 2) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
