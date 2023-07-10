<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Console\Command;
use App\Notifications\NonRecurringExpenseReminder;

class NonRecurringExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'non-recurring:expenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email where 2 days have past since adding a new expense and not been marked as paid.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expenses = Expense::whereNull('paid_at')
            ->where('recurring', false)
            ->whereNull('reminder_sent_user')
            ->get();

        if ($expenses) {
            $expenses->each(function ($expense) {
                $primary = User::find($expense->user_id);
                $primary->notify((new NonRecurringExpenseReminder()));

                $expense->reminder_sent_user = now();

                if (!is_null($expense->payee_id)) {
                    $secondary = User::find($expense->payee_id);
                    $secondary->notify((new NonRecurringExpenseReminder()));

                    $expense->reminder_sent_payee = now();
                }

                $expense->save();
            });
        }
    }
}
