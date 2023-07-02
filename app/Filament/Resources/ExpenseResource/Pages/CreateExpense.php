<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use Filament\Pages\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ExpenseResource;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

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

        return $data;
    }

    public function splitPayment($amount, $percentage)
    {
        $res = $amount * ($percentage / 100);

        return number_format((float) $res, 2, '.', '');
    }
}
