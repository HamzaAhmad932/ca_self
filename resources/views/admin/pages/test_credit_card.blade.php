@extends('layouts.admin')
@section('content')

    <style xmlns="http://www.w3.org/1999/html">

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
        /** jSon butify **/
        pre {
            background-color: #f8f8ff;
            border: 1px solid #C0C0C0;
            padding: 10px 20px;
            margin: 20px;
        }
        .json-key {
            color: red;
            font-weight: bold;
        }
        .json-string {
            color: green;
        }
        .json-number {
            color: darkorange;
        }
        .json-boolean {
            color: blue;
        }
        .json-null {
            color: magenta;
        }
        .json-comma {
            color: red;
        }

    </style>

    <div class="m-grid__item m-grid__item--fluid m-wrapper" id="test_credit_card">

        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">

            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/leftnav.test_credit_card')}}</h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item m-nav__item--home">
                            <a href="#" class="m-nav__link m-nav__link--icon">
                                <i class="m-nav__link-icon la la-home"></i>
                            </a>
                        </li>
                        <li class="m-nav__separator">-</li>

                        <li class="m-nav__item">

                            <span class="m-nav__link-text">{{__('admin/leftnav.test_credit_card')}}</span>

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

                        <form id="test-booking-form">
                            {{ csrf_field() }}

                            <div class="row m--margin-top-20">

                                <div class="col-md-3">
                                    <label for="user_account_id">User Account</label>
                                    <select id="user_account_id" name="user_account_id" class="form-control" v-model="test_credit_card.user_account_id">
                                        <option value="" selected disabled>Select User Account</option>
                                        <option v-for="user_account in user_accounts" :value="user_account.id">@{{ user_account.id +' -- '+user_account.name }}</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="pms_booking_id">PMS Booking ID</label>
                                    <input type="number" name="pms_booking_id" id="pms_booking_id" class="form-control" v-model="test_credit_card.pms_booking_id">
                                </div>

                                <div class="col-md-3">
                                    <label for="credit_card_info_id">Credit Card Info ID</label>
                                    <input type="number" name="credit_card_info_id" id="credit_card_info_id" class="form-control" v-model="test_credit_card.credit_card_info_id">
                                </div>

                                <div class="col-md-3">
                                    <label for="credit_card_token">Credit Card Token</label>
                                    <input type="text" name="credit_card_token" id="credit_card_token" class="form-control" v-model="test_credit_card.credit_card_token">
                                </div>

                            </div>
                            <div class="row m--margin-top-20" style="border-bottom: 1px solid #ebedf2; margin-bottom: 3rem; padding-bottom: 3rem;">
                                <div class="col-md-4 offset-4">
                                    <label for="credit_card_submit">&nbsp;</label>
                                    <br>
                                    <button type="button" id="credit_card_submit" name="submit" @click="getCreditCardSystemUsage" class="btn btn-primary btn-block">Search</button>
                                </div>
                            </div>
                        </form>

                    </div>

                    <div class="m-section__content">
                        <div class="row m--margin-top-20">
                            <div class="col-12 alert alert-danger text-center" v-if="error_message != ''">@{{error_message}}</div>
                            <div class="col-md-12">
                                <h2 class="text-center">Credit Card</h2>
                                <pre v-if="cc_info != ''">
                                    <p v-html="cc_info"></p>
{{--                                    <p>@{{ cc_info }}</p>--}}
                                </pre>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>


@endsection


