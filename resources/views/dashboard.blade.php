<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('MOTOSHOP-SAAS Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-wide text-gray-500">
                                {{ __('Signed in as') }}
                            </p>
                            <h3 class="mt-2 text-2xl font-semibold text-gray-900">
                                {{ $user->name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $user->email }}
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:min-w-96">
                            <div class="rounded-lg border border-gray-200 p-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('Company') }}</p>
                                <p class="mt-2 text-base font-semibold text-gray-900">
                                    {{ $user->company?->name ?? __('No company assigned') }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $user->company?->status ? ucfirst($user->company->status) : __('Super admin or unassigned user') }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('Account status') }}</p>
                                <p class="mt-2 text-base font-semibold text-gray-900">
                                    {{ ucfirst($user->status) }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('User access state') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white shadow-sm sm:rounded-lg lg:col-span-1">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Assigned roles') }}</h3>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($user->roles->sortBy('name') as $role)
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800">
                                    {{ $role->name }}
                                </span>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No roles assigned') }}</p>
                            @endforelse
                        </div>

                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Enabled modules') }}</h3>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @forelse ($user->company?->modules?->where('is_enabled', true)->sortBy('module_name') ?? collect() as $module)
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800">
                                        {{ $module->module_name }}
                                    </span>
                                @empty
                                    <p class="text-sm text-gray-500">{{ __('No company modules assigned') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('SaaS foundation status') }}</h3>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($foundationStats as $stat)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stat['value'] }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ $stat['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
