<?php

namespace App\Filament\Resources\MonitoredDataResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MonitoredDataResource;

class ManageMonitoredDatas extends ManageRecords
{
    protected static string $resource = MonitoredDataResource::class;

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
