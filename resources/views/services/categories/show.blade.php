<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $category->name }}</h2>
            <a href="{{ route('service-categories.edit', $category) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Edit') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 md:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Status') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $statuses[$category->status] ?? ucfirst($category->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Sort order') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $category->sort_order ?? __('Not set') }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm text-gray-500">{{ __('Description') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $category->description ?? __('Not set') }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</x-app-layout>
