<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Expenses') }}</h2>
            <a href="{{ route('expenses.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Record Expense') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))<div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>@endif
            <section class="bg-white p-4 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('expenses.index') }}" class="grid gap-4 md:grid-cols-6">
                    <div><x-input-label for="date_from" :value="__('From')" /><x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$filters['date_from'] ?? ''" /></div>
                    <div><x-input-label for="date_to" :value="__('To')" /><x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$filters['date_to'] ?? ''" /></div>
                    <div><x-input-label for="branch_id" :value="__('Branch')" /><select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">{{ __('All branches') }}</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
                    <div><x-input-label for="expense_category_id" :value="__('Category')" /><select id="expense_category_id" name="expense_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">{{ __('All categories') }}</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) ($filters['expense_category_id'] ?? 0) === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                    <div><x-input-label for="status" :value="__('Status')" /><select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">{{ __('All statuses') }}</option>@foreach ($statuses as $statusValue => $statusLabel)<option value="{{ $statusValue }}" @selected(($filters['status'] ?? '') === $statusValue)>{{ $statusLabel }}</option>@endforeach</select></div>
                    <div class="flex items-end"><x-primary-button>{{ __('Filter') }}</x-primary-button></div>
                </form>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Date') }}</th><th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Description') }}</th><th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Branch') }}</th><th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Category') }}</th><th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Amount') }}</th><th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th><th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $expense->expense_date?->format('M d, Y') }}</td>
                                    <td class="px-4 py-3"><div class="font-medium text-gray-900">{{ $expense->description }}</div><div class="text-sm text-gray-500">{{ $expense->reference_number ?? __('No reference') }}</div></td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $expense->branch?->name ?? __('Company-wide') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $expense->category?->name ?? __('Uncategorized') }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format((float) $expense->amount, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $statuses[$expense->status] ?? ucfirst($expense->status) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium"><a href="{{ route('expenses.show', $expense) }}" class="text-gray-700 hover:text-gray-950">{{ __('View') }}</a><a href="{{ route('expenses.edit', $expense) }}" class="ms-3 text-gray-700 hover:text-gray-950">{{ __('Edit') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No expenses found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">{{ $expenses->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
