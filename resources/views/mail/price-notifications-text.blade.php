{{__('Hi')}} {{ $user->name }},

@if ($priceNotificationsTextTable === null)
{{__('No price notifications today')}}.
@else
{{__("These are today/'s price notifications")}}:

{{$priceNotificationsTextTable}}
@endif

{{__('Thanks')}},
{{__('Rates123')}}.
