<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Models\Expense;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ExpenseResource;
use Illuminate\Database\Eloquent\Builder;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->user_id === auth()->user()->id) {
            if (
                array_key_exists('amount', $data) &&
                array_key_exists('split_percentage', $data) &&
                $data['split_percentage'] !== 'Other' &&
                $data['split'] === true
            ) {
                $data['split_amount'] = $this->splitPayment($data['amount'], $data['split_percentage']);
            } else {
                $data['payee_id'] = null;
                $data['split_percentage'] = null;
                $data['split_amount'] = null;
            }
        }

        if ($this->record->payee_id === auth()->user()->id) {
            if ($this->record->payee_paid === false && $data['payee_paid'] === true) {
                $data['amount'] = $this->splitPayment($this->record['amount'], $this->record['split_percentage']);
            }

            if ($this->record->payee_paid === true && $data['payee_paid'] === false) {
                $data['amount'] = $this->revokePayment($this->record['amount'], $this->record['split_percentage']);
            }
        }

        return $data;
    }

    public function splitPayment($amount, $percentage)
    {
        $res = $amount * ($percentage / 100);

        return number_format((float) $res, 2, '.', '');
    }

    public function revokePayment($amount, $percentage)
    {
        $res = $amount / ($percentage / 100);

        return number_format((float) $res, 2, '.', '');
    }
}
