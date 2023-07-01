<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Expense;
use Illuminate\Console\Command;

class AddRecurringExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:expenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add recurring expenses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expenses = Expense::where('recurring', true)
            ->where('copied', false)
            ->get();

        if ($expenses) {
            $expenses->each(function ($expense) {

                switch ($expense->frequency) {
                    case 'Weekly':
                        $this->validateFrequency($expense, 'Weekly');
                        break;

                    case 'Bi-weekly':
                        $this->validateFrequency($expense, 'Bi-weekly');
                        break;

                    case 'Monthly':
                        $this->validateFrequency($expense, 'Monthly');
                        break;

                    case 'Quarterly':
                        $this->validateFrequency($expense, 'Quarterly');
                        break;
                }
            });
        }
    }

    protected function validateFrequency(Expense $expense, string $frequency)
    {
        switch ($frequency) {
            case 'Weekly':
                $interval = '1 week';
                break;

            case 'Bi-weekly':
                $interval = '2 weeks';
                break;

            case 'Monthly':
                $interval = '1 month';
                break;

            case 'Quarterly':
                $interval = '3 months';
                break;
        }

        if (Carbon::parse($expense->date)->add($interval) <= now()) {
            $existingDuplicate = Expense::where('user_id', $expense->user_id)
                ->where('date', Carbon::parse($expense->date)->add($interval))
                ->first();

            if (!$existingDuplicate) {
                $new_expense = $expense->replicate();

                $new_expense->fill([
                    'date' => Carbon::parse($expense->date)->add($interval),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $new_expense->save();

                $expense->tags->each(function ($tag) use ($new_expense) {
                    $new_expense->tags()->attach($tag->id, [
                        'expense_id' => $new_expense->id,
                        'tag_id' => $tag->id,
                    ]);
                });

                $expense->update([
                    'copied' => true
                ]);
            }
        }
    }
}
