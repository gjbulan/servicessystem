<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Financial Summary') }}</h2></x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <section class="bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('reports.financial-summary') }}" class="grid gap-4 md:grid-cols-4">
                    <div><x-input-label for="date_from" :value="__('From')" /><x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$filters['date_from'] ?? ''" /></div>
                    <div><x-input-label for="date_to" :value="__('To')" /><x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$filters['date_to'] ?? ''" /></div>
                    <div><x-input-label for="branch_id" :value="__('Branch')" /><select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">{{ __('All branches') }}</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
                    <div class="flex items-end"><x-primary-button>{{ __('Filter') }}</x-primary-button></div>
                </form>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['label' => __('Total revenue'), 'value' => $summary['revenue']],
                    ['label' => __('Total COGS'), 'value' => $summary['cogs']],
                    ['label' => __('Gross profit'), 'value' => $summary['gross_profit']],
                    ['label' => __('Total expenses'), 'value' => $summary['expenses']],
                    ['label' => __('Technician incentives paid'), 'value' => $summary['technician_incentives_paid']],
                    ['label' => __('Net profit'), 'value' => $summary['net_profit']],
                    ['label' => __('Outstanding balances'), 'value' => $summary['outstanding_balance']],
                    ['label' => __('Sales count'), 'value' => $summary['sales_count'], 'plain' => true],
                    ['label' => __('Expense count'), 'value' => $summary['expense_count'], 'plain' => true],
                ] as $stat)
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ ! empty($stat['plain']) ? $stat['value'] : number_format((float) $stat['value'], 2) }}</p>
                    </div>
                @endforeach
            </section>

            <section class="bg-white p-6 text-sm text-gray-600 shadow-sm sm:rounded-lg">
                {{ __('Revenue is recognized from paid sales only. Completed job order services are excluded in Phase 5 to avoid double counting when service work is later converted to a sale or invoice.') }}
            </section>
        </div>
    </div>
</x-app-layout>
