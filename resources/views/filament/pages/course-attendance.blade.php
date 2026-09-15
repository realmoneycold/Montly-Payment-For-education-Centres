<x-filament-panels::page>
    @php($stats = $this->getCourseStats())

    @if(!$courseId)
        <x-filament::section>
            <p class="text-sm text-gray-500">{{ __('No course selected.') }}</p>
        </x-filament::section>
    @else
        <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <x-filament::section>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Students in group') }}</div>
                <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['students'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Paid') }}</div>
                <div class="mt-1 text-2xl font-semibold text-success-600 dark:text-success-400">{{ $stats['paid'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Not paid') }}</div>
                <div class="mt-1 text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ $stats['unpaid'] }}</div>
            </x-filament::section>
        </div>

    @if(count($events) > 0 && count($students) > 0)
        {{-- Responsive spreadsheet-style monthly roster --}}
        <div>
            <x-filament::section>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="w-8 px-1.5 py-2 text-center sticky left-0 bg-gray-50 dark:bg-gray-700 z-10 text-gray-700 dark:text-gray-300">#</th>
                                <th class="w-40 max-w-40 px-2 py-2 sticky left-8 bg-gray-50 dark:bg-gray-700 z-10 text-gray-700 dark:text-gray-300">{{ __('Student') }}</th>
                                @foreach($events as $event)
                                    <th class="w-10 px-1 py-2 text-center whitespace-nowrap">
                                        <span class="block text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $event['date'] }}</span>
                                        <span class="block text-[10px] font-normal normal-case text-gray-500 dark:text-gray-400">{{ $event['weekday'] }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $studentIndex => $student)
                                <tr class="border-b dark:border-gray-600">
                                    <td class="w-8 px-1.5 py-1.5 text-center sticky left-0 bg-white dark:bg-gray-800 z-10 text-xs text-gray-600 dark:text-gray-400">
                                        {{ $studentIndex + 1 }}
                                    </td>
                                    <td class="w-40 max-w-40 truncate px-2 py-1.5 sticky left-8 bg-white dark:bg-gray-800 z-10 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-gray-100">
                                        {{ $student['studentName'] }}
                                    </td>
                                    @foreach($events as $event)
                                        <td class="w-10 px-1 py-1 text-center">
                                            @php($isPresent = ($student['attendances'][$event['id']] ?? null) === 1)
                                            <button
                                                type="button"
                                                wire:click="togglePresent({{ $student['studentId'] }}, {{ $event['id'] }})"
                                                title="{{ __('Toggle present') }}"
                                                class="inline-flex h-6 w-6 items-center justify-center rounded border transition {{ $isPresent ? 'border-success-600 bg-success-600 text-white' : 'border-gray-300 bg-white hover:border-primary-500 dark:border-gray-600 dark:bg-gray-800' }}"
                                            >
                                                @if($isPresent)
                                                    <x-heroicon-m-check class="h-3.5 w-3.5" />
                                                @endif
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        {{-- Legend --}}
        <div class="mt-4 flex flex-wrap gap-3 text-sm text-gray-600 dark:text-gray-400">
            @foreach($attendanceTypes as $type)
                <span><x-filament::badge :color="$type['color']">{{ $type['name'] }}</x-filament::badge></span>
            @endforeach
        </div>
    @else
        <x-filament::section>
            <p class="text-sm text-gray-500">{{ __('No events or students found for this course.') }}</p>
        </x-filament::section>
    @endif
    @endif
</x-filament-panels::page>
