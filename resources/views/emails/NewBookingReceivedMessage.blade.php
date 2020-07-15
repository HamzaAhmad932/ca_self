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
#Hello  {{ ucwords($data['name']) }}

{{$data['msgs']}}
 
@component('mail::button', ['url' => $data['url'], 'color' => 'green'])
   {{$data['btn']}}
@endcomponent

If clicking the button does not take you directly, click the link below or copy the link and paste it into the address box of a browser

@php echo $data['url'] @endphp

@component('mail::regards')
##We are looking forward to welcoming you!

Warm regards,

{{(!isset($data['companyName']) ?  config('app.name') : $data['companyName']) }}
@endcomponent

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


