<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Models\Expense;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ExpenseResource;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    //protected function mutateFormDataBeforeSave(array $data): array
    //{
    //    dd($data);
    //    $expense = Expense::where('name', $this->record->name)
    //        ->where('amount', $this->record->amount)
    //        ->each(fn ($e) => $e->update([
    //            $data
    //        ]));

    //    return $data;
    //}
}