@section('ajax_script')

    <script>

        var testCreditCard = new Vue({
            el: '#test_credit_card',
            data(){
                return {
                    test_credit_card: {
                        user_account_id: '',
                        pms_booking_id: '',
                        credit_card_info_id: '',
                        credit_card_token: '',
                    },
                    user_accounts: '',
                    numbers: /^[0-9]+$/,
                    cc_info: '',
                    error_message: ''
                }
            },
            methods:{
                getCreditCardSystemUsage(){
                    var self = this;
                    if (this.test_credit_card.pms_booking_id == '' && this.test_credit_card.credit_card_info_id == '' && this.test_credit_card.credit_card_token == '') {
                        alert("Please enter pms booking id or credit card info id or (credit card token and pms booking id)");
                        return;
                    } else if ( this.test_credit_card.pms_booking_id != '' && this.test_credit_card.user_account_id == '') {
                        alert("Please select user account");
                        return;
                    } else if ( this.test_credit_card.credit_card_token != '' && this.test_credit_card.pms_booking_id == '' && this.test_credit_card.user_account_id == '') {
                        alert("Please enter PMS booking id and select user account");
                        return;
                    } else if ( this.test_credit_card.credit_card_token != '' && this.test_credit_card.pms_booking_id != '' && this.test_credit_card.user_account_id == '') {
                        alert("Please select user account");
                        return;
                    } else if ( this.test_credit_card.credit_card_token != '' && this.test_credit_card.pms_booking_id == '' && this.test_credit_card.user_account_id != '') {
                        alert("Please enter PMS booking id");
                        return;
                    }

                    /*if (self.test_credit_card.booking_info_id.match(self.numbers)) {
                        alert('Please input numeric characters only');
                    }*/

                    mApp.block("#test_credit_card",{
                        overlayColor:"#000000",
                        type:"loader",
                        state:"success",
                        message:"Please wait..."
                    });

                    axios.post('{{route("getCreditCardSystemUsage")}}', self.test_credit_card)
                        .then(function (response) {
                            console.log(response.data)
                            if(response.data.status_code == 200){
                                self.cc_info = self.JSONStringify(response.data.data.credit_card);
                                if (typeof response.data.data.actual_response !== "undefined") {
                                    Swal.fire({
                                        title: 'Save Card?',
                                        text: "Do you want to save this card against booking " + self.test_credit_card.pms_booking_id,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Save',
                                        cancelButtonText: 'Cancel',
                                        reverseButtons: true,
                                    }).then((result) => {
                                        if (result.value) {
                                            swal({
                                                title: 'Please Wait..!',
                                                text: 'Is working..',
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                allowEnterKey: false,
                                                onOpen: () => {
                                                    swal.showLoading()
                                                }
                                            });

                                            axios({
                                                url : "{{route('saveNewCreditCard')}}",
                                                method : 'POST',
                                                data: {
                                                    card : response.data.data.credit_card,
                                                    pms_booking_id : self.test_credit_card.pms_booking_id,
                                                    user_account_id : self.test_credit_card.user_account_id
                                                },
                                            }).then(resp => {
                                                swal.hideLoading();
                                                if (resp.data.status_code == 200) {
                                                    Swal.fire(
                                                        'Saved!',
                                                        resp.data.message,
                                                        'success'
                                                    )
                                                } else if (resp.data.status_code == 404) {
                                                    Swal.fire(
                                                        'Error!',
                                                        resp.data.message,
                                                        'error'
                                                    )
                                                }

                                            });


                                        } else if (
                                            /* Read more about handling dismissals below */
                                            result.dismiss === Swal.DismissReason.cancel
                                        ) {
                                            Swal.fire(
                                                'Cancelled',
                                                'Card has not saved',
                                                'error'
                                            )
                                        }
                                    })

                                }
                                self.error_message = '';
                                mApp.unblock("#test_credit_card");
                            } else if(response.data.status_code == 404) {
                                self.error_message = response.data.message;
                                self.cc_info = '';
                                mApp.unblock("#test_credit_card");
                            }
                        })
                        .catch(function (error) {
                            var errors = error.response;
                            mApp.unblock("#test_credit_card");
                            console.log(errors);
                        });

                },
                JSONStringify(json_data) {
                    var library = {};

                    library.json = {
                        replacer: function(match, pIndent, pKey, pVal, pEnd) {
                            var _key = '<span class=json-key>';
                            var _string = '<span class=json-string>';
                            var _number = '<span class=json-number>';
                            var _boolean = '<span class=json-boolean>';
                            var _null  = '<span class=json-null>';
                            var _comma  = '<span class=json-comma>';
                            var response = pIndent || '';

                            if (pKey) {
                                response = response + _key + pKey.replace(/[": ]/g, '') + '</span>: ';
                            }

                            if (pVal) {
                                if (/^"/.test(pVal[0])) {
                                    if (/:$/.test(pVal[0])) {
                                        response = response + _key + pVal + '</span>';
                                    } else {
                                        response = response + _string + pVal + '</span>';
                                    }
                                } else if (/true|false/.test(pVal)) {
                                    response = response + _boolean + pVal + '</span>';
                                } else if (/null/.test(pVal)) {
                                    response = response + _null + pVal + '</span>';
                                } else {
                                    response = response + _number + pVal + '</span>';
                                }
                            }

                            if (pEnd) {
                                response = response + _comma + pEnd + '</span>';
                            }
                            return response;
                        },
                        prettyPrint: function(obj) {
                            var jsonLine = /^( *)("[\w]+": )?("[^"]*"|[\w.+-]*)?([,[{])?$/mg;
                            return JSON.stringify(obj, null, 3)
                                .replace(/&/g, '&amp;').replace(/\\"/g, '&quot;')
                                .replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                .replace(jsonLine, library.json.replacer);
                        }
                    };

                    return library.json.prettyPrint(json_data);
                },
                getAllUsers(){
                    var self = this;
                    axios.get('{{route("getAllUserAccounts")}}', self.test_property)
                        .then(function (response) {
                            // console.log(response.data);
                            if(response.status == 200){
                                self.user_accounts = response.data.data;
                            }
                        })
                        .catch(function (error) {
                            var errors = error.response;
                            console.log(errors);
                        });
                },
            },
            mounted(){
                this.getAllUsers();
            }
        });



    </script>

@endsection