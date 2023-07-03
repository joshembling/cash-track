<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Expense;
use App\Notifications\NewExpense;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        //dd($expense);
        if ($expense->payee_id !== null && $expense->split) {
            $secondary = User::find($expense->payee_id);
            $secondary->notify(new NewExpense($expense));
        }
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        //
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        //
    }

    /**
     * Handle the Expense "restored" event.
     */
    public function restored(Expense $expense): void
    {
        //
    }

    /**
     * Handle the Expense "force deleted" event.
     */
    public function forceDeleted(Expense $expense): void
    {
        //
    }
}
