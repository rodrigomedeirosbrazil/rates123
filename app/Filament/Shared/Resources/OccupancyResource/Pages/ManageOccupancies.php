<?php

namespace App\Filament\Shared\Resources\OccupancyResource\Pages;

use App\Filament\Shared\Resources\OccupancyResource;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class ManageOccupancies extends ManageRecords
{
    protected static string $resource = OccupancyResource::class;

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
