<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Platform Users') }}</h2>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Create User') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="bg-white p-4 text-sm font-medium text-red-700 shadow-sm sm:rounded-lg">{{ $errors->first() }}</div>
            @endif

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="sm:w-80">
                        <x-input-label for="company_id" :value="__('Filter by company')" />
                        <select id="company_id" name="company_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All users') }}</option>
                            <option value="unassigned" @selected($companyFilter === 'unassigned')>{{ __('No company / platform users') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected((string) $companyFilter === (string) $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('User') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Company') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Role') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($users as $platformUser)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $platformUser->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $platformUser->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $platformUser->company?->name ?? __('No company') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $platformUser->roles->pluck('name')->join(', ') ?: __('No role') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $statuses[$platformUser->status] ?? ucfirst($platformUser->status) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('admin.users.show', $platformUser) }}" class="text-gray-700 hover:text-gray-950">{{ __('View') }}</a>
                                        <a href="{{ route('admin.users.edit', $platformUser) }}" class="ms-3 text-gray-700 hover:text-gray-950">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No users found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">{{ $users->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
