<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $platformUser->name }}</h2>
            <a href="{{ route('admin.users.edit', $platformUser) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Edit') }}</a>
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

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="grid gap-6 p-6 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Email') }}</p>
                        <p class="font-medium text-gray-900">{{ $platformUser->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Status') }}</p>
                        <p class="font-medium text-gray-900">{{ $statuses[$platformUser->status] ?? ucfirst($platformUser->status) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Company') }}</p>
                        <p class="font-medium text-gray-900">{{ $platformUser->company?->name ?? __('No company') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Role') }}</p>
                        <p class="font-medium text-gray-900">{{ $platformUser->roles->pluck('name')->join(', ') ?: __('No role') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Branch') }}</p>
                        <p class="font-medium text-gray-900">{{ $branch?->name ?? __('All branches / none') }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.users.destroy', $platformUser) }}" onsubmit="return confirm('{{ __('Delete this user?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>{{ __('Delete User') }}</x-danger-button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
