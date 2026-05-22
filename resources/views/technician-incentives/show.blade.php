<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Technician Incentive') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $statuses[$incentive->status] ?? ucfirst($incentive->status) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($canManageIncentives && ! in_array($incentive->status, ['paid', 'cancelled'], true))
                    <a href="{{ route('technician-incentives.edit', $incentive) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                @endif
                @if ($canApproveOrPay && $incentive->status === 'pending')
                    <form method="POST" action="{{ route('technician-incentives.approve', $incentive) }}">
                        @csrf
                        <x-primary-button>{{ __('Approve') }}</x-primary-button>
                    </form>
                @endif
                @if ($canApproveOrPay && $incentive->status === 'approved')
                    <form method="POST" action="{{ route('technician-incentives.mark-paid', $incentive) }}">
                        @csrf
                        <button class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Mark Paid') }}</button>
                    </form>
                @endif
                @if ($canManageIncentives && $incentive->status !== 'paid' && $incentive->status !== 'cancelled')
                    <form method="POST" action="{{ route('technician-incentives.cancel', $incentive) }}">
                        @csrf
                        <button class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 hover:bg-red-50">{{ __('Cancel') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-white p-4 text-sm font-medium text-red-700 shadow-sm sm:rounded-lg">{{ $errors->first() }}</div>
            @endif

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 md:grid-cols-3">
                    <div><dt class="text-sm text-gray-500">{{ __('Date') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->created_at?->format('M d, Y') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Branch') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->branch?->name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Job order') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->jobOrder?->job_order_number }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Technician') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->technician?->name }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Service') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->service_name_snapshot }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Status') }}</dt><dd class="font-medium text-gray-900">{{ $statuses[$incentive->status] ?? ucfirst($incentive->status) }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Default amount') }}</dt><dd class="font-medium text-gray-900">{{ number_format((float) $incentive->default_amount, 2) }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Override amount') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->override_amount !== null ? number_format((float) $incentive->override_amount, 2) : __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Final amount') }}</dt><dd class="font-medium text-gray-900">{{ number_format((float) $incentive->final_amount, 2) }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Approved by') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->approver?->name ?? __('Not approved') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Approved at') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->approved_at?->format('M d, Y h:i A') ?? __('Not set') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Paid at') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->paid_at?->format('M d, Y h:i A') ?? __('Not set') }}</dd></div>
                    <div class="md:col-span-3"><dt class="text-sm text-gray-500">{{ __('Override reason') }}</dt><dd class="font-medium text-gray-900">{{ $incentive->override_reason ?? __('Not set') }}</dd></div>
                </dl>
            </section>
        </div>
    </div>
</x-app-layout>
