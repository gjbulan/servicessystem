@csrf
@php
    $expenseDateValue = $expense->expense_date instanceof \Carbon\CarbonInterface ? $expense->expense_date->format('Y-m-d') : $expense->expense_date;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="expense_date" :value="__('Expense date')" />
        <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', $expenseDateValue)" required />
        <x-input-error class="mt-2" :messages="$errors->get('expense_date')" />
    </div>

    <div>
        <x-input-label for="amount" :value="__('Amount')" />
        <x-text-input id="amount" name="amount" type="number" min="0.01" step="0.01" class="mt-1 block w-full" :value="old('amount', $expense->amount)" required />
        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
    </div>

    <div>
        <x-input-label for="branch_id" :value="__('Branch')" />
        <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('Company-wide') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((int) old('branch_id', $expense->branch_id) === $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('branch_id')" />
    </div>

    <div>
        <x-input-label for="expense_category_id" :value="__('Category')" />
        <select id="expense_category_id" name="expense_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('Uncategorized') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('expense_category_id', $expense->expense_category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('expense_category_id')" />
    </div>

    <div>
        <x-input-label for="reference_number" :value="__('Reference number')" />
        <x-text-input id="reference_number" name="reference_number" type="text" class="mt-1 block w-full" :value="old('reference_number', $expense->reference_number)" />
        <x-input-error class="mt-2" :messages="$errors->get('reference_number')" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($statuses as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected(old('status', $expense->status) === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description')" />
        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $expense->description)" required />
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="attachment" :value="__('Receipt attachment')" />
        <input id="attachment" name="attachment" type="file" class="mt-1 block w-full text-sm text-gray-700">
        @if ($expense->attachment_path)
            <p class="mt-2 text-sm text-gray-500">{{ __('Current attachment is kept unless a new file is uploaded.') }}</p>
        @endif
        <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ $buttonLabel }}</x-primary-button>
    <a href="{{ route('expenses.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
</div>
