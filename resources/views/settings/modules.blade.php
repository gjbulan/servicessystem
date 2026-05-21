<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Company Modules') }}
        </h2>
    </x-slot>

    @php
        $groupedDefinitions = collect($moduleDefinitions)->groupBy('group', preserveKeys: true);
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4 text-sm font-medium text-green-700">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            @forelse ($companies as $company)
                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $company->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $company->slug }}</p>
                            </div>

                            <span class="inline-flex w-fit items-center rounded-md bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
                                {{ ucfirst($company->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @foreach ($groupedDefinitions as $groupName => $definitions)
                            <div class="p-6">
                                <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                                    {{ $groupName }}
                                </h4>

                                <div class="mt-4 divide-y divide-gray-100">
                                    @foreach ($definitions as $moduleKey => $definition)
                                        @php
                                            $module = $company->modules->firstWhere('module_key', $moduleKey);
                                        @endphp

                                        @continue(! $module)

                                        <div class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="max-w-2xl">
                                                <p class="font-medium text-gray-900">{{ $module->module_name }}</p>
                                                <p class="mt-1 text-sm text-gray-500">{{ $module->description }}</p>
                                                <p class="mt-1 text-xs font-medium text-gray-400">{{ $module->module_key }}</p>
                                            </div>

                                            <form method="POST" action="{{ route('settings.modules.update', $module) }}" class="flex items-center gap-3">
                                                @csrf
                                                @method('PATCH')

                                                <input type="hidden" name="is_enabled" value="0">

                                                <label for="module-{{ $module->id }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                                                    <input
                                                        id="module-{{ $module->id }}"
                                                        name="is_enabled"
                                                        type="checkbox"
                                                        value="1"
                                                        @checked($module->is_enabled)
                                                        class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-500"
                                                    >
                                                    {{ $module->is_enabled ? __('Enabled') : __('Disabled') }}
                                                </label>

                                                <x-primary-button>
                                                    {{ __('Save') }}
                                                </x-primary-button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <section class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-600">
                        {{ __('No companies are available yet.') }}
                    </div>
                </section>
            @endforelse
        </div>
    </div>
</x-app-layout>
