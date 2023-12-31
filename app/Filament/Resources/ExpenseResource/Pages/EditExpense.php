<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Models\Expense;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Pages\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ExpenseResource;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn (Model $record) => $record->payee_id === auth()->user()->id),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // if original user
        if ($this->record->user_id === auth()->user()->id) {

            // modifying original amount
            if (array_key_exists('original_amount', $data) && $data['original_amount'] !== $this->record['original_amount']) {
                $data['amount'] = $data['original_amount'];
                $data['split_amount'] = $this->splitPayment($data['original_amount'], $data['split_percentage']);
            }

            if (
                array_key_exists('split', $data) &&
                $data['split'] === false
            ) {
                $data['payee_id'] = null;
                $data['payee_paid'] = false;
                $data['split'] = false;
                $data['split_percentage'] = null;
                $data['split_amount'] = null;
            }
        }

        if (array_key_exists('payee_paid', $data)) {

            // payee_paid in db
            if (
                $this->record->payee_paid &&
                $data['payee_paid'] &&
                array_key_exists('original_amount', $data)
            ) {
                if ($data['original_amount'] === $this->record['original_amount'] && $data['split_percentage'] !== 50) {
                    $data['amount'] = $this->record['amount'];
                } elseif ($data['original_amount'] !== $this->record['original_amount']) {
                    $data['amount'] = $this->splitPayment($data['original_amount'], $data['split_percentage']);
                } else {
                    $data['amount'] = $data['original_amount'];
                }
            }

            // just switched to paid
            if (!$this->record->payee_paid && $data['payee_paid']) {
                $data['amount'] = $this->splitPayment($data['original_amount'], $data['split_percentage'] ?? $this->record['split_percentage']);

                $data['paid_at'] = now();
            }

            if ($this->record->payee_paid && !$data['payee_paid']) {
                $data['amount'] = $this->revokePayment($data['original_amount'], $data['split_percentage'] ?? $this->record['split_percentage']);
                $data['split_amount'] = $this->splitPayment($data['original_amount'], $data['split_percentage'] ?? $this->record['split_percentage']);

                $data['paid_at'] = null;
            }
        }

        if (array_key_exists('paid_at', $data)) {
            if (is_null($this->record->paid_at) && $data['paid_at'] === true) {
                $data['paid_at'] = now();
            }

            if (!is_null($this->record->paid_at) && $data['paid_at'] === true) {
                $data['paid_at'] = $this->record->paid_at;
            }

            if (!is_null($this->record->paid_at) || $data['paid_at'] === false) {
                $data['paid_at'] = null;
            }
        }

        if (array_key_exists('recurring', $data)) {
            if ($data['recurring'] === false) {
                $data['frequency'] = null;
            }
        }

        return $data;
    }


    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Go back')
            ->icon('heroicon-s-arrow-sm-left')
            ->url($this->previousUrl ?? static::getResource()::getUrl())
            ->color('secondary');
    }

    public function splitPayment($amount, $percentage)
    {
        $res = $amount * ($percentage / 100);

        return number_format((float) $res, 2, '.', '');
    }

    public function revokePayment($amount, $percentage)
    {
        //$res = $amount / ($percentage / 100);
        $res = $amount;
        return number_format((float) $res, 2, '.', '');
    }
}
