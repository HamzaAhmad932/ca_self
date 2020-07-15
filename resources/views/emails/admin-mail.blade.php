@component('mail::layout')

@slot('header')

@component('mail::header', ['url' => config('app.url')])
ChargeAutomation
@endcomponent
@endslot

A New client just registered with us at ChargeAutomation.
<table>
<tr><td><strong>Full Name:</strong></td><td> {{ $data->name }}</td></tr>
<tr><td><strong>Company Name:</strong></td><td> {{ $data->user_account->name}}</td></tr>
<tr><td><strong>Contact Number:</strong></td><td> {{ $data->phone }}</td></tr>
<tr><td><strong>Email Address:</strong></td><td> {{ $data->email }}</td></tr>
<tr><td><strong>Current PMS/Channel Manager:</strong></td><td> {{ $data->user_account->current_pms }}</td></tr>
</table>

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