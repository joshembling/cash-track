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
        if (!array_key_exists('user_id', $data)) {
            $data['user_id'] = auth()->id();
        }

        $data['original_amount'] = $data['amount'];

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

        if (array_key_exists('paid_at', $data)) {
            if ($data['paid_at'] === true) {
                $data['paid_at'] = now();
            } else {
                $data['paid_at'] = null;
            }
        }

        return $data;
    }

    public function splitPayment($amount, $percentage)
    {
        $res = $amount * ($percentage / 100);

        return number_format((float) $res, 2, '.', '');
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if ($resource::hasPage('view') && $resource::canView($this->record)) {
            return $resource::getUrl('view', ['record' => $this->record]);
        }

        if ($resource::hasPage('edit') && $resource::canEdit($this->record) && $this->record->user_id === auth()->id()) {
            return $resource::getUrl('edit', ['record' => $this->record]);
        }

        return $resource::getUrl('index');
    }
}
