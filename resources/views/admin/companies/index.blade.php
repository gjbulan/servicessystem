<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Companies') }}
            </h2>

            <a href="{{ route('admin.companies.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                {{ __('Create Company') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 text-sm font-medium text-green-700">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Company') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Users') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Modules') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($companies as $company)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $company->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $company->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                            {{ $statuses[$company->status] ?? ucfirst($company->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $company->users_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $company->modules_count }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap items-center justify-end gap-3 text-sm font-medium">
                                            <a href="{{ route('admin.companies.show', $company) }}" class="text-gray-700 hover:text-gray-950">{{ __('View') }}</a>
                                            <a href="{{ route('admin.companies.edit', $company) }}" class="text-gray-700 hover:text-gray-950">{{ __('Edit') }}</a>
                                            <a href="{{ route('admin.companies.users', $company) }}" class="text-gray-700 hover:text-gray-950">{{ __('Users') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                        {{ __('No companies found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $companies->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
