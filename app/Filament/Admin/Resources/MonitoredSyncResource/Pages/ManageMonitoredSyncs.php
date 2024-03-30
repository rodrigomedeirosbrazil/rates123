<?php

namespace App\Filament\Admin\Resources\MonitoredSyncResource\Pages;

use App\Filament\Admin\Resources\MonitoredSyncResource;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class ManageMonitoredSyncs extends ManageRecords
{
    protected static string $resource = MonitoredSyncResource::class;

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
