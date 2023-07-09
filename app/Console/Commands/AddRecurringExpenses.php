<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Expense;
use App\Notifications\RecurringExpenseReminder;
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

    protected function validateFrequency(Expense $expense, string $frequency): void
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

        if (Carbon::parse($expense->expense_date)->add($interval)->subDay() <= now()) {
            $this->sendReminder($expense);
        }

        if (Carbon::parse($expense->expense_date)->add($interval) <= now()) {
            $existingDuplicate = Expense::where('user_id', $expense->user_id)
                ->where('expense_date', Carbon::parse($expense->expense_date)->add($interval))
                ->first();

            if (!$existingDuplicate) {
                $new_expense = $expense->replicate();

                $new_expense->fill([
                    'expense_date' => Carbon::parse($expense->expense_date)->add($interval),
                    'paid_at' => !is_null($expense->paid_at) ? Carbon::parse($expense->paid_at)->add($interval) : null,
                    'reminder_sent_user' => null,
                    'reminder_sent_payee' => null,
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

    protected function sendReminder(Expense $expense): void
    {
        $delay = now()->addMinutes(1);

        if ($expense->reminder_sent_user === null) {
            $primary = User::find($expense->user_id);
            $primary->notify((new RecurringExpenseReminder($expense))->delay($delay));

            $expense->update([
                'reminder_sent_user' => $delay,
            ]);
        }

        if ($expense->reminder_sent_payee === null && $expense->split && $expense->payee_id !== null) {
            $secondary = User::find($expense->payee_id);
            $secondary->notify((new RecurringExpenseReminder($expense))->delay($delay));

            $expense->update([
                'reminder_sent_payee' => $delay,
            ]);
        }
    }
}
