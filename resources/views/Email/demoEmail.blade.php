@component('mail::message')
# Your Travel Details

Thank you for booking your trip to Paris.

@component('mail::button', ['url' => ''])
#1b27q85
@endcomponent

Thanks, Island-Hopping.<br>
{{ config('Island-Hopping') }}
@endcomponent
