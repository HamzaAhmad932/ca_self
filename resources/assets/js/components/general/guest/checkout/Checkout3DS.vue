<template>
    <div id="checkout">

        <form id="payment-form">
            <div class="gp-page gp-min" style="padding-top: 65px !important">
                <div class="gp-title mb-2">
                    <h1 class="page-title">Welcome {{guestName}}</h1>
                </div>
                <div class="gp-box">
                    <div class="gp-box-content">
                        <div v-if="isPaid != '1'">
                            <div class="gp-inset">

                                <div class="form-section-title">
                                    <h4>Card information</h4>
                                    <div class="card-type-list">
                                        <img alt="" src="/v2/img/mastercard-icon.png">
                                        <img alt="" src="/v2/img/visa-icon.png">
                                        <img alt="" src="/v2/img/amex-icon.png">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="first_name">First Name:</label>
                                    <input class="form-control" id="first_name" name="first_name" placeholder="First Name"
                                           required type="text" v-model="data.fName">
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name:</label>
                                    <input class="form-control" id="last_name" name="last_name" placeholder="Last Name"
                                           required type="text" v-model="data.lName">
                                </div>

                                <div class="form-group">
                                    <label for="email">Email:</label>
                                    <input class="form-control" id="email" name="email" placeholder="Email"
                                           required type="email" v-model="data.email">
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone:</label>
                                    <input class="form-control" id="phone" name="phone" placeholder="Phone" required
                                           type="tel" v-model="data.phone">
                                </div>

                                <div class="form-group">
                                    <label for="line1">Address Line 1:</label>
                                    <input class="form-control" id="line1" name="line1" placeholder="Address 1" required type="text" v-model="data.address_line1">
                                </div>

                                <div class="form-group">
                                    <label for="line2">Address Line 2:</label>
                                    <input class="form-control" id="line2" name="line2" placeholder="Address 2" type="text" v-model="data.address_line2">
                                </div>

                                <div class="form-group">
                                    <label for="postal_code">Postal Code / Zip Code:</label>
                                    <input class="form-control" id="postal_code" name="postal_code" placeholder="Postal Code" required type="text" v-model="data.postal_code">
                                </div>

                                <div class="form-group">
                                    <label for="country">Country:</label>
                                    <select class="form-control" id="country" name="country" required v-model="data.country">
                                        <option value="-1" disabled>Select Your Country</option><option value="BD">Bangladesh</option><option value="BE">Belgium</option><option value="BF">Burkina Faso</option><option value="BG">Bulgaria</option><option value="BA">Bosnia and Herzegovina</option><option value="BB">Barbados</option><option value="WF">Wallis and Futuna</option><option value="BL">Saint Barthelemy</option><option value="BM">Bermuda</option><option value="BN">Brunei</option><option value="BO">Bolivia</option><option value="BH">Bahrain</option><option value="BI">Burundi</option><option value="BJ">Benin</option><option value="BT">Bhutan</option><option value="JM">Jamaica</option><option value="BV">Bouvet Island</option><option value="BW">Botswana</option><option value="WS">Samoa</option><option value="BQ">Bonaire, Saint Eustatius and Saba </option><option value="BR">Brazil</option><option value="BS">Bahamas</option><option value="JE">Jersey</option><option value="BY">Belarus</option><option value="BZ">Belize</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="RS">Serbia</option><option value="TL">East Timor</option><option value="RE">Reunion</option><option value="TM">Turkmenistan</option><option value="TJ">Tajikistan</option><option value="RO">Romania</option><option value="TK">Tokelau</option><option value="GW">Guinea-Bissau</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GS">South Georgia and the South Sandwich Islands</option><option value="GR">Greece</option><option value="GQ">Equatorial Guinea</option><option value="GP">Guadeloupe</option><option value="JP">Japan</option><option value="GY">Guyana</option><option value="GG">Guernsey</option><option value="GF">French Guiana</option><option value="GE">Georgia</option><option value="GD">Grenada</option><option value="GB">United Kingdom</option><option value="GA">Gabon</option><option value="SV">El Salvador</option><option value="GN">Guinea</option><option value="GM">Gambia</option><option value="GL">Greenland</option><option value="GI">Gibraltar</option><option value="GH">Ghana</option><option value="OM">Oman</option><option value="TN">Tunisia</option><option value="JO">Jordan</option><option value="HR">Croatia</option><option value="HT">Haiti</option><option value="HU">Hungary</option><option value="HK">Hong Kong</option><option value="HN">Honduras</option><option value="HM">Heard Island and McDonald Islands</option><option value="VE">Venezuela</option><option value="PR">Puerto Rico</option><option value="PS">Palestinian Territory</option><option value="PW">Palau</option><option value="PT">Portugal</option><option value="SJ">Svalbard and Jan Mayen</option><option value="PY">Paraguay</option><option value="IQ">Iraq</option><option value="PA">Panama</option><option value="PF">French Polynesia</option><option value="PG">Papua New Guinea</option><option value="PE">Peru</option><option value="PK">Pakistan</option><option value="PH">Philippines</option><option value="PN">Pitcairn</option><option value="PL">Poland</option><option value="PM">Saint Pierre and Miquelon</option><option value="ZM">Zambia</option><option value="EH">Western Sahara</option><option value="EE">Estonia</option><option value="EG">Egypt</option><option value="ZA">South Africa</option><option value="EC">Ecuador</option><option value="IT">Italy</option><option value="VN">Vietnam</option><option value="SB">Solomon Islands</option><option value="ET">Ethiopia</option><option value="SO">Somalia</option><option value="ZW">Zimbabwe</option><option value="SA">Saudi Arabia</option><option value="ES">Spain</option><option value="ER">Eritrea</option><option value="ME">Montenegro</option><option value="MD">Moldova</option><option value="MG">Madagascar</option><option value="MF">Saint Martin</option><option value="MA">Morocco</option><option value="MC">Monaco</option><option value="UZ">Uzbekistan</option><option value="MM">Myanmar</option><option value="ML">Mali</option><option value="MO">Macao</option><option value="MN">Mongolia</option><option value="MH">Marshall Islands</option><option value="MK">Macedonia</option><option value="MU">Mauritius</option><option value="MT">Malta</option><option value="MW">Malawi</option><option value="MV">Maldives</option><option value="MQ">Martinique</option><option value="MP">Northern Mariana Islands</option><option value="MS">Montserrat</option><option value="MR">Mauritania</option><option value="IM">Isle of Man</option><option value="UG">Uganda</option><option value="TZ">Tanzania</option><option value="MY">Malaysia</option><option value="MX">Mexico</option><option value="IL">Israel</option><option value="FR">France</option><option value="IO">British Indian Ocean Territory</option><option value="SH">Saint Helena</option><option value="FI">Finland</option><option value="FJ">Fiji</option><option value="FK">Falkland Islands</option><option value="FM">Micronesia</option><option value="FO">Faroe Islands</option><option value="NI">Nicaragua</option><option value="NL">Netherlands</option><option value="NO">Norway</option><option value="NA">Namibia</option><option value="VU">Vanuatu</option><option value="NC">New Caledonia</option><option value="NE">Niger</option><option value="NF">Norfolk Island</option><option value="NG">Nigeria</option><option value="NZ">New Zealand</option><option value="NP">Nepal</option><option value="NR">Nauru</option><option value="NU">Niue</option><option value="CK">Cook Islands</option><option value="XK">Kosovo</option><option value="CI">Ivory Coast</option><option value="CH">Switzerland</option><option value="CO">Colombia</option><option value="CN">China</option><option value="CM">Cameroon</option><option value="CL">Chile</option><option value="CC">Cocos Islands</option><option value="CA">Canada</option><option value="CG">Republic of the Congo</option><option value="CF">Central African Republic</option><option value="CD">Democratic Republic of the Congo</option><option value="CZ">Czech Republic</option><option value="CY">Cyprus</option><option value="CX">Christmas Island</option><option value="CR">Costa Rica</option><option value="CW">Curacao</option><option value="CV">Cape Verde</option><option value="CU">Cuba</option><option value="SZ">Swaziland</option><option value="SY">Syria</option><option value="SX">Sint Maarten</option><option value="KG">Kyrgyzstan</option><option value="KE">Kenya</option><option value="SS">South Sudan</option><option value="SR">Suriname</option><option value="KI">Kiribati</option><option value="KH">Cambodia</option><option value="KN">Saint Kitts and Nevis</option><option value="KM">Comoros</option><option value="ST">Sao Tome and Principe</option><option value="SK">Slovakia</option><option value="KR">South Korea</option><option value="SI">Slovenia</option><option value="KP">North Korea</option><option value="KW">Kuwait</option><option value="SN">Senegal</option><option value="SM">San Marino</option><option value="SL">Sierra Leone</option><option value="SC">Seychelles</option><option value="KZ">Kazakhstan</option><option value="KY">Cayman Islands</option><option value="SG">Singapore</option><option value="SE">Sweden</option><option value="SD">Sudan</option><option value="DO">Dominican Republic</option><option value="DM">Dominica</option><option value="DJ">Djibouti</option><option value="DK">Denmark</option><option value="VG">British Virgin Islands</option><option value="DE">Germany</option><option value="YE">Yemen</option><option value="DZ">Algeria</option><option value="US">United States</option><option value="UY">Uruguay</option><option value="YT">Mayotte</option><option value="UM">United States Minor Outlying Islands</option><option value="LB">Lebanon</option><option value="LC">Saint Lucia</option><option value="LA">Laos</option><option value="TV">Tuvalu</option><option value="TW">Taiwan</option><option value="TT">Trinidad and Tobago</option><option value="TR">Turkey</option><option value="LK">Sri Lanka</option><option value="LI">Liechtenstein</option><option value="LV">Latvia</option><option value="TO">Tonga</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="LR">Liberia</option><option value="LS">Lesotho</option><option value="TH">Thailand</option><option value="TF">French Southern Territories</option><option value="TG">Togo</option><option value="TD">Chad</option><option value="TC">Turks and Caicos Islands</option><option value="LY">Libya</option><option value="VA">Vatican</option><option value="VC">Saint Vincent and the Grenadines</option><option value="AE">United Arab Emirates</option><option value="AD">Andorra</option><option value="AG">Antigua and Barbuda</option><option value="AF">Afghanistan</option><option value="AI">Anguilla</option><option value="VI">U.S. Virgin Islands</option><option value="IS">Iceland</option><option value="IR">Iran</option><option value="AM">Armenia</option><option value="AL">Albania</option><option value="AO">Angola</option><option value="AQ">Antarctica</option><option value="AS">American Samoa</option><option value="AR">Argentina</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AW">Aruba</option><option value="IN">India</option><option value="AX">Aland Islands</option><option value="AZ">Azerbaijan</option><option value="IE">Ireland</option><option value="ID">Indonesia</option><option value="UA">Ukraine</option><option value="QA">Qatar</option><option value="MZ">Mozambique</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="state">State:</label>
                                    <input class="form-control" id="state" name="state" placeholder="State" type="text" v-model="data.state">
                                </div>

                                <div class="form-group">
                                    <label for="city">City:</label>
                                    <input class="form-control" id="city" name="city" placeholder="City" required type="text" v-model="data.city">
                                </div>

                                <div class="form-group">
                                    <label for="card-element">Credit/Debit Card:</label>
                                    <div id="card-element"></div>
                                    <div id="card-errors" role="alert" style="color: darkred;"></div>
                                </div>

                                <p class="text-center mt-4">
                                    <img alt="Powered By Stripe" height="30" src="/v2/img/powered_by_stripe.png">
                                </p>

                                <div class="text-center mt-4">

                                    <a @click.prevent="clearForm()" class="btn btn-warning" href="javascript:">Clear
                                        Form <i class="fa fa-trash" style="color: white;"></i></a>

                                    <a v-if="!precheckin" :href="guestPortalLink" class="btn btn-primary">Guest Portal</a>

                                </div>
                                <div class="text-center mt-4">
                                    <button :data-secret="clientSecret" class="btn btn-success btn-confirm"
                                            id="card-button">{{ buttonText }} <i class="fa fa-money-check"></i></button>
                                    <br>
                                    <br>
                                </div>

                            </div>
                        </div>
                        <div v-else>
                            <div class="form-section-title">
                                <h4>Already Paid</h4>
                            </div>
                            <div class="text-center mt-4">
                                <a v-if="!precheckin" :href="guestPortalLink" class="btn btn-primary">Guest Portal</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gp-divider"></div>
            </div>
        </form>

    </div>

