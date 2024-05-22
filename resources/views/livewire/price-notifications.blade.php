<div class="flex flex-col">
    <h2>{{ __("Price Notifications to") }} {{ $this->propertyName }}</h2>
    @if ($this->priceNotifications->isEmpty())
        <div class="p-2 my-2 border-2">
            <p>{{__('No price notifications today')}}</p>
        </div>
    @else
        @foreach ($this->priceNotifications as $priceNotification)
            <div class="p-2 my-2 border-2">
                <ul>
                    <li>{{__('Checkin')}}: {{ format_date_with_weekday($priceNotification->checkin) }}</li>
                    <li>{{__('Type')}}: {{ __($priceNotification->type->value) }}</li>
                    <li>{{__('Property')}}: {{ $priceNotification->property->name }}</li>
                    <li>{{__('Before')}}: ${{ \Illuminate\Support\Number::format($priceNotification->before, 0) }}</li>
                    <li>{{__('After')}}: ${{ \Illuminate\Support\Number::format($priceNotification->after, 0) }}</li>
                    @if ($priceNotification->type === \App\Enums\PriceNotificationTypeEnum::PriceUp || $priceNotification->type === \App\Enums\PriceNotificationTypeEnum::PriceDown)
                    <li>{{__('Variation')}}: {{ \Illuminate\Support\Number::format($priceNotification->variation, 0) }}%</li>
                    <li>{{__('Avg Variation')}}: {{ \Illuminate\Support\Number::format($priceNotification->averageVariation, 0) }}%</li>
                    @endif
                </ul>
            </div>
        @endforeach
    @endif
</div>
