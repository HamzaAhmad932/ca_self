@extends('v2.guest.app')
@section('title', 'some title')

@section('page_content')

    @php
        $guestPortalLink = \Illuminate\Support\Facades\URL::signedRoute('guest_portal', ['id'=>$b_info]);
        $checkoutPostUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('checkout-status-update', now()->addMinutes(30), ['id'=>$id, 'type'=>$type]);
    @endphp

    <checkout-3ds
            guest-name = "{{ $guest_name }}"
            is-paid = "{{ $isPaid ? 1 : 0}}"
            f-name = "{{ $fName }}"
            l-name = "{{ $lName }}"
            email = "{{ $email }}"
            phone = "{{ $phone }}"
            guest-portal-link = "{{ $guestPortalLink }}"
            client-secret = "{{ $client_secret }}"
            button-text = "{{ $button_text }}"
            public-key = "{{ $public_key }}"
            account-id = "{{ $account_id }}"
            b-info = "{{ $b_info }}"
            id = "{{ $id }}"
            type = "{{ $type }}"
            checkout-post-url = "{{ $checkoutPostUrl }}"
            :precheckin="false"
            postal_code = "{{ $postal_code }}"
            country = "{{ $country }}"
            address_line1 = "{{ $address_line1 }}"
            city = "{{ $city }}"
            state = "{{ $state }}"
    ></checkout-3ds>


@endsection

@push('below_script')
    @if(!$isPaid)
        <script src="https://js.stripe.com/v3/"></script>
    @endif
@endpush