<?php

namespace App\Filament\Shared\Resources\CalendarResource\Widgets;

use App\Domains\Bookings\Contracts\BookingActionContract;
use App\Domains\Bookings\Enums\BookingActionEventType;
use App\Units\Filament\Resources\IntegrationResource;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Actions\PasswordButtonAction;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Bookings\Models\Booking;
use App\Models\MonitoredData;
use Filament\Infolists\Components\TextEntry;
use Saade\FilamentFullCalendar\Data\EventData;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Filament\Infolists\Components\Section as InfolistSection;

class CalendarWidget extends FullCalendarWidget
{
    public Model|string|null $model = MonitoredData::class;
    public MonitoredData $calendar;

    public array $filters = [];

    #[On('bookings-filter-changed')]
    public function filtersUpdated($filters): void
    {
        $this->filters = $filters;
        $this->refreshRecords();
    }

    public function fetchEvents(array $info): array
    {
        if (! $this->getFilter('is_filtered', false)) {
            $this->dispatchTotalBookingsEvent(null);

            return [];
        }

        $bookings = $this->getEloquentQuery()
            ->join('listings', 'bookings.listing_id', '=', 'listings.id')
            ->where('begin', '>=', $info['start'])
            ->where('end', '<=', $info['end'])
            ->when($this->getFilter('type'), fn ($query, $type) => $this->queryType($query, $type))
            ->when(
                $this->getFilter('filter_by'),
                fn ($query, $filterBy) => $this->queryIntegration($query, $filterBy)
            )
            ->when(
                $this->getFilter('external_booking_id'),
                fn ($query, $id) => $query->where('external_booking_id', $id)
            )
            ->when(
                $this->getFilter('external_listing_id'),
                fn ($query, $id) => $query->where('listings.external_listing_id', $id)
            )
            ->when($this->getFilter('status'), fn ($query, $status) => $this->queryStatus($query, $status))
            ->select('bookings.*', 'listings.external_listing_id')
            ->get()
            ->map(
                fn (Booking $booking) => EventData::make()
                    ->id($booking->id)
                    ->title("EBI: $booking->external_booking_id | ELI: $booking->external_listing_id")
                    ->start($booking->begin)
                    ->end($booking->end)
                    ->extraProperties([
                        ...$this->getExtraProperties($booking),
                    ])
            );

        $this->dispatchTotalBookingsEvent($bookings->count());

        return $bookings->toArray();
    }

    public function getExtraProperties(Booking $booking): array
    {
        if ($booking->trashed()) {
            return [
                'backgroundColor' => '#fff',
                'borderColor' => '#eee',
                'textColor' => '#333',
            ];
        }

        if ($booking->is_blocked_date) {
            return [
                'backgroundColor' => '#937264',
                'borderColor' => '#eee',
                'textColor' => '#fff',
            ];
        }

        return [];
    }

    public function resolveEventRecord(array $data): Model
    {
        return Booking::withTrashed()->find($data['id']);
    }

    protected function getFilter(string $key, mixed $default = null): mixed
    {
        return data_get($this->filters, $key, $default);
    }

    protected function queryIntegration($query, string $filterBy): void
    {
        if ($filterBy === 'integration_id') {
            $query->where('integration_id', $this->getFilter('integration_id'));

            return;
        }

        $query->join('integrations', 'listings.integration_id', '=', 'integrations.id')
            ->where('integrations.platform_id', $this->getFilter('platform_id'))
            ->where('integrations.account_id', $this->getFilter('account_id'));
    }

    protected function queryType($query, ?string $type): void
    {
        if ($type === 'blocked-dates') {
            $query->where('is_blocked_date', true);
        } elseif ($type === 'bookings') {
            $query->where('is_blocked_date', false);
        }
    }

