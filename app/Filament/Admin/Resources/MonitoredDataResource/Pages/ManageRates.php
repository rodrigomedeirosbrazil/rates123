<?php

namespace App\Filament\Admin\Resources\RateResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Admin\Resources\RateResource;

class ManageRates extends ManageRecords
{
    protected static string $resource = RateResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function paginateTableQuery(Builder $query): CursorPaginator
    {
        return $query->cursorPaginate(
            ($this->getTableRecordsPerPage() === 'all')
            ? $query->count()
            : $this->getTableRecordsPerPage()
        );
    }
}