</template>

<script>
    export default {

        props: [
            'guestName', 'isPaid', 'fName', 'lName', 'email', 'phone', 'guestPortalLink', 'clientSecret', 'buttonText',
            'publicKey', 'accountId', 'bInfo', 'id', 'type', 'checkoutPostUrl', 'precheckin', 'postal_code', 'country',
            'address_line1', 'city', 'state'],

        data() {
            return {
                data: {
                    fName: this.fName,
                    lName: this.lName,
                    email: this.email,
                    phone: this.phone,
                    address_line2: '',
                    address_line1: this.address_line1,
                    country: this.country,
                    city: this.city,
                    postal_code: this.postal_code,
                    state: this.state
                },
                sLoader: null,
                sLoaderSuccess: null,
                stripe: null,
                elements: null,
                style: {
                    base: {
                        color: '#32325d',
                        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {color: '#aab7c4'}
                    },
                    invalid: {color: '#fa755a', iconColor: '#fa755a'}
                },
                card: null,
                form: null
            };
        },

        computed: {},
        created() {

            this.sLoader = swal.mixin({
                title: 'Please wait',
                text: 'Updating Payment status',
                imageUrl: '/images/throbber.gif',
                showConfirmButton: false,
                backdrop: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
            });

        },
        mounted() {
            
            if(this.isPaid === '1')
                return;

            let checkout = this;
            this.stripe = Stripe(this.publicKey, {stripeAccount: this.accountId});
            this.elements = this.stripe.elements();

            this.card = this.elements.create('card', {style: this.style});

            this.card.mount('#card-element');

            this.form = document.getElementById('payment-form');

            this.card.addEventListener('change', function (event) {
                let displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            this.form.addEventListener('submit', function (event) {

                event.preventDefault();

                let dataToSend = {
                    payment_method: {
                        card: checkout.card,
                        billing_details: {
                            name: checkout.data.fName + " " + checkout.data.lName,
                            email: checkout.data.email,
                            phone: checkout.data.phone,
                            address: {
                                city: checkout.data.city,
                                country: checkout.data.country,
                                line1: checkout.data.address_line1,
                                postal_code: checkout.data.postal_code,
                            }
                        }
                    }
                };



                if(checkout.data.address_line2 !== '') {
                    dataToSend.payment_method.billing_details.address.line2 = checkout.data.address_line2;
                }

                if(checkout.data.state !== '') {
                    dataToSend.payment_method.billing_details.address.state = checkout.data.state;
                }

                checkout.stripe.confirmCardPayment(checkout.clientSecret, dataToSend).then(function (result) {

                    if (result.error) {
                        // Display error
                        let errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        
                        if(result.error.code === 'setup_intent_unexpected_state') {
                            toastr.error("After page reload automatically try again. If problem persists please contact support.");
                            
                            setTimeout(function() {
                                location.reload(true);
                            }, 3000);
                        }

                    } else {
                        // Setup has succeeded
                        // console.log(result);
                        checkout.stripePaymentMethodHandler(result.paymentIntent.payment_method, result.paymentIntent.id, result.paymentIntent.client_secret);
                    }
                });

            }, true);


            if(this.precheckin){

                this.sLoaderSuccess = swal.mixin({
                    title: 'Payment Successful',
                    type: null,
                    showConfirmButton: false,
                    confirmButtonText: 'OK',
                    backdrop: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    timer: 10000,
                    text: ' ',
                    html: '<span style="font-size: 10px;">Redirecting to next step in <strong></strong></span>',
                    onBeforeOpen: () => {
                        checkout.sLoaderSuccess = setInterval(() => {
                            swal.getContent().querySelector('strong').textContent = parseInt(swal.getTimerLeft() / 1000)
                        }, 1000)
                    },
                    onClose: () => {
                        clearInterval(checkout.sLoaderSuccess)
                    }
                });
            }else{
                this.sLoaderSuccess = swal.mixin({
                    title: 'Payment Successful',
                    type: null,
                    showConfirmButton: true,
                    confirmButtonText: 'Visit Guest Portal',
                    backdrop: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    timer: 10000,
                    text: ' ',
                    html: '<span style="font-size: 10px;">Redirecting to guest-portal in <strong></strong></span>',
                    onBeforeOpen: () => {
                        checkout.sLoaderSuccess = setInterval(() => {
                            swal.getContent().querySelector('strong').textContent = parseInt(swal.getTimerLeft() / 1000)
                        }, 1000)
                    },
                    onClose: () => {
                        clearInterval(checkout.sLoaderSuccess)
                    }
                });
            }

        },

        methods: {

            clearForm() {

                this.data.fName = '';
                this.data.lName = '';
                this.data.email = '';
                this.data.phone = '';
                this.data.city = '';
                this.data.country = '-1';
                this.data.address_line1 = '';
                this.data.address_line2 = '';
                this.data.state = '';
                this.data.postal_code = '';
            },

            stripePaymentMethodHandler(paymentMethod, intentId, clientSecret) {

                let checkout = this;

                this.sLoader.fire();

                axios.post(checkout.checkoutPostUrl,
                    {
                        id: checkout.id,
                        type: checkout.type,
                        paymentMethod: paymentMethod,
                        b_info: checkout.bInfo,
                        first_name: checkout.data.fName,
                        last_name: checkout.data.lName,
                        phone: checkout.data.phone,
                        email: checkout.data.email,
                        intentId: intentId,
                        clientSecret: clientSecret

                    }).then(function (response) {

                    checkout.sLoader.close();

                    if (response.data.status === 1) {
                        checkout.sLoaderSuccess.fire().then((result) => {
                            if(checkout.guestPortalLink == ''){
                                $('#force_close_3ds_modal').click();
                            }else{
                                window.location.href = checkout.guestPortalLink;
                            }
                        });
                    }else{
                        toastr.error(response.data.message);
                    }

                }).catch(function (error) {
                    console.log(error);
                    checkout.sLoader.close();
                    toastr.error(error);
                });

            },

        }

    }
</script>