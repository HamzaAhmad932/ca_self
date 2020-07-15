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

    <div class="m-grid__item m-grid__item--fluid m-wrapper" id="test_booking">

        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">

            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/leftnav.test_booking')}}</h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item m-nav__item--home">
                            <a href="#" class="m-nav__link m-nav__link--icon">
                                <i class="m-nav__link-icon la la-home"></i>
                            </a>
                        </li>
                        <li class="m-nav__separator">-</li>

                        <li class="m-nav__item">

                            <span class="m-nav__link-text">{{__('admin/leftnav.test_booking')}}</span>

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

                            <div class="row m--margin-top-20" style="border-bottom: 1px solid #ebedf2; margin-bottom: 3rem; padding-bottom: 3rem;">

                                <div class="col-md-5">
                                    <label for="user_account_id">User Account</label>
{{--                                    <input type="text" id="user_account_id" name="user_account_id" class="form-control" v-model="test_booking.user_account_id" />--}}
                                    <select id="user_account_id" name="user_account_id" class="form-control" v-model="test_booking.user_account_id">
                                        <option value="" selected disabled>Select User Account</option>
                                        <option v-for="user_account in user_accounts" :value="user_account.id">@{{ user_account.id +' -- '+user_account.name }}</option>
                                    </select>
                                </div>

                                <div class="col-md-5">
                                    <label for="booking_id">PMS Booking ID</label>
                                    <input type="text" id="booking_id" name="booking_id" class="form-control" v-model="test_booking.booking_id" />
                                </div>

                                <div class="col-md-2">
                                    <label for="booking-submit">&nbsp;</label>
                                    <br>
                                    <button type="button" id="booking-submit" name="submit" @click="getBookingXmlJsonRequest" class="btn btn-primary">Search</button>
                                </div>

                            </div>

                        </form>

                    </div>

                    <div class="m-section__content">
                        <div class="row m--margin-top-20">
                            <div class="col-12 alert alert-danger text-center" v-if="error_message != ''">@{{error_message}}</div>
                            <div class="col-md-6">
                                <h2 class="text-center">JSON</h2>
                                <div class="col-12 alert alert-danger text-center" v-if="request_response.json_error != ''">
                                    @{{request_response.json_error}}
                                </div>
                                <pre v-if="request_response.json_response != ''">
                                    <p v-html="request_response.json_response"></p>
                                </pre>
                            </div>
                            <div class="col-md-6">
                                <h2 class="text-center">XML</h2>
                                <div class="col-12 alert alert-danger text-center" v-if="request_response.xml_error != ''">
                                    @{{request_response.xml_error}}
                                </div>
                                <pre v-if="request_response.xml_response != ''">
                                    <p v-html="request_response.xml_response"></p>
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

        var testBooking = new Vue({
            el: '#test_booking',
            data(){
                return {
                    test_booking: {
                        user_account_id: '',
                        booking_id: '',
                    },
                    request_response: {
                        json_response: '',
                        xml_response: '',
                        json_error: '',
                        xml_error: '',
                    },
                    user_accounts: '',
                    error_message: ''
                }
            },
            methods:{
                getBookingXmlJsonRequest(){
                    var self = this;
                    if(this.test_booking.user_account_id == '') {
                        alert("Please enter user account id");
                        return;
                    }
                    if(this.test_booking.booking_id == '') {
                        alert("Please enter booking id");
                        return;
                    }

                    mApp.block("#test_booking",{
                        overlayColor:"#000000",
                        type:"loader",
                        state:"success",
                        message:"Please wait..."
                    });

                    axios.post('{{route("getBookingXmlJsonRequest")}}', self.test_booking)
                        .then(function (response) {
                            // console.log(response.data)
                            if(response.data.status_code == 200){
                                self.request_response.json_response = self.JSONStringify(response.data.data.json_response);
                                self.request_response.json_error = response.data.data.json_error;
                                self.request_response.xml_response = self.JSONStringify(response.data.data.xml_response);
                                self.request_response.xml_error = response.data.data.xml_error;
                                self.error_message = '';
                                mApp.unblock("#test_booking");
                            } else if(response.data.status_code == 404) {
                                self.error_message = response.data.message;
                                self.request_response.json_response = '';
                                self.request_response.xml_response = '';
                                mApp.unblock("#test_booking");
                            }
                        })
                        .catch(function (error) {
                            var errors = error.response;
                            mApp.unblock("#test_booking");
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
                    axios.get('{{route("getAllUserAccounts")}}', self.test_booking)
                        .then(function (response) {
                            console.log(response.data);
                            if(response.status == 200){
                                self.user_accounts = response.data.data;
                            }
                        })
                        .catch(function (error) {
                            var errors = error.response;
                            console.log(errors);
                        });
                }

                },
            mounted(){
                this.getAllUsers();
            }
        });



    </script>

@endsection