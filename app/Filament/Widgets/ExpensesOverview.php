<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Expense;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\BarChartWidget;

class ExpensesOverview extends BarChartWidget
{
    protected static ?string $heading = 'Expenses Overview';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::query(
            Expense::query()
                ->where('user_id', auth()->user()->id)
                ->orWhere('payee_id', auth()->user()->id)
        )
            ->between(
                start: now()->subMonths(2),
                end: now(),
            )
            ->dateColumn('expense_date')
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#9BD0F5',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('M Y')),
        ];
    }
}
