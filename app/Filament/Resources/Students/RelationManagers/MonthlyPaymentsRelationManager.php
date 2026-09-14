<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Models\Enrollment;
use App\Models\MonthlyPaymentRecord;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonthlyPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public static function getTitle(mixed $ownerRecord, string $pageClass): string
    {
        return __('Monthly Payments');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['course', 'monthlyPayments']))
            ->columns([
                TextColumn::make('course.name')
                    ->label(__('Course'))
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('month_label')
                    ->label(__('Month'))
                    ->state(function (Enrollment $record): string {
                        $payment = $record->monthlyPayments
                            ->sortByDesc('month')
                            ->first();

                        return $payment
                            ? Carbon::createFromFormat('Y-m', $payment->month)->format('F Y')
                            : Carbon::now()->format('F Y');
                    }),
                TextColumn::make('payment_status')
                    ->label(__('Status'))
                    ->badge()
                    ->state(function (Enrollment $record): string {
                        $month = Carbon::now()->format('Y-m');
                        $payment = $record->monthlyPayments
                            ->where('month', $month)
                            ->first();

                        return ($payment && $payment->paid_at !== null) ? __('Paid') : __('Not Paid');
                    })
                    ->color(fn (string $state): string => $state === __('Paid') ? 'success' : 'warning'),
            ])
            ->recordActions([
                Action::make('togglePaid')
                    ->label(function (Enrollment $record): string {
                        $month = Carbon::now()->format('Y-m');
                        $payment = $record->monthlyPayments->where('month', $month)->first();

                        return ($payment && $payment->paid_at !== null) ? __('Mark as Unpaid') : __('Mark as Paid');
                    })
                    ->icon(function (Enrollment $record): string {
                        $month = Carbon::now()->format('Y-m');
                        $payment = $record->monthlyPayments->where('month', $month)->first();

                        return ($payment && $payment->paid_at !== null) ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                    })
                    ->color(function (Enrollment $record): string {
                        $month = Carbon::now()->format('Y-m');
                        $payment = $record->monthlyPayments->where('month', $month)->first();

                        return ($payment && $payment->paid_at !== null) ? 'warning' : 'success';
                    })
                    ->action(function (Enrollment $record): void {
                        $month = Carbon::now()->format('Y-m');

                        $paymentRecord = MonthlyPaymentRecord::firstOrNew([
                            'enrollment_id' => $record->id,
                            'month' => $month,
                        ]);

                        $isNowPaid = false;
                        if ($paymentRecord->paid_at === null) {
                            $paymentRecord->paid_at = now();
                            $isNowPaid = true;
                        } else {
                            $paymentRecord->paid_at = null;
                        }

                        $paymentRecord->save();

                        Notification::make()
                            ->success()
                            ->title($isNowPaid ? __('Marked as Paid') : __('Marked as Unpaid'))
                            ->duration(2000)
                            ->send();
                    }),
            ])
            ->recordUrl(null)
            ->recordAction(null);
    }
}
