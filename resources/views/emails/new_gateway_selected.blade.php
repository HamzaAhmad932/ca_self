@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            @if ( empty($data['companyInitials']) )
                <img src="{{ $data['companyImage'] }}" style="width:45px;margin: -30px auto 0 !important;">
            @else
                <span style="background-color: #334e68 !important; border-radius: 50% !important; color: #fff !important;
          display: block !important;font-size: 16px !important;font-weight: 600 !important; height: 45px !important;
          line-height: 45px !important; margin: -30px auto 0 !important; text-align: center !important;width: 45px !important;">
					{{ $data['companyInitials'] }}
				</span>
            @endif
            <h5 style="margin: 7px 0 0 0 !important;">{{ ( isset($data['companyName']) ? $data['companyName'] : config('app.name') ) }}</h5>
        @endcomponent
    @endslot

# Dear {{ ucwords($data['name']) }}

You have recently changed your payment gateway. This means:

1.	your existing reservations card details are no longer accessible by ChargeAutomation
2.	Any Refund for previous transactions will have to take place through your previous  payment gateway, not through ChargeAutomation
3.	You or the guest will have to re-enter card details for the following reservations that have future transaction scheduled:


@if($data['textData'] != null)
@component('mail::panel')

{!! $data['textData'] !!}

@endcomponent
@endif
@component('mail::button', ['url' => $data['url'] ])
        View Detail
@endcomponent

if clicking the button does not take you directly, click the link below or copy the link and paste it into the address box of a browser

@php echo $data['url'] @endphp
 
Yours,

The {{(!isset($data['companyName']) ?  config('app.name') : $data['companyName']) }}
 Team


{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
@endcomponent
@endslot
@endcomponent


