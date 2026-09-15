<x-filament-panels::page>
    @php
        $stats = $this->getCourseStats();
    @endphp

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
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-primary-400 hover:bg-primary-50 hover:text-primary-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-primary-500 dark:hover:bg-primary-900/20 dark:hover:text-primary-400"
                title="{{ __('Previous month') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="min-w-[180px] text-center">
                <span class="text-base sm:text-lg font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $this->getMonthLabel() }}
                </span>
            </div>

            <button
                type="button"
                wire:click="nextMonth"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-primary-400 hover:bg-primary-50 hover:text-primary-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-primary-500 dark:hover:bg-primary-900/20 dark:hover:text-primary-400"
                title="{{ __('Next month') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        {{-- ── Stats bar ── --}}
        <div class="mb-5 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
            <x-filament::section class="p-3 sm:p-4">
                <div class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300 truncate">{{ __('Students in group') }}</div>
                <div class="mt-1 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-50">{{ $stats['students'] }}</div>
            </x-filament::section>
            <x-filament::section class="p-3 sm:p-4">
                <div class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300 truncate">{{ __('Paid') }}</div>
                <div class="mt-1 text-2xl sm:text-3xl font-bold text-success-700 dark:text-success-400">{{ $stats['paid'] }}</div>
            </x-filament::section>
            <x-filament::section class="p-3 sm:p-4">
                <div class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300 truncate">{{ __('Partial') }}</div>
                <div class="mt-1 text-2xl sm:text-3xl font-bold text-warning-700 dark:text-warning-400">{{ $stats['partial'] }}</div>
            </x-filament::section>
            <x-filament::section class="p-3 sm:p-4 col-span-2 sm:col-span-1">
                <div class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300 truncate">{{ __('Not paid') }}</div>
                <div class="mt-1 text-2xl sm:text-3xl font-bold text-danger-700 dark:text-danger-400">{{ $stats['unpaid'] }}</div>
            </x-filament::section>
        </div>

        {{-- ── Total Amount Collected (mobile: full, desktop: below stats) ── --}}
        <x-filament::section class="mb-5 p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <div class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">{{ __('Total collected this month') }}</div>
                    <div class="mt-1 text-xl sm:text-2xl font-bold text-primary-700 dark:text-primary-400">
                        @if($stats['totalPaidAmount'] > 0)
                            {{ number_format($stats['totalPaidAmount'], 2) }} {{ __('UZS') }}
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        @if(count($students) > 0)
            <x-filament::section>
                {{-- ── DESKTOP TABLE (hidden on mobile) ── --}}
                <div class="hidden sm:block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- ── Table header ── --}}
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-800">
                                <th class="w-10 px-3 py-3 text-center text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">
                                    #
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">
                                    {{ __('Student') }}
                                </th>
                                <th class="w-48 px-3 py-3 text-center text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">
                                    {{ __('Payment status') }}
                                </th>
                                <th class="w-40 px-3 py-3 text-center text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">
                                    {{ __('Paid Amount') }}
                                </th>
                                <th class="w-14 px-3 py-3 text-center text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">
                                    {{ __('Comment') }}
                                </th>
                                <th class="w-12 px-3 py-3 text-center text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">
                                </th>
                            </tr>
                        </thead>

                        {{-- ── Table body ── --}}
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                            @foreach($students as $index => $student)
                                @php
                                    $statusColors = match ($student['status']) {
                                        'paid' => 'bg-success-50 dark:bg-success-900/10 hover:bg-success-100/70 dark:hover:bg-success-900/20',
                                        'partial' => 'bg-warning-50 dark:bg-warning-900/10 hover:bg-warning-100/70 dark:hover:bg-warning-900/20',
                                        default => 'hover:bg-gray-50 dark:hover:bg-gray-700/40',
                                    };
                                    $avatarClass = match ($student['status']) {
                                        'paid' => 'bg-success-200 text-success-800 dark:bg-success-800/50 dark:text-success-300',
                                        'partial' => 'bg-warning-200 text-warning-800 dark:bg-warning-800/50 dark:text-warning-300',
                                        default => 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300',
                                    };
                                    $selectClass = match ($student['status']) {
                                        'paid' => 'border-success-500 bg-success-50 text-success-800 dark:bg-success-900/30 dark:text-success-200 dark:border-success-500/60',
                                        'partial' => 'border-warning-500 bg-warning-50 text-warning-800 dark:bg-warning-900/30 dark:text-warning-200 dark:border-warning-500/60',
                                        default => 'border-danger-400 bg-danger-50 text-danger-800 dark:bg-danger-900/30 dark:text-danger-200 dark:border-danger-500/60',
                                    };
                                    $hasComment = filled($student['comment']);
                                    $commentBtnBase = 'inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm font-semibold border-2 transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary-500 ';
                                    $commentBtnClass = $hasComment
                                        ? $commentBtnBase . 'bg-primary-50 border-primary-400 text-primary-700 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-500/60 dark:hover:bg-primary-900/50'
                                        : $commentBtnBase . 'bg-white border-gray-200 text-gray-400 hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500 dark:hover:border-primary-500 dark:hover:text-primary-400 dark:hover:bg-primary-900/20';
                                    $commentBtnTitle = $hasComment ? $student['comment'] : __('Add comment');
                                @endphp
                                <tr
                                    wire:key="student-{{ $student['enrollmentId'] }}"
                                    class="group transition-colors duration-150 {{ $statusColors }}"
                                >
                                    {{-- Row number --}}
                                    <td class="px-3 py-3 text-center">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>

                                    {{-- Student name --}}
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-extrabold uppercase select-none {{ $avatarClass }}">
                                                {{ mb_substr($student['studentName'], 0, 1) }}
                                            </div>
                                            <a href="{{ url('admin/students/' . $student['studentId']) }}" class="text-base font-bold text-gray-800 hover:text-primary-600 hover:underline dark:text-gray-100 dark:hover:text-primary-400 truncate max-w-[240px]">
                                                {{ $student['studentName'] }}
                                            </a>
                                        </div>
                                    </td>

                                    {{-- Status select --}}
                                    <td class="px-3 py-3 text-center">
                                        <div x-data="{ status: '{{ $student['status'] }}', partialAmount: @js($student['paidAmount'] ?? null) }" class="space-y-2">
                                            {{-- Select --}}
                                            <select
                                                x-model="status"
                                                @change="
                                                    if (status === 'partial') {
                                                        let amount = prompt('{{ __('How much did the student pay? (UZS)') }}', partialAmount || '');
                                                        if (amount === null) { status = '{{ $student['status'] }}'; return; }
                                                        amount = amount.replace(/[^0-9.]/g, '');
                                                        if (!amount || parseFloat(amount) <= 0) { status = '{{ $student['status'] }}'; return; }
                                                        partialAmount = amount;
                                                    }
                                                    \$wire.updateStatus({{ $student['enrollmentId'] }}, status, status === 'partial' ? partialAmount : null);
                                                "
                                                class="w-full rounded-lg border-2 text-sm font-semibold py-2 px-2.5 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary-500 transition {{ $selectClass }}"
                                            >
                                                <option value="paid" class="bg-white text-success-700 dark:bg-gray-800 dark:text-success-300">✅ {{ __('Paid') }}</option>
                                                <option value="partial" class="bg-white text-warning-700 dark:bg-gray-800 dark:text-warning-300">🟡 {{ __('Partially Paid') }}</option>
                                                <option value="unpaid" class="bg-white text-danger-700 dark:bg-gray-800 dark:text-danger-300">❌ {{ __('Not paid') }}</option>
                                            </select>

                                            {{-- Status badge text helper --}}
                                            <div class="flex items-center justify-center text-[11px] font-medium gap-1.5">
                                                @if($student['status'] === 'paid')
                                                    <x-heroicon-m-check-circle class="h-3.5 w-3.5 text-success-600 dark:text-success-400" />
                                                    <span class="text-success-700 dark:text-success-300">{{ __('Paid in full') }}</span>
                                                @elseif($student['status'] === 'partial')
                                                    <x-heroicon-m-exclamation-triangle class="h-3.5 w-3.5 text-warning-600 dark:text-warning-400" />
                                                    <span class="text-warning-700 dark:text-warning-300">{{ __('Partially paid') }}</span>
                                                @else
                                                    <x-heroicon-m-clock class="h-3.5 w-3.5 text-danger-600 dark:text-danger-400" />
                                                    <span class="text-danger-700 dark:text-danger-300">{{ __('Not paid') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Paid Amount --}}
                                    <td class="px-3 py-3 text-center">
                                        @if($student['paidAmount'] !== null && $student['paidAmount'] > 0)
                                            <div class="inline-flex flex-col items-center">
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                                    {{ number_format((float)$student['paidAmount'], 0) }}
                                                </span>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">UZS</span>
                                            </div>
                                        @else
                                            @if($student['status'] === 'paid')
                                                <span class="text-xs font-semibold text-success-700 dark:text-success-400">{{ __('Full') }}</span>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Comment button --}}
                                    <td class="px-3 py-3 text-center">
                                        <button
                                            type="button"
                                            wire:click="openCommentModal({{ $student['enrollmentId'] }})"
                                            class="{{ $commentBtnClass }}"
                                            title="{{ $commentBtnTitle }}"
                                        >
                                            <x-heroicon-m-chat-bubble-left-ellipsis class="h-4.5 w-4.5" />
                                        </button>
                                    </td>

                                    {{-- Three-dot actions menu --}}
                                    <td class="px-2 py-3 text-center">
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

                {{-- ── MOBILE CARDS (hidden on desktop) ── --}}
                <div class="sm:hidden space-y-3">
                    @foreach($students as $index => $student)
                        @php
                            $cardBorder = match ($student['status']) {
                                'paid' => 'border-l-4 border-l-success-500 dark:border-l-success-400',
                                'partial' => 'border-l-4 border-l-warning-500 dark:border-l-warning-400',
                                default => 'border-l-4 border-l-danger-400 dark:border-l-danger-500/70',
                            };
                            $cardBg = match ($student['status']) {
                                'paid' => 'bg-success-50/60 dark:bg-success-900/10',
                                'partial' => 'bg-warning-50/60 dark:bg-warning-900/10',
                                default => 'bg-white dark:bg-gray-800',
                            };
                            $mAvatarClass = match ($student['status']) {
                                'paid' => 'bg-success-200 text-success-800 dark:bg-success-800/50 dark:text-success-300',
                                'partial' => 'bg-warning-200 text-warning-800 dark:bg-warning-800/50 dark:text-warning-300',
                                default => 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300',
                            };
                            $mHasComment = filled($student['comment']);
                            $mCommentBtnBase = 'inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm font-semibold border-2 transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary-500 ';
                            $mCommentBtnClass = $mHasComment
                                ? $mCommentBtnBase . 'bg-primary-50 border-primary-400 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-500/60'
                                : $mCommentBtnBase . 'bg-white border-gray-200 text-gray-400 hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500 dark:hover:border-primary-500 dark:hover:text-primary-400';
                            $mCommentBtnTitle = $mHasComment ? $student['comment'] : __('Add comment');
                            $mSelectClass = match ($student['status']) {
                                'paid' => 'border-success-500 bg-success-50 text-success-800 dark:bg-success-900/30 dark:text-success-200',
                                'partial' => 'border-warning-500 bg-warning-50 text-warning-800 dark:bg-warning-900/30 dark:text-warning-200',
                                default => 'border-danger-400 bg-danger-50 text-danger-800 dark:bg-danger-900/30 dark:text-danger-200',
                            };
                        @endphp
                        <div
                            wire:key="mobile-student-{{ $student['enrollmentId'] }}"
                            class="rounded-xl border border-gray-200 dark:border-gray-700 {{ $cardBorder }} {{ $cardBg }} p-3 shadow-sm"
                        >
                            {{-- ── Header: Avatar + Name + Actions ── --}}
                            <div class="flex items-start gap-3 mb-3">
                                {{-- Avatar --}}
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-base font-extrabold uppercase select-none {{ $mAvatarClass }}">
                                    {{ mb_substr($student['studentName'], 0, 1) }}
                                </div>

                                {{-- Name + Status Badge --}}
                                <div class="flex-1 min-w-0">
                                    <a href="{{ url('admin/students/' . $student['studentId']) }}" class="block text-base font-bold text-gray-800 hover:text-primary-600 hover:underline dark:text-gray-100 dark:hover:text-primary-400 truncate">
                                        {{ $index + 1 }}. {{ $student['studentName'] }}
                                    </a>
                                    {{-- Small status badge --}}
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px]">
                                        @if($student['status'] === 'paid')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2 py-0.5 font-bold text-success-800 dark:bg-success-900/40 dark:text-success-300">
                                                <x-heroicon-m-check-circle class="h-3 w-3" /> {{ __('Paid') }}
                                            </span>
                                        @elseif($student['status'] === 'partial')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-warning-100 px-2 py-0.5 font-bold text-warning-800 dark:bg-warning-900/40 dark:text-warning-300">
                                                <x-heroicon-m-exclamation-triangle class="h-3 w-3" /> {{ __('Partial') }}
                                            </span>
                                            @if($student['paidAmount'] !== null && $student['paidAmount'] > 0)
                                                <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-0.5 font-bold text-primary-800 dark:bg-primary-900/40 dark:text-primary-300">
                                                    {{ number_format((float)$student['paidAmount'], 0) }} UZS
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-danger-100 px-2 py-0.5 font-bold text-danger-800 dark:bg-danger-900/40 dark:text-danger-300">
                                                <x-heroicon-m-clock class="h-3 w-3" /> {{ __('Not paid') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Actions: 3-dot + Comment --}}
                                <div class="flex items-center gap-1 shrink-0">
                                    {{-- Comment button --}}
                                    <button
                                        type="button"
                                        wire:click="openCommentModal({{ $student['enrollmentId'] }})"
                                        class="{{ $mCommentBtnClass }}"
                                        title="{{ $mCommentBtnTitle }}"
                                    >
                                        <x-heroicon-m-chat-bubble-left-ellipsis class="h-4.5 w-4.5" />
                                    </button>

                                    {{-- 3-dot menu --}}
                                    <div
                                        x-data="{ open: false }"
                                        @click.outside="open = false"
                                    >
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300 focus:outline-none"
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
                                            class="absolute right-4 z-50 mt-2 w-36 origin-top-right rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                            style="display: none;"
                                        >
                                            <button
                                                type="button"
                                                wire:click="editStudent({{ $student['studentId'] }})"
                                                @click="open = false"
                                                class="flex w-full items-center gap-2 rounded-t-xl px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50"
                                            >
                                                <x-heroicon-m-pencil-square class="h-4 w-4 text-gray-400" />
                                                {{ __('Edit') }}
                                            </button>
                                            <div class="border-t border-gray-100 dark:border-gray-700"></div>
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
                                </div>
                            </div>

                            {{-- ── Comment preview (if exists) ── --}}
                            @if(filled($student['comment']))
                                <div class="mb-3 rounded-lg bg-white/70 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 p-2">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-0.5">{{ __('Comment') }}</div>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 line-clamp-2">{{ $student['comment'] }}</p>
                                </div>
                            @endif

                            {{-- ── Status Select (mobile, full width) ── --}}
                            <div x-data="{ status: '{{ $student['status'] }}', partialAmount: @js($student['paidAmount'] ?? null) }">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                    {{ __('Change payment status') }}
                                </label>
                                <select
                                    x-model="status"
                                    @change="
                                        if (status === 'partial') {
                                            let amount = prompt('{{ __('How much did the student pay? (UZS)') }}', partialAmount || '');
                                            if (amount === null) { status = '{{ $student['status'] }}'; return; }
                                            amount = amount.replace(/[^0-9.]/g, '');
                                            if (!amount || parseFloat(amount) <= 0) { status = '{{ $student['status'] }}'; return; }
                                            partialAmount = amount;
                                        }
                                        \$wire.updateStatus({{ $student['enrollmentId'] }}, status, status === 'partial' ? partialAmount : null);
                                    "
                                    class="w-full rounded-lg border-2 text-sm font-semibold py-2.5 px-3 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary-500 transition {{ $mSelectClass }}"
                                >
                                    <option value="paid" class="bg-white text-success-700 dark:bg-gray-800 dark:text-success-300">✅ {{ __('Paid') }}</option>
                                    <option value="partial" class="bg-white text-warning-700 dark:bg-gray-800 dark:text-warning-300">🟡 {{ __('Partially Paid') }}</option>
                                    <option value="unpaid" class="bg-white text-danger-700 dark:bg-gray-800 dark:text-danger-300">❌ {{ __('Not paid') }}</option>
                                </select>

                                {{-- Paid Amount display on mobile --}}
                                @if($student['paidAmount'] !== null && $student['paidAmount'] > 0)
                                    <div class="mt-2 flex items-center justify-between rounded-lg bg-white dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 px-3 py-1.5">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Paid Amount') }}</span>
                                        <span class="text-sm font-bold text-primary-700 dark:text-primary-400">{{ number_format((float)$student['paidAmount'], 0) }} UZS</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ── Footer progress ── --}}
                <div class="mt-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-t border-gray-100 dark:border-gray-700 pt-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        {{ __(':paid paid + :partial partial of :total students', [
                            'paid' => $stats['paid'],
                            'partial' => $stats['partial'],
                            'total' => $stats['students']
                        ]) }}
                    </p>
                    @if($stats['students'] > 0)
                        @php
                            $paidPercent = round((($stats['paid'] + $stats['partial']) / $stats['students']) * 100);
                        @endphp
                        <div class="flex w-full sm:w-auto items-center gap-3">
                            <div class="h-2.5 flex-1 sm:w-48 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-warning-500 via-warning-400 to-success-500 transition-all duration-700"
                                    style="width: {{ $paidPercent }}%"
                                ></div>
                            </div>
                            <span class="text-sm font-extrabold text-primary-600 dark:text-primary-400 shrink-0">
                                {{ $paidPercent }}%
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4"
        wire:click.self="closeEditModal"
        @keydown.escape.window="$wire.closeEditModal()"
    >
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-5 sm:p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">{{ __('Edit student info') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
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
                <div class="col-span-1 sm:col-span-2">
                    <label class="block mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Gender') }}</label>
                    <div class="flex gap-4 sm:gap-6 mt-1">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="radio" wire:model="editGenderId" value="2" class="text-primary-500 h-4 w-4" /> {{ __('Male') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="radio" wire:model="editGenderId" value="1" class="text-primary-500 h-4 w-4" /> {{ __('Female') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                <button wire:click="closeEditModal" type="button" class="w-full sm:w-auto rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('Cancel') }}
                </button>
                <button wire:click="updateStudent" type="button" class="w-full sm:w-auto rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Delete Confirmation Modal ── --}}
    @if($showDeleteModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4"
        wire:click.self="closeDeleteModal"
        @keydown.escape.window="$wire.closeDeleteModal()"
    >
        <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-5 sm:p-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-danger-100 dark:bg-danger-900/30">
                <x-heroicon-m-trash class="h-6 w-6 text-danger-600 dark:text-danger-400" />
            </div>
            <h2 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">{{ __('Remove student') }}</h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">{{ __('This will remove the student from this course. This action cannot be undone.') }}</p>
            <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3">
                <button wire:click="closeDeleteModal" type="button" class="w-full sm:w-auto rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('Cancel') }}
                </button>
                <button wire:click="deleteStudent({{ $deleteStudentId }})" type="button" class="w-full sm:w-auto rounded-lg bg-danger-600 px-4 py-2 text-sm font-semibold text-white hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-danger-500">
                    {{ __('Delete') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Comment Modal ── --}}
    @if($showCommentModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4"
        wire:click.self="closeCommentModal"
        @keydown.escape.window="$wire.closeCommentModal()"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-5 sm:p-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                    <x-heroicon-m-chat-bubble-left-ellipsis class="h-5 w-5" />
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Teacher Comment') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('Month') }}: {{ $this->getMonthLabel() }} — {{ __('This comment is only visible for this month') }}</p>
                </div>
                <button wire:click="closeCommentModal" type="button" class="shrink-0 h-8 w-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-500 dark:hover:text-gray-300 dark:hover:bg-gray-700">
                    <x-heroicon-m-x-mark class="h-5 w-5" />
                </button>
            </div>

            <div class="mb-4">
                <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400">
                    {{ __('Add your notes about this student for :month', ['month' => $this->getMonthLabel()]) }}
                </label>
                <textarea
                    wire:model="commentText"
                    rows="5"
                    placeholder="{{ __('e.g. Paid half of the tuition, will pay the rest next week. / Student has financial difficulties this month...') }}"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white resize-y focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                ></textarea>
                <div class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">{{ __('Comments are saved per month. September comments will NOT appear in October.') }}</div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                <button wire:click="closeCommentModal" type="button" class="w-full sm:w-auto rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    {{ __('Cancel') }}
                </button>
                <button wire:click="saveComment" type="button" class="w-full sm:w-auto rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                    <span class="inline-flex items-center justify-center gap-1.5">
                        <x-heroicon-m-check class="h-4 w-4" />
                        {{ __('Save comment') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

</x-filament-panels::page>
