<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $company->name }}
            </h2>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.companies.users', $company) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50">
                    {{ __('Manage Users') }}
                </a>
                <a href="{{ route('admin.companies.edit', $company) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                    {{ __('Edit') }}
                </a>
            </div>
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
                <div class="grid gap-6 p-6 md:grid-cols-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Slug') }}</p>
                        <p class="mt-1 text-gray-900">{{ $company->slug }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Status') }}</p>
                        <p class="mt-1 text-gray-900">{{ $statuses[$company->status] ?? ucfirst($company->status) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Users') }}</p>
                        <p class="mt-1 text-gray-900">{{ $company->users_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Modules') }}</p>
                        <p class="mt-1 text-gray-900">{{ $company->modules_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Email') }}</p>
                        <p class="mt-1 text-gray-900">{{ $company->email ?? __('Not set') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Phone') }}</p>
                        <p class="mt-1 text-gray-900">{{ $company->phone ?? __('Not set') }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm font-medium text-gray-500">{{ __('Address') }}</p>
                        <p class="mt-1 text-gray-900">{{ $company->address ?? __('Not set') }}</p>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Modules') }}</h3>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse ($company->modules as $module)
                            <div class="flex items-start justify-between gap-4 p-6">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $module->module_name }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ $module->module_key }}</p>
                                </div>

                                <span class="rounded-md px-2 py-1 text-xs font-medium {{ $module->is_enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $module->is_enabled ? __('Enabled') : __('Disabled') }}
                                </span>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">{{ __('No modules created yet.') }}</div>
                        @endforelse
                    </div>
                </section>

                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Assigned Users') }}</h3>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse ($company->users as $user)
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="mt-1 text-sm text-gray-500">{{ $user->email }}</p>
                                    </div>

                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @forelse ($user->roles as $role)
                                        <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-sm text-gray-500">{{ __('No roles assigned') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">{{ __('No users assigned yet.') }}</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" onsubmit="return confirm('{{ __('Delete this company?') }}')">
                        @csrf
                        @method('DELETE')

                        <x-danger-button>{{ __('Delete Company') }}</x-danger-button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
