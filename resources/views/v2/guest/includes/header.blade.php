{{--@php--}}
{{--    $bookingDetails = json_decode($bookingDetails);--}}

{{--    $property_email = $bookingDetails->propertyInfo->property_email;--}}
{{--    $property_name = $bookingDetails->propertyInfo->name;--}}
{{--@endphp--}}

{{--<div class="gp-header">--}}
{{--    <a class="company-logo" href="#">--}}
{{--        --}}{{--        <img src="img/gp-logo.png" alt="{{$booking_info->property_info->name}}"/>--}}
{{--        <h4 style="color: #EC485B; font-size: 23px; font-weight: bold; font-family: 'Roboto', sans-serif;">{{$property_name}}</h4>--}}
{{--        <h4 style="color: #EC485B; font-size: 23px; font-weight: bold; font-family: 'Roboto', sans-serif;">Property Name</h4>--}}
{{--    </a>--}}
{{--    <div class="gp-nav">--}}
{{--        @if(isset($property_tel_no) && ($property_tel_no !=null || $property_tel_no != ''))--}}
{{--            <a href="#"><i class="fas fa-phone-volume">--}}
{{--                </i><span> {{$property_tel_no}}</span>--}}
{{--                </i><span> Tel</span>--}}
{{--            </a>--}}
{{--        @endif--}}
{{--        @if(!empty($property_email))--}}
{{--            <a href="mailto:{{$property_email}}">--}}
{{--        <a href="#">--}}
{{--                <i class="far fa-envelope"> </i><span> Email Us</span>--}}
{{--            </a>--}}
{{--        @endif--}}
{{--    </div>--}}
{{--    @if ($guestChatStatus == 1)--}}
{{--        <a class="btn btn-success text-white chat-open chat-btn-v2" href="#0" data-target="#chat-panel" aria-controls="chat-panel">--}}
{{--            <i class="far fa-comment"> </i>--}}
{{--            <span> Live Chat </span>--}}
{{--        </a>--}}
{{--    @endif--}}
{{--</div>--}}



<div class="gp-header">
    <a class="company-logo" href="#">
        <h4 style="color: #EC485B; font-size: 23px; font-weight: bold; font-family: 'Roboto', sans-serif;">{{$header['booking']->property_info->name}}</h4>
    </a>
    <div class="gp-nav">
            <a href="mailto:support@chargeautomation.com">
                <a href="#">
                    <i class="far fa-envelope"> </i><span> Email Us</span>
                </a>
            </a>
    </div>
    @if($header['is_chat_active'])
        <a class="btn btn-success text-white chat-open chat-btn-v2" href="#" data-target="#chat-panel-header" aria-controls="chat-panel">
            <i class="far fa-comment"> </i>
            <span> Live Chat </span>
        </a>
    @endif
</div>
