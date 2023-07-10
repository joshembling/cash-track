<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\PieChartWidget;

class ExpenseTypes extends PieChartWidget
{
    protected static ?string $heading = 'Expense Tags';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = Expense::with('tags')->get();

        foreach ($data as $expense) {
            foreach ($expense->tags as $tag) {
                if (isset($tags[$tag->name])) {
                    $tags[$tag->name]++;
                } else {
                    $tags[$tag->name] = 1;
                }
            }
        }

        $colors = [
            "#9BD0F5",
            "#fdd9d9",
            "#FF8C00", // Dark Orange
            "#00FF7F", // Spring Green
            "#DC143C", // Crimson
            "#8B4513"  // Saddle Brown
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Blog posts',
                    'data' => array_values($tags),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => array_keys($tags),
        ];
    }
}
