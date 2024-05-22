<div class="flex flex-col">
    <h2>Price notifications to {{ $this->propertyName }}</h2>
    @foreach ($this->getPriceNotifications() as $priceNotification)
        <div class="p-2 my-2 border-2">
            <ul>
                <li>{{__('Checkin')}}: {{ format_date_with_weekday($priceNotification->checkin) }}</li>
                <li>{{__('Type')}}: {{ __($priceNotification->type->value) }}</li>
                <li>{{__('Property')}}: {{ $priceNotification->propertyName }}</li>
                <li>{{__('Before')}}: ${{ $priceNotification->oldPrice }}</li>
                <li>{{__('After')}}: ${{ $priceNotification->newPrice }}</li>
            </ul>
        </div>
    @endforeach
</div>
