<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Assign Technicians') }} {{ $jobOrder->job_order_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('job-orders.technicians.update', $jobOrder) }}">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">{{ __('Assign') }}</th><th class="px-4 py-3 text-left">{{ __('Technician') }}</th><th class="px-4 py-3 text-left">{{ __('Role') }}</th><th class="px-4 py-3 text-left">{{ __('Primary') }}</th><th class="px-4 py-3 text-left">{{ __('Notes') }}</th></tr></thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($technicians as $index => $technician)
                                    @php $assignment = $assignedTechnicians->get($technician->id); @endphp
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="hidden" name="technicians[{{ $index }}][technician_id]" value="{{ $technician->id }}">
                                            <input type="checkbox" name="technicians[{{ $index }}][selected]" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked((bool) $assignment)>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $technician->name }}</td>
                                        <td class="px-4 py-3"><input name="technicians[{{ $index }}][role]" value="{{ old('technicians.'.$index.'.role', $assignment?->role) }}" class="block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                        <td class="px-4 py-3"><input type="checkbox" name="technicians[{{ $index }}][is_primary]" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('technicians.'.$index.'.is_primary', $assignment?->is_primary))></td>
                                        <td class="px-4 py-3"><input name="technicians[{{ $index }}][notes]" value="{{ old('technicians.'.$index.'.notes', $assignment?->notes) }}" class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <x-primary-button>{{ __('Save Technicians') }}</x-primary-button>
                        <a href="{{ route('job-orders.show', $jobOrder) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
