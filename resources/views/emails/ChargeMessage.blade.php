@component('mail::layout')
{{-- Header --}}
@slot('header')

@component('mail::header', ['url' => config('app.url') , 'companyImage' => (isset($data['companyImage']) ? $data['companyImage'] : '' )])

{{  (!isset($data['companyName']) ?  config('app.name') : $data['companyName']) }}
@endcomponent
@endslot

# Dear {{ ucwords($data['name']) }}

Your Charge Ended with these credentials : {{$data['msgs']}}
 
Next step is to @component('mail::button', ['url' => $data['url'], 'color' => 'green'])
    Pay Now
@endcomponent and complete the setup.
You can be up and running within few minutes.

if clicking the button does not take you directly, click the link below or copy the link and paste it into the address box of a browser

@php echo $data['url'] @endphp

Should you have any questions or need assistance setting up, please do no hesitate to contact us.

Regard,
Team 
{{(!isset($data['companyName']) ?  config('app.name') : $data['companyName']) }}


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
