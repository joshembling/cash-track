<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Expense;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\BarChartWidget;

class BudgetRemaining extends BarChartWidget
{
    protected static ?string $heading = 'Budget Remaining';

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
        'scales' => [
            'x' => [
                'stacked' => true,
            ],
            'y' => [
                'stacked' => true,
            ],
        ]
    ];

    protected function getData(): array
    {
        $data = Trend::model(Expense::class)
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
                    'label' => 'Expenditure',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#9BD0F5',
                ],
                [
                    'label' => 'Amount Remaining',
                    'data' => $data->map(fn (TrendValue $value) => auth()->user()->monthly_salary - $value->aggregate),
                    'backgroundColor' => '#FAA0A0',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('M Y')),
        ];
    }
}
