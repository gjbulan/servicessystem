<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $branch->name }}</h2>
            <a href="{{ route('branches.edit', $branch) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Edit') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-white p-4 text-sm font-medium text-green-700 shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="grid gap-6 p-6 md:grid-cols-2">
                    <div><p class="text-sm text-gray-500">{{ __('Code') }}</p><p class="font-medium text-gray-900">{{ $branch->code ?? __('Not set') }}</p></div>
                    <div><p class="text-sm text-gray-500">{{ __('Status') }}</p><p class="font-medium text-gray-900">{{ $statuses[$branch->status] ?? ucfirst($branch->status) }}</p></div>
                    <div><p class="text-sm text-gray-500">{{ __('Email') }}</p><p class="font-medium text-gray-900">{{ $branch->email ?? __('Not set') }}</p></div>
                    <div><p class="text-sm text-gray-500">{{ __('Phone') }}</p><p class="font-medium text-gray-900">{{ $branch->phone ?? __('Not set') }}</p></div>
                    <div><p class="text-sm text-gray-500">{{ __('Manager') }}</p><p class="font-medium text-gray-900">{{ $branch->manager_name ?? __('Not set') }}</p></div>
                    <div><p class="text-sm text-gray-500">{{ __('Operating hours') }}</p><p class="font-medium text-gray-900">{{ $branch->operating_hours ?? __('Not set') }}</p></div>
                    <div class="md:col-span-2"><p class="text-sm text-gray-500">{{ __('Address') }}</p><p class="font-medium text-gray-900">{{ $branch->address ?? __('Not set') }}</p></div>
                </div>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('{{ __('Delete this branch?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>{{ __('Delete Branch') }}</x-danger-button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
