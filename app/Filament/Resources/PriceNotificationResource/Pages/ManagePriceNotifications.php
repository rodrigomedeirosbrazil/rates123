<?php

namespace App\Filament\Resources\PriceNotificationResource\Pages;

use App\Filament\Resources\PriceNotificationResource;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class ManagePriceNotifications extends ManageRecords
{
    protected static string $resource = PriceNotificationResource::class;

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
