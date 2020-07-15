@extends('admin.stripe_commission_billing.guest.app')
@section('title', 'some title')
@section('page_content')
    <div id="checkout">
        <div id="payment-form">
            <div class="gp-page gp-min" style="padding-top: 65px !important">
                <div class="gp-title mb-2">
                    <h1 class="page-title" style="text-align: center">Billing Card Details for {{$userAccount->name}}</h1>
                </div>
                <div class="gp-box">
                    <div class="gp-box-content">
                        <div class="gp-inset">
                            {{ @csrf_field() }}
                            <div class="form-section-title">
                                <h4>Card information</h4>
                                <div class="card-type-list">
                                    <img src="{{ asset('v2/img/mastercard-icon.png') }}" alt="">
                                    <img src="{{ asset('v2/img/visa-icon.png') }}" alt="">
                                    <img src="{{ asset('v2/img/amex-icon.png') }}" alt="">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="first_name">First Name: </label>
                                <input value="" class="form-control" id="first_name" name="first_name" type="text" placeholder="First Name" required>
                            </div>

                            <div class="form-group">
                                <label for="last_name">Last Name:</label>
                                <input value="" class="form-control" id="last_name" name="last_name" type="text" placeholder="Last Name" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input value="{{$userAccount->email}}" class="form-control" id="email" name="email" type="email" placeholder="Email" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone:</label>
                                <input value="" class="form-control" id="phone" name="phone" type="tel" placeholder="Phone" required>
                            </div>

                            <div class="form-group">
                                <label for="cardNumber">Credit/Debit Card:</label>
                                <div id="card-element"></div>
                                <div id="card-errors" role="alert" style="color: darkred;"></div>
                            </div>

                            <p class="text-center mt-4"><img src="{{ asset('v2/img/powered_by_stripe.png') }}" alt="Powered By Stripe" height="30"></p>

                            <div class="text-center mt-4" style="float: left; margin: 30px">
                                <a class="btn btn-warning" href="javascript:" onclick="clearForm()">Clear Form <i class="fa fa-trash" style="color: white;"></i></a>
                            </div>
                            <div class="text-center mt-4" style="float: left; ">
                                <button class="btn btn-success btn-confirm" id="card-button" data-secret="{{$intent->client_secret}}"> Save Card  <i class="fa fa-money-check"></i></button>
                            </div>
                            </div>


                    </div>
                </div>

                <div class="gp-divider"></div>
            </div>
        </div>

    </div>

@endsection

@push('below_script')
    <script src="https://js.stripe.com/v3/"></script>

    <script>
        var $publish_key = '{{ config(get_billing_config_file_name().'.publish_key') }}';
        var stripe = Stripe($publish_key);

        var elements = stripe.elements();
        var cardElement = elements.create('card');
        cardElement.mount('#card-element');

        //var cardholderName = document.getElementById('cardholder-name');
        var cardButton = document.getElementById('card-button');
        var clientSecret = cardButton.dataset.secret;
        var fName = document.getElementById('first_name');
        var lName = document.getElementById('last_name');
        var email = document.getElementById('email');
        var phone = document.getElementById('phone');

        cardButton.addEventListener('click', function(ev) {
            document.getElementById('card-errors').textContent = '';
            stripe.handleCardSetup(
                clientSecret, cardElement, {
                    payment_method_data: {
                        billing_details: {name: (fName.value+' '+lName.value), email: email.value, phone: phone.value}
                    }
                }
            ).then(function(result) {
                console.log(result);
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    // Display error.message in your UI.
                } else {
                    stripeCustomerCreatHandler(result.setupIntent.payment_method);
                    // The setup has succeeded. Display a success message.
                }
            });
        });
        function  stripeCustomerCreatHandler(paymentMethod) {
            sLoader.fire();
            var $userAccountId = '{{$userAccount->id}}';
            axios.post("/create-stripe-billing-customer", {'paymentMethod' : paymentMethod, 'userAccountId' : $userAccountId}).then(function (response) {
                sLoader.close();
                if(response.data.status){
                    toastr.success(response.data.message);
                    window.location.href = "{{route('assignPlans')}}";
                } else{
                    toastr.error(response.data.message);
                    location.reload();
                }
            }).catch(function (error) {
                    console.log(error);
                    sLoader.close();
                    toastr.error(error);
            });
        }

        function clearForm() {
            fName.value = '';
            lName.value = '';
            email.value = '';
            phone.value = '';
        }

        let sLoader = swal.mixin({
            title: 'Please wait',
            text: 'Updating Billing Details on Stripe',
            imageUrl: '{{ asset('images/throbber.gif') }}',
            showConfirmButton: false,
            backdrop: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
        });

    </script>
    <script>
        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };
    </script>
@endpush