    protected function queryStatus($query, ?string $status): void
    {
        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function dispatchTotalBookingsEvent(?int $totalBookings): void
    {
        $this->dispatch('total-bookings', $totalBookings);
    }


    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistGrid::make([])->schema([
                    InfolistSection::make([
                        TextEntry::make('id'),
                    ]),
                ]),
            ]);
    }


    protected function viewAction(): Action
    {
        return parent::viewAction()
            ->modalHeading('Booking Details')
            ->infolist([
                InfolistGrid::make([])->schema([
                    InfolistSection::make([
                        InfolistGrid::make([])->schema([
                            TextEntry::make('id'),
                            TextEntry::make('external_booking_id'),
                            TextEntry::make('reservation_code'),
                            TextEntry::make('uid'),
                            TextEntry::make('is_blocked_date'),
                            TextEntry::make('deletedByUser.name')
                                ->hidden(fn () => ! $this->getRecord()->trashed())
                                ->label('Deleted By'),
                        ])
                            ->columns(5),
                    ])
                        ->collapsible()
                        ->description('Basic'),
                    InfolistSection::make([
                        InfolistGrid::make([])->schema([
                            TextEntry::make('begin'),
                            TextEntry::make('end'),
                            TextEntry::make('processed_at'),
                            TextEntry::make('deleted_at')->hidden(fn () => ! $this->getRecord()->trashed()),
                        ])
                            ->columns(4),
                    ])
                        ->collapsible()
                        ->description('Dates'),
                    InfolistSection::make([
                        InfolistGrid::make([])->schema([
                            TextEntry::make('summary'),
                            TextEntry::make('customer_name'),
                            TextEntry::make('description'),
                            TextEntry::make('guests_adults'),
                            TextEntry::make('guests_children'),
                            TextEntry::make('guests_infants'),
                        ])
                            ->columns(3),
                    ])
                        ->collapsible()
                        ->description('Details'),
                    InfolistSection::make([
                        InfolistGrid::make([])->schema([
                            TextEntry::make('listing_id')->label('Listing id'),
                            TextEntry::make('listing.external_listing_id')->label('External Listing ID'),
                            TextEntry::make('listing.alias')->label('Alias'),
                            TextEntry::make('listing.street_line_1')->label('Street Line 1'),
                        ])
                            ->columns(4),
                    ])
                        ->collapsible()
                        ->description('Listing'),
                ])
                    ->columns(4),
            ])
            ->modalFooterActions($this->buildFooterActions())
            ->modalFooterActionsAlignment('right');
    }

    private function buildFooterActions(): array
    {
        return [
            Action::make('Resend')
                ->hidden(fn () => ! $this->showDisplayActions())
                ->requiresConfirmation()
                ->modalFooterActions([
                    PasswordButtonAction::make('resendAsUpdated')
                        ->color('gray')
                        ->action(fn () => $this->resendEvent(BookingActionEventType::Update))
                        ->cancelParentActions(),

                    PasswordButtonAction::make('resendAsCreated')
                        ->color('primary')
                        ->action(fn () => $this->resendEvent(BookingActionEventType::Create))
                        ->cancelParentActions(),
                ]),

            Action::make('delete')
                ->hidden(fn () => ! $this->showDisplayActions(false))
                ->requiresConfirmation()
                ->color('danger')
                ->modalFooterActions([
                    PasswordButtonAction::make('deleteWithoutSendEvent')
                        ->color('gray')
                        ->action(fn () => $this->deleteAction(false))
                        ->hidden(fn () => ! $this->showDisplayActions())
                        ->cancelParentActions(),

                    PasswordButtonAction::make('deleteAndSendEvent')
                        ->color('primary')
                        ->action(fn () => $this->deleteAction())
                        ->cancelParentActions(),
                ])
                ->modalIcon('heroicon-o-trash'),

            Action::make('seeIntegration')
                ->action(fn () => $this->redirect(
                    IntegrationResource::getUrlFilteredByIntegrationId($this->getRecord()->listing->integration_id)
                ))
                ->color('gray'),
        ];
    }

    private function afterAction(string $notification)
    {
        Notification::make()
            ->title($notification)
            ->success()
            ->send();

        $this->refreshRecords();
        $this->closeActionModal();
    }

    private function resendEvent(BookingActionEventType $eventType): void
    {
        $record = $this->getRecord();
        if (! $record) {
            return;
        }

        resolve(BookingActionContract::class)
            ->resend($record, $eventType);

        $message = match ($eventType) {
            BookingActionEventType::Update => 'Updated event sent successfully',
            BookingActionEventType::Create => 'Created event sent successfully',
        };

        $this->afterAction($message);
    }

    private function deleteAction(bool $sendEvent = true): void
    {
        $record = $this->getRecord();
        $user = auth()->user();
        if (! $record) {
            return;
        }

        resolve(BookingActionContract::class)
            ->delete($record, $user, $sendEvent);

        $message = $sendEvent
            ? 'Booking deleted and event send successfully'
            : 'Booking deleted successfully';

        $this->afterAction($message);
    }

    private function showDisplayActions(bool $checkTrashed = true): bool
    {
        $record = $this->getRecord();

        if (! $record) {
            return false;
        }

        if ($checkTrashed && $record->trashed()) {
            return false;
        }

        return ! $record->is_blocked_date;
    }

    public function resolveRecordRouteBinding(int | string $key): ?Model
    {
        return Booking::withTrashed()
            ->find($key);
    }
}
