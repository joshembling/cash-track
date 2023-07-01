<?php

namespace App\Filament\Resources\TagResource\Pages;

use Filament\Pages\Actions;
use Illuminate\Support\Str;
use App\Filament\Resources\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['name'] = Str::lower($data['name']);

        return $data;
    }
}
