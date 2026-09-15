<x-filament-panels::page>
    @php($stats = $this->getCourseStats())

    @if(!$courseId)
        <x-filament::section>
            <p class="text-sm text-gray-500">{{ __('No course selected.') }}</p>
        </x-filament::section>
    @else

        {{-- ── Month navigator ── --}}
        <div class="mb-5 flex items-center justify-center gap-4">
            <button
                type="button"
                wire:click="previousMonth"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-primary-400 hover:bg-primary-50 hover:text-primary-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-primary-500 dark:hover:bg-primary-900/20 dark:hover:text-primary-400"
                title="{{ __('Previous month') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="min-w-[160px] text-center">
                <span class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $this->getMonthLabel() }}
                </span>
            </div>

            <button
                type="button"
                wire:click="nextMonth"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-primary-400 hover:bg-primary-50 hover:text-primary-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-primary-500 dark:hover:bg-primary-900/20 dark:hover:text-primary-400"
                title="{{ __('Next month') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        {{-- ── Stats bar ── --}}
        <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <x-filament::section>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">{{ __('Students in group') }}</div>
                <div class="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-50">{{ $stats['students'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">{{ __('Paid') }}</div>
                <div class="mt-1 text-3xl font-bold text-success-700 dark:text-success-400">{{ $stats['paid'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">{{ __('Not paid') }}</div>
                <div class="mt-1 text-3xl font-bold text-warning-700 dark:text-warning-400">{{ $stats['unpaid'] }}</div>
            </x-filament::section>
        </div>

        @if(count($students) > 0)
            <x-filament::section>
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- ── Table header ── --}}
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-800">
                                <th class="w-12 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    #
                                </th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    {{ __('Student') }}
                                </th>
                                <th class="w-36 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    {{ __('Status') }}
                                </th>
                                <th class="w-20 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    {{ __('Paid') }}
                                </th>
                                <th class="w-14 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                </th>
                            </tr>
                        </thead>

                        {{-- ── Table body ── --}}
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                            @foreach($students as $index => $student)
                                <tr
                                    wire:key="student-{{ $student['enrollmentId'] }}"
                                    class="group transition-colors duration-150
                                        {{ $student['isPaid']
                                            ? 'bg-success-50 dark:bg-success-900/10 hover:bg-success-100/50 dark:hover:bg-success-900/20'
                                            : 'hover:bg-transparent'
                                        }}"
                                >
                                    {{-- Row number --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="text-sm font-medium text-gray-400 dark:text-gray-500">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>

                                    {{-- Student name --}}
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            {{-- Avatar with initial --}}
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-extrabold uppercase select-none
                                                {{ $student['isPaid']
                                                    ? 'bg-success-200 text-success-800 dark:bg-success-800/50 dark:text-success-300'
                                                    : 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300'
                                                }}">
                                                {{ mb_substr($student['studentName'], 0, 1) }}
                                            </div>
                                            {{-- Name - explicit dark color so it never disappears on hover --}}
                                            <a href="{{ url('admin/students/' . $student['studentId']) }}" class="text-base font-bold text-gray-800 hover:text-primary-600 hover:underline dark:text-gray-100 dark:hover:text-primary-400">
                                                {{ $student['studentName'] }}
                                            </a>
                                        </div>
                                    </td>

                                    {{-- Status badge --}}
                                    <td class="px-4 py-3.5 text-center">
                                        @if($student['isPaid'])
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-100 px-3 py-1 text-xs font-bold text-success-800 dark:bg-success-900/40 dark:text-success-300">
                                                <x-heroicon-m-check-circle class="h-3.5 w-3.5" />
                                                {{ __('Paid') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                                                <x-heroicon-m-clock class="h-3.5 w-3.5" />
                                                {{ __('Not paid') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Toggle checkbox --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <button
                                            type="button"
                                            wire:click="togglePaid({{ $student['enrollmentId'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="togglePaid({{ $student['enrollmentId'] }})"
                                            title="{{ $student['isPaid'] ? __('Mark as unpaid') : __('Mark as paid') }}"
                                            class="group/btn relative inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                                                {{ $student['isPaid']
                                                    ? 'border-success-500 bg-success-500 text-white shadow-sm hover:bg-success-600 hover:border-success-600'
                                                    : 'border-gray-300 bg-white text-gray-300 hover:border-success-400 hover:bg-success-50 hover:text-success-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600 dark:hover:border-success-500 dark:hover:text-success-400'
                                                }}"
                                        >
                                            {{-- Check icon --}}
                                            <x-heroicon-m-check class="h-4 w-4 transition-all duration-200
                                                {{ $student['isPaid'] ? 'scale-100 text-white' : 'scale-75 opacity-40 group-hover/btn:scale-100 group-hover/btn:opacity-100' }}" />

                                            {{-- Loading spinner --}}
                                            <span wire:loading wire:target="togglePaid({{ $student['enrollmentId'] }})"
                                                class="absolute inset-0 flex items-center justify-center">
                                                <svg class="h-4 w-4 animate-spin text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                        </button>
                                    </td>

                                    {{-- Three-dot actions menu --}}
                                    <td class="px-2 py-3.5 text-center">
                                        <div
                                            x-data="{ open: false, top: 0, left: 0 }"
                                            @click.outside="open = false"
                                        >
                                            <button
                                                type="button"
                                                @click="
                                                    open = !open;
                                                    if (open) {
                                                        let rect = $el.getBoundingClientRect();
                                                        top  = rect.bottom + window.scrollY + 4;
                                                        left = rect.right + window.scrollX - 144;
                                                    }
                                                "
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300 focus:outline-none"
                                                title="{{ __('Actions') }}"
                                            >
                                                <x-heroicon-m-ellipsis-vertical class="h-5 w-5" />
                                            </button>

                                            <div
                                                x-show="open"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                :style="'position: fixed; top: ' + top + 'px; left: ' + left + 'px;'"
                                                class="z-[9999] w-36 origin-top-right rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                                style="display: none;"
                                            >
                                                {{-- Edit --}}
                                                <button
                                                    type="button"
                                                    wire:click="editStudent({{ $student['studentId'] }})"
                                                    @click="open = false"
                                                    class="flex w-full items-center gap-2 rounded-t-xl px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50"
                                                >
                                                    <x-heroicon-m-pencil-square class="h-4 w-4 text-gray-400" />
                                                    {{ __('Edit') }}
                                                </button>

                                                {{-- Divider --}}
                                                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                                                {{-- Delete --}}
                                                <button
                                                    type="button"
                                                    wire:click="confirmDeleteStudent({{ $student['studentId'] }})"
                                                    @click="open = false"
                                                    class="flex w-full items-center gap-2 rounded-b-xl px-4 py-2.5 text-sm font-medium text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-900/20"
                                                >
                                                    <x-heroicon-m-trash class="h-4 w-4" />
                                                    {{ __('Delete') }}
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ── Footer progress ── --}}
                <div class="mt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ __(':paid of :total students have paid', ['paid' => $stats['paid'], 'total' => $stats['students']]) }}
                    </p>
                    @if($stats['students'] > 0)
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-40 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-success-500 transition-all duration-700"
                                    style="width: {{ round(($stats['paid'] / $stats['students']) * 100) }}%"
                                ></div>
                            </div>
                            <span class="text-sm font-extrabold text-success-600 dark:text-success-400">
                                {{ round(($stats['paid'] / $stats['students']) * 100) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <p class="text-sm text-gray-500">{{ __('No students enrolled in this course.') }}</p>
            </x-filament::section>
        @endif
    @endif

    {{-- ── Edit Student Modal ── --}}
    @if($showEditModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        wire:click.self="closeEditModal"
        @keydown.escape.window="$wire.closeEditModal()"
    >
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-6">
            <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">{{ __('Edit student info') }}</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('First name') }}</label>
                    <input wire:model="editFirstname" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Last name') }}</label>
                    <input wire:model="editLastname" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Phone number') }}</label>
                    <input wire:model="editPhoneNumber" type="tel" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Birthdate') }}</label>
                    <input wire:model="editBirthdate" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <div class="col-span-2">
                    <label class="block mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Gender') }}</label>
                    <div class="flex gap-6 mt-1">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="radio" wire:model="editGenderId" value="2" class="text-primary-500" /> {{ __('Male') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="radio" wire:model="editGenderId" value="1" class="text-primary-500" /> {{ __('Female') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="closeEditModal" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('Cancel') }}
                </button>
                <button wire:click="updateStudent" type="button" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Delete Confirmation Modal ── --}}
    @if($showDeleteModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        wire:click.self="closeDeleteModal"
        @keydown.escape.window="$wire.closeDeleteModal()"
    >
        <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-danger-100 dark:bg-danger-900/30">
                <x-heroicon-m-trash class="h-6 w-6 text-danger-600 dark:text-danger-400" />
            </div>
            <h2 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">{{ __('Remove student') }}</h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('This will remove the student from this course. This action cannot be undone.') }}</p>
            <div class="flex justify-center gap-3">
                <button wire:click="closeDeleteModal" type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('Cancel') }}
                </button>
                <button wire:click="deleteStudent({{ $deleteStudentId }})" type="button" class="rounded-lg bg-danger-600 px-4 py-2 text-sm font-semibold text-white hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-danger-500">
                    {{ __('Delete') }}
                </button>
            </div>
        </div>
    </div>
    @endif

</x-filament-panels::page>
