@extends('layouts.admin')
@section('content')

    <style>

        .col-1, .col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, .col-10, .col-11, .col-12, .col, .col-auto, .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm, .col-sm-auto, .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12, .col-md, .col-md-auto, .col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg, .col-lg-auto, .col-xl-1, .col-xl-2, .col-xl-3, .col-xl-4, .col-xl-5, .col-xl-6, .col-xl-7, .col-xl-8, .col-xl-9, .col-xl-10, .col-xl-11, .col-xl-12, .col-xl, .col-xl-auto {
            padding-right: 4px;
            padding-left: 4px;
        }

        .autocomplete {
            /*the container must be positioned relative:*/
            position: relative;
            display: inline-block;
        }

        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: 2px solid #716aca;
            z-index: 99;
            /*position the autocomplete items to be the same width as the container:*/
            top: 110%;
            left: 5%;
            right: 5%;
        }
        .autocomplete-items div {
            padding: 8px;
            cursor: pointer;
            background-color: #E6EAF5;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            /*when hovering an item:*/
            background-color: #e9e9e9;
        }
        .autocomplete-active {
            /*when navigating through the items using the arrow keys:*/
            background-color: DodgerBlue !important;
            color: #ffffff;
        }

        .label {
            color: #0a6aa1;
            font-size: 1em;
        }

        .badge-room {
            background-color: #2a5164;
            color: white;
            padding: 4px;
            margin: 1px;
            display: inline-block;
        }

        .badge-room i {
            color: white;
        }

    </style>

    <div class="m-grid__item m-grid__item--fluid m-wrapper">

        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">

            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/leftnav.bookings_menu_title')}}</h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item m-nav__item--home">
                            <a href="#" class="m-nav__link m-nav__link--icon">
                                <i class="m-nav__link-icon la la-home"></i>
                            </a>
                        </li>
                        <li class="m-nav__separator">-</li>

                        <li class="m-nav__item">

                            <span class="m-nav__link-text">{{__('admin/leftnav.bookings_menu_create_test_bookings')}}</span>

                        </li>
                    </ul>
                </div>

            </div>
        </div>
        <!-- END: Subheader -->

        <div class="m-content" style="padding-bottom: 0; margin-bottom: 0;">
            <div class="m-portlet">
                <div class="m-portlet__body">

                    <div class="m-section__content">

                        <form action="" method="post" id="create-test-booking-form">
                            {{ csrf_field() }}

                            <div class="row">

                                <div class="col-md-2">
                                    <label class="label" for="booking-type">Booking Type</label>
                                    <div id="booking-type">
                                        <input type="radio" id="single-booking" value="single" checked name="booking-type" onclick="setType('single')">
                                        <label for="single-booking">&nbsp;&nbsp;Single Booking</label>
                                        <br>
                                        <input type="radio" id="group-booking" value="group" name="booking-type" onclick="setType('group')">
                                        <label for="group-booking">&nbsp;&nbsp;Group Booking</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="label" class="label" for="select-account">Test Accounts</label>
                                    <select required id="select-account" name="select-account" class="form-control" onchange="userAccountSelected(this.value)">
                                        <option value="-1" selected disabled="">Select Account</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="label" class="label" for="select-property">Properties</label>
                                    <select required id="select-property" name="select-property" class="form-control" onchange="propertySelected(this.value)">
                                        <option value="-1" selected disabled="">Select Property</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="select-room">Rooms</label>
                                    <select required id="select-room" name="select-room" class="form-control" onchange="roomSelected(this)">
                                        <option value="-1" selected disabled="">Select Room</option>
                                    </select>
                                </div>

                                <div class="col-md-2">

                                    <div id="room-contatiner"></div>



                                </div>

                            </div> <!-- Row End -->


                            <div class="row m--margin-top-20">

                                <div class="col-md-6">
                                    <label class="label" for="select-booking-source">Booking Source</label>
                                    <select required id="select-booking-source" name="select-booking-source" class="form-control" onchange="bookingSourceSelected(this)">
                                        <option value="-1" selected disabled>Select Source</option>
                                        @foreach($bookingSources as $bs)
                                            <option value="{{ $bs['channel_code'] }}">{{ $bs['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="label" for="card-type">Card Type</label>
                                    <select required id="card-type" name="card-type" class="form-control" onchange="cardTypeSelected(this)">
                                        <option value="CC" selected>Credit/Debit Card</option>
                                        <option value="VC">Virtual Card</option>
                                        <option value="BT">Bank Transfer</option>
                                    </select>
                                </div>

                            </div>  <!-- Row End -->

                            <div class="row m--margin-top-20" >

                                <div class="col-md-3">
                                    <label class="label" for="check-in-date">Check-In Date</label>
                                    <input autocomplete="off" placeholder="yyyy-mm-dd" data-provide="datepicker" required id="check-in-date" name="check-in-date" class="form-control booking-date" oninput="checkInDateChange(this)" onchange="checkInDateChange(this)"/>
                                </div>

                                <div class="col-md-3">
                                    <label class="label" for="check-out-date">Check-Out Date</label>
                                    <input autocomplete="off" placeholder="yyyy-mm-dd" data-provide="datepicker" required id="check-out-date" name="check-out-date" class="form-control booking-date"/>
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="total-price">Total Price</label>
                                    <input required value="{{ $price }}" type="number" id="total-price" name="total-price" placeholder="100.50" class="form-control" />
                                </div>

                                <div class="col-md-4 autocomplete">
                                    <label class="label" for="guest-email">Guest Email</label>
                                    <input type="text" id="guest-email" name="guest-email" class="form-control" />
                                </div>

                            </div> <!-- Row End -->

                            <div class="row m--margin-top-20">

                                <div class="col-md-2">
                                    <label class="label" for="guest-title">Guest Title</label>
                                    <input value="{{ $gTitle }}" type="text" id="guest-title" name="guest-title" placeholder="Mr." class="form-control" />
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="guest-first-name">First Name</label>
                                    <input value="{{ $fName }}" type="text" id="guest-first-name" name="guest-first-name" class="form-control" />
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="guest-last-name">Last Name</label>
                                    <input value="{{ $lName }}" type="text" id="guest-last-name" name="guest-last-name" class="form-control" />
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="guest-mobile">Mobile</label>
                                    <input value="{{ $mobile }}" type="tel" id="guest-mobile" name="guest-mobile" class="form-control" />
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="guest-address">Address</label>
                                    <input value="{{ $address }}" type="text" id="guest-address" name="guest-address" class="form-control" />
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="guest-postal-code">Postal Code</label>
                                    <input value="{{ $postalCode }}" type="text" id="guest-postal-code" name="guest-postal-code" class="form-control" />
                                </div>

                            </div> <!-- Row End -->

                            <div class="row m--margin-top-20">

                                <div class="col-md-2">
                                    <label class="label" for="guest-country">Country</label>
                                    <input value="{{ $country }}" type="text" id="guest-country" name="guest-country" class="form-control" />
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="guest-city">City</label>
                                    <input value="{{ $city }}" type="text" id="guest-city" name="guest-city" class="form-control" />
                                </div>

                                <div class="col-md-8" id="guest-comments-div">
                                    <label class="label" for="guest-comments">Guest Comments</label>
                                    <input type="text" id="guest-comments" name="guest-comments" class="form-control" />

                                </div>

                                <div class="col-md-2" id="non-refundable-div" style="display: none;">
                                    <label class="label" for="non-refundable">Booking Policy</label>
                                    <select id="non-refundable" name="non-refundable" class="form-control" >
                                        <option value="0" selected>Select Policy</option>
                                        <option value="1">Non-Refundable</option>
                                    </select>
                                </div>

                            </div> <!-- Row End -->

                            <div class="row m--margin-top-20">

                                <div class="col-md-2">
                                    <label class="label" for="good-card">Good Card</label>
                                    <select id="good-card" name="good-card" class="form-control" onchange="useCardGood(this)">
                                        <option selected disabled value="-1">Select Card</option>
                                        <option value="-2" disabled> ---- Stripe ---- </option>
                                        <option value="4242424242424242">VISA</option>
                                        <option value="4000056655665556">VISA (debit)</option>
                                        <option value="5555555555554444">Mastercard</option>
                                        <option value="2223003122003222">Mastercard 2 Series</option>
                                        <option value="5200828282828210">Mastercard (debit)</option>
                                        <option value="5105105105105100">Mastercard (prepaid)</option>
                                        <option value="378282246310005">American Express 1</option>
                                        <option value="371449635398431">American Express 2</option>
                                        <option value="6011111111111117">Discover 1</option>
                                        <option value="6011000990139424">Discover 2</option>
                                        <option value="30569309025904">Diners Club 1</option>
                                        <option value="38520000023237">Diners Club 2</option>
                                        <option value="3566002020360505">JCB</option>
                                        <option value="6200000000000005">UnionPay</option>
                                        <option value="-3" disabled> ---- CA Gateway ---- </option>
                                        <option value="4111111111111111">Visa</option>
                                        <option value="4444333322221111455">Visa (19-digit)</option>
                                        <option value="5555555555554444">MasterCard</option>
                                        <option value="2223003122003222">MasterCard (2-series bin)</option>
                                        <option value="378282246310005">American Express</option>
                                        <option value="6011111111111117">Discover</option>
                                        <option value="30569309025904">Diners Club</option>
                                        <option value="3569990010030400">JCB</option>
                                        <option value="5019717010103742">Dankort</option>
                                        <option value="6759000000000000005">Maestro</option>
                                        <option value="5062280000000002">Carnet</option>
                                        <option value="4556761029983886">3D Secure Enrolled</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="bad-card">Bad Card</label>
                                    <select id="bad-card" name="bad-card" class="form-control" onchange="useCardBad(this)">
                                        <option selected disabled value="-1">Select Card</option>
                                        <option value="-1" disabled>------------------- Stripe Decline and Special Case --------------------- </option>

                                        {{--<option value="-2" disabled>------- Stripe 3D Secure test card numbers -- </option>
                                        <option value="4000000000003063">required => 3D Secure authentication must be completed for the payment to be successful.</option>
                                        <option value="4000000000003089">recommended => 3D Secure is supported and recommended but not required on this card. Payments succeed whether 3D Secure is used or not. </option>
                                        <option value="4000000000003055">optional => 3D Secure is supported but not required on this card. 3D Secure authentication may still be performed, but is not required. Payments succeed whether 3D Secure is used or not. </option>
                                        <option value="4242424242424242">optional => 3D Secure is supported for this card, but this card is not enrolled in 3D Secure. This means that if 3D Secure is invoked, the customer is not asked to authenticate. Payments succeed whether 3D Secure is invoked or not.</option>
                                        <option value="378282246310005">not_supported => 3D Secure is not supported on this card and cannot be invoked. </option>--}}

                                        <option value="-3" disabled>------- Stripe 3DS New Test Data -- </option>
                                        <option value="4000002500003155">Required on setup or first transaction => This test card requires authentication for one-time payments. However, if you set up this card using the Setup Intents API and use the saved card for subsequent payments, no further authentication is needed.</option>
                                        <option value="4000002760003184">Required => This test card requires authentication on all transactions.</option>
                                        <option value="4000008260003178">Required => This test card requires authentication, but payments will be declined with an insufficient_funds failure code after successful authentication.</option>
                                        <option value="4000000000003055">Supported => This test card supports authentication via 3D Secure 2, but does not require it. Payments using this card do not require additional authentication in test mode unless your test mode Radar rules request authentication.</option>

                                        <option value="-4" disabled>------- Stripe Testing for specific responses and errors -- </option>
                                        <option value="4000000000000077">Charge succeeds and funds will be added directly to your available balance (bypassing your pending balance).</option>
                                        <option value="4000000000000093">Charge succeeds and domestic pricing is used (other test cards use international pricing). This card is only significant in countries with split pricing.</option>
                                        <option value="4000000000000010">The address_line1_check and address_zip_check verifications fail. If your account is blocking payments that fail ZIP code validation, the charge is declined.</option>
                                        <option value="4000000000000028">Charge succeeds but the address_line1_check verification fails.</option>
                                        <option value="4000000000000036">The address_zip_check verification fails. If your account is blocking payments that fail ZIP code validation, the charge is declined.</option>
                                        <option value="4000000000000044">Charge succeeds but the address_zip_check and address_line1_check verifications are both unavailable.</option>
                                        <option value="4000000000005126">Charge succeeds but refunding a captured charge fails with a failure_reason of expired_or_canceled_card.</option>
                                        <option value="4000000000000101">If a CVC number is provided, the cvc_check fails. If your account is blocking payments that fail CVC code validation, the charge is declined.</option>
                                        <option value="4000000000000341">Attaching this card to a Customer object succeeds, but attempts to charge the customer fail.</option>
                                        <option value="4000000000009235">Results in a charge with a risk_level of elevated.</option>
                                        <option value="4000000000004954">Results in a charge with a risk_level of highest.</option>
                                        <option value="4100000000000019">Results in a charge with a risk_level of highest. The charge is blocked as it's considered fraudulent.</option>
                                        <option value="4000000000000002">Charge is declined with a card_declined code.</option>
                                        <option value="4000000000009995">Charge is declined with a card_declined code. The decline_code attribute is insufficient_funds.</option>
                                        <option value="4000000000009987">Charge is declined with a card_declined code. The decline_code attribute is lost_card.</option>
                                        <option value="4000000000009979">Charge is declined with a card_declined code. The decline_code attribute is stolen_card.</option>
                                        <option value="4000000000000069">Charge is declined with an expired_card code.</option>
                                        <option value="4000000000000127">Charge is declined with an incorrect_cvc code.</option>
                                        <option value="4000000000000119">Charge is declined with a processing_error code.</option>
                                        <option value="4242424242424241">Charge is declined with an incorrect_number code as the card number fails the Luhn check.</option>

                                        <option value="-5" disabled> ----------------- CA Gateway -------------- </option>
                                        <option value="4012888888881881">Visa</option>
                                        <option value="4917610000000000003">Visa (19-digit)</option>
                                        <option value="5105105105105100">MasterCard</option>
                                        <option value="2720992720992729">MasterCard (2-series bin)</option>
                                        <option value="371449635398431">American Express</option>
                                        <option value="6011000990139424">Discover</option>
                                        <option value="30207712915383">Diners Club</option>
                                        <option value="3528327757705979">JCB</option>
                                        <option value="5019994000124034">Dankort</option>
                                        <option value="6799990100000000019">Maestro</option>
                                        <option value="6393889871239875">Carnet</option>
                                        <option value="4024007101934890">3D Secure Enrolled</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="label" for="guest-card">Card</label>
                                    <input type="text" id="guest-card" name="guest-card" class="form-control" />
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="card-expiry">Expiry</label>
                                    <input type="text" id="card-expiry" name="card-expiry" class="form-control" />
                                </div>

                                <div class="col-md-2">
                                    <label class="label" for="card-cvv">CVV</label>
                                    <input type="text" id="card-cvv" name="card-cvv" class="form-control" />
                                </div>

                                <div class="col-md-1">
                                    <label class="label" for="booking-submit">&nbsp;</label>
                                    <br>
                                    <input type="submit" id="booking-submit" name="submit" value="Save" class="btn btn-primary" />
                                </div>

                            </div>

                        </form>

                    </div>

                </div>
            </div>
        </div>


    </div>

    <div class="modal fade" id="m_modal_5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="assignto" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create Booking Response</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row" id="response-true" style="display: none;">
                        <div class="col-md-6 text-center">
                            <h3>Booking ID</h3>
                            <p id="response-booking-id" style="font-size: 4em; color: blueviolet;"></p>
                        </div>
                        <div class="col-md-6 text-center">
                            <h3>Booking Message</h3>
                            <p id="response-booking-message" style="font-size: 2em; color: green;"></p>
                        </div>
                    </div>
                    <div class="row" id="response-false" style="display: none;">
                        <div class="col-md-12 text-center">
                            <p id="response-error" class="alert alert-danger"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('ajax_script')

    <script>

        let userAccounts = JSON.parse('<?php echo json_encode($userAccounts, JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP); ?>');
        let emails = JSON.parse('<?php echo json_encode($emails); ?>');
        let cardTypeAll = '<option value="CC" selected>Credit/Debit Card</option><option value="VC">Virtual Card</option><option value="BT">Bank Transfer</option>';
        let cardTypeVC = '<option value="VC" selected>Virtual Card</option>';
        let roomContainer = null;
        let bookingType = 'single';
        let roomCount = 0;

        $('document').ready(function () {

            roomContainer = $("#room-contatiner");
            $("#single-booking").prop('checked', true);


            var accountsHtml = '<option value="-1" selected disabled="">Select Account</option>';
            for(i = 0; i < userAccounts.length; i++) {
                accountsHtml += '<option value="'+userAccounts[i].id+'">'+userAccounts[i].id+' -- '+userAccounts[i].name+'</option>'
            }
            $("#select-account").html(accountsHtml);

            autocomplete(document.getElementById("guest-email"), emails);

            let showModal = '{{ key_exists('status', $bookingResponse) ?  1 : 0 }}';
            let message = "";
            let bookingId = "";
            if(showModal === '1') {

                var responseStatus = '';

                @if(key_exists('status', $bookingResponse))
                    responseStatus  = '{{ $bookingResponse['status'] === "1" ?  1 : 0 }}';
                @endif

                        @if(key_exists('status', $bookingResponse) && $bookingResponse['status'] == "1")
                    bookingId = '{{ $bookingResponse['bookingId'] }}';
                message = '{{ $bookingResponse['message'] }}';
                @endif

                        @if(key_exists('status', $bookingResponse) && $bookingResponse['status'] == "0")
                    message = '{{ $bookingResponse['error'] }}';
                @endif

                if(responseStatus === '1') {
                    $("#response-booking-id").html(bookingId);
                    $("#response-booking-message").html(message);
                    $("#response-true").show();
                    $("#m_modal_5").modal('show');

                } else if(responseStatus === '0') {
                    $("#response-error").html(message);
                    $("#response-false").show();
                    $("#m_modal_5").modal('show');
                }
            }

            $(".booking-date").datepicker(
                {
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    changeMonth: true,
                    changeYear: true,
                    orientation: "bottom",
                    todayHighlight: true,
                    zIndexOffset: 100
                }
            );

            $("#create-test-booking-form").submit(function(e) {

                if($("#select-account").find(":selected").val() == -1) {
                    e.preventDefault();
                    alert("Please select Account");
                    return
                }
                if($("#select-property").find(":selected").val() == -1) {
                    e.preventDefault();
                    alert("Please select Property");
                    return;
                }
                if($("#select-room").find(":selected").val() == -1) {
                    e.preventDefault();
                    alert("Please select Room");
                    return;
                }
                if($("#select-booking-source").find(":selected").val() == -1) {
                    e.preventDefault();
                    alert("Please select Booking Source");
                    return;
                }

                if(bookingType === 'group' && roomCount <= 1) {
                    e.preventDefault();
                    alert("Please select multiple rooms");
                    return;
                }

                // #check-in-date
                // #check-out-date

            });

        });

        function userAccountSelected(id) {
            axios({
                url : '/admin/get-user-properties/' + id,
                method : 'GET'
            })
            .then(function (response) {
                var propertiesHtml = '<option value="-1" selected disabled="">Select Property</option>';
                if(response.data.status_code==200){
                    response.data.data.forEach(function(val, ind){
                        propertiesHtml += '<option value="'+val.id+'">'+val.name+'</option>';
                    });

                } else {
                    var propertiesHtml = '<option value="-1" selected disabled="">Property not found</option>';
                }
                $("#select-property").html(propertiesHtml);
                $("#select-room").html('<option value="-1" selected disabled="">Select Room</option>');
            })
            .catch(function (error) {
                console.log(error);
            });

        }

        function propertySelected(id) {
            var roomsHtml = '<option value="-1" selected disabled="">Select Room</option>';
            axios({
                url : '/admin/get-property-rooms/' + id,
                method : 'GET'
            })
                .then(function (response) {
                    console.error(response);
                    var roomsHtml = '<option value="-1" selected disabled="">Select Room</option>';
                    if(response.data.status_code==200){
                        response.data.data.forEach(function(val, ind){
                            roomsHtml += '<option value="'+val.id+'">'+val.name+'</option>';
                        });

                    } else {
                        var roomsHtml = '<option value="-1" selected disabled="">Room not found</option>';
                    }
                    $("#select-room").html(roomsHtml);
                })
                .catch(function (error) {
                    console.log(error);
                });

        }

        function bookingSourceSelected(option) {

            if(option.value === '17' || option.value === '53') {
                $("#card-type").html(cardTypeVC);
            } else {
                $("#card-type").html(cardTypeAll);
            }

            var cardType = $("#card-type").val();
            var checkInDate = $("#check-in-date").val();
            var guestComment = $("#guest-comments");

            if(option.value === '19') {
                if(cardType === 'VC') {
                    var comment = 'You may charge it as of ' + checkInDate + ".";
                    guestComment.val(comment);
                    return true;
                }

                $("#guest-comments-div").attr('class','col-md-6');
                $('#non-refundable-div').show();

            }else {
                $("#guest-comments-div").attr('class','col-md-8');
                $('#non-refundable-div').hide();
            }
            guestComment.val('');
        }

        function cardTypeSelected(option) {

            var bookingSource = $("#select-booking-source").val();
            var checkInDate = $("#check-in-date").val();
            var guestComment = $("#guest-comments");

            if(bookingSource === '19') {
                if(option.value === 'VC') {
                    var comment = 'You may charge it as of ' + checkInDate + ".";
                    guestComment.val(comment);
                    return true;
                }
            }

            guestComment.val('');
        }

        function checkInDateChange(input) {

            var bookingSource = $("#select-booking-source").val();
            var cardType = $("#card-type").val();
            var guestComment = $("#guest-comments");
            var checkInDate = $("#check-in-date").val();

            var d = new Date(checkInDate);
            time = moment(d).add(2,'days');
            $("#check-out-date").val(time.format('YYYY-MM-DD'));

            if(bookingSource === '19') {
                if(cardType === 'VC') {
                    var comment = 'You may charge it as of ' + checkInDate + ".";
                    guestComment.val(comment);
                    return true;
                }
            }

            guestComment.val('');
        }

        function useCardGood(option) {
            $("#guest-card").val(option.value);
            $("#card-expiry").val("12/22");
            $("#card-cvv").val("123");
            $("#bad-card").val(-1);
        }

        function useCardBad(option) {
            $("#guest-card").val(option.value);
            $("#card-expiry").val("12/22");
            $("#card-cvv").val("123");
            $("#good-card").val(-1);
        }

        function autocomplete(inp, arr) {
            /*the autocomplete function takes two arguments,
            the text field element and an array of possible autocompleted values:*/
            var currentFocus;
            /*execute a function when someone writes in the text field:*/
            inp.addEventListener("input", function(e) {
                var a, b, i, val = this.value;
                /*close any already open lists of autocompleted values*/
                closeAllLists();
                if (!val) { return false;}
                currentFocus = -1;
                /*create a DIV element that will contain the items (values):*/
                a = document.createElement("DIV");
                a.setAttribute("id", this.id + "autocomplete-list");
                a.setAttribute("class", "autocomplete-items");
                /*append the DIV element as a child of the autocomplete container:*/
                this.parentNode.appendChild(a);
                /*for each item in the array...*/
                for (i = 0; i < arr.length; i++) {
                    /*check if the item starts with the same letters as the text field value:*/
                    if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                        /*create a DIV element for each matching element:*/
                        b = document.createElement("DIV");
                        /*make the matching letters bold:*/
                        b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                        b.innerHTML += arr[i].substr(val.length);
                        /*insert a input field that will hold the current array item's value:*/
                        b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                        /*execute a function when someone clicks on the item value (DIV element):*/
                        b.addEventListener("click", function(e) {
                            /*insert the value for the autocomplete text field:*/
                            inp.value = this.getElementsByTagName("input")[0].value;
                            /*close the list of autocompleted values,
                            (or any other open lists of autocompleted values:*/
                            closeAllLists();
                        });
                        a.appendChild(b);
                    }
                }
            });
            /*execute a function presses a key on the keyboard:*/
            inp.addEventListener("keydown", function(e) {
                var x = document.getElementById(this.id + "autocomplete-list");
                if (x) x = x.getElementsByTagName("div");
                if (e.keyCode == 40) {
                    /*If the arrow DOWN key is pressed,
                    increase the currentFocus variable:*/
                    currentFocus++;
                    /*and and make the current item more visible:*/
                    addActive(x);
                } else if (e.keyCode == 38) { //up
                    /*If the arrow UP key is pressed,
                    decrease the currentFocus variable:*/
                    currentFocus--;
                    /*and and make the current item more visible:*/
                    addActive(x);
                } else if (e.keyCode == 13) {
                    /*If the ENTER key is pressed, prevent the form from being submitted,*/
                    e.preventDefault();
                    if (currentFocus > -1) {
                        /*and simulate a click on the "active" item:*/
                        if (x) x[currentFocus].click();
                    }
                }
            });
            function addActive(x) {
                /*a function to classify an item as "active":*/
                if (!x) return false;
                /*start by removing the "active" class on all items:*/
                removeActive(x);
                if (currentFocus >= x.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (x.length - 1);
                /*add class "autocomplete-active":*/
                x[currentFocus].classList.add("autocomplete-active");
            }
            function removeActive(x) {
                /*a function to remove the "active" class from all autocomplete items:*/
                for (var i = 0; i < x.length; i++) {
                    x[i].classList.remove("autocomplete-active");
                }
            }
            function closeAllLists(elmnt) {
                /*close all autocomplete lists in the document,
                except the one passed as an argument:*/
                var x = document.getElementsByClassName("autocomplete-items");
                for (var i = 0; i < x.length; i++) {
                    if (elmnt != x[i] && elmnt != inp) {
                        x[i].parentNode.removeChild(x[i]);
                    }
                }
            }
            /*execute a function when someone clicks in the document:*/
            document.addEventListener("click", function (e) {
                closeAllLists(e.target);
            });
        }

        function getRoomBadge(text, val) {
            return  '<span id="room-'+val+'" class="badge-room">'+text+' ' +
                        '<a href="javascript:" onclick="removeRoom(\''+val+'\')"><i class="fa fa-trash"></i></a>' +
                        '<input type="hidden" name="roomGroup[]" value="'+val+'" id="room-i-'+val+'"/>' +
                    '</span>';
        }

        function removeRoom(id) {
            $("#room-" + id).remove();
            roomCount--;
        }

        function setType(type) {
            bookingType = type;
            if(bookingType === 'single')
                roomContainer.empty();
        }

        function roomSelected(sel) {

            if(bookingType === 'group') {
                if($("#room-i-"+sel.value).length === 0) {
                    let rText = sel.options[sel.selectedIndex].text;
                    let oHtml = getRoomBadge(rText, sel.value);
                    roomContainer.append(oHtml);
                    roomCount++;
                }
            }
        }

    </script>

@endsection