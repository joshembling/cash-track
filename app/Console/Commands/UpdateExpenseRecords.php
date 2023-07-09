<?php

namespace App\Console\Commands;

use App\Models\Expense;
use Illuminate\Console\Command;

class UpdateExpenseRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:expenses {{--revert}}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the null original_amount column';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expenses = Expense::whereNull('original_amount')->get();

        if ($expenses) {
            $expenses->each(fn ($e) => $e->update([
                'original_amount' => $e->amount
            ]));
        }

        // revert
        $expenses = Expense::whereNotNull('original_amount')->get();

        if ($expenses && $this->option('revert')) {
            $expenses->each(fn ($e) => $e->update([
                'original_amount' => null
            ]));
        }
    }
}
