<?php

namespace App\Filament\Widgets;

use Closure;
use Filament\Tables;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Widgets\TableWidget as BaseWidget;

class OutstandingExpenses extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected function getTableQuery(): Builder
    {
        return Expense::query()->whereNull('paid_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('expense_date')
                ->label('Date')
                ->date('d/m/y')
                ->sortable(),
            Tables\Columns\TextColumn::make('name')
                ->label('Expense')
                ->searchable()
                ->sortable()
                ->wrap(),
            Tables\Columns\TextColumn::make('amount')
                ->sortable()
                ->prefix('£'),
            Tables\Columns\TextColumn::make('user.name')
                ->label('Added by')
                ->sortable()
                ->searchable(),
            Tables\Columns\IconColumn::make('recurring')
                ->boolean(),
            Tables\Columns\IconColumn::make('split')
                ->boolean(),
            Tables\Columns\IconColumn::make('paid_at')
                ->sortable()
                ->label('Paid')
                ->boolean(),
            Tables\Columns\TextColumn::make('frequency')
                ->sortable(),
            Tables\Columns\TextColumn::make('category.name')
                ->label('Category')
                ->sortable()
                ->searchable()
                ->wrap(),
            Tables\Columns\TextColumn::make('tags.name')
                ->searchable()
                ->wrap(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkAction::make('mark_as_paid')
                ->icon('heroicon-s-shield-check')
                ->action(fn (Collection $records) => $records->where('paid_at', null)
                    ->each(function ($record) {
                        $record->update([
                            'paid_at' => now()
                        ]);
                    })),
            Tables\Actions\BulkAction::make('mark_as_unpaid')
                ->icon('heroicon-s-x-circle')
                ->action(fn (Collection $records) => $records
                    ->each(function ($record) {
                        $record->update([
                            'paid_at' => null
                        ]);
                    })),
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }
}
