<template>
    <div>
        <div class="forms-portion-outer full-row">
        <div class="form-body-outer full-row">
            <div class="m-login__head full-row">
                <h3 class="d-block float-left overflow-hidden w-100"> {{ $t('main.text.signup') }} </h3>
            </div>
            <div class="error_wrapper full-row">
                <span class="invalid-feedback" role="alert"></span>
            </div>
            <form class="full-row" role="form" id="register-form">
                <div class="full-row" :class="{'has-error' : register.error_status.name}">
                    <input type="text" placeholder="Name" v-model="register.name">
                    <div class="error_wrapper full-row">
                        <span :class="{'d-block' : register.error_status.name}" class="invalid-feedback" role="alert" v-if="register.error_status.name">
                            <span>{{register.error_message.name}}</span>
                        </span>
                    </div>
                </div>
                <div class="full-row" :class="{'has-error' : register.error_status.companyname}">
                    <input type="text" placeholder="Company Name" v-model="register.companyname">
                    <div class="error_wrapper full-row">
                        <span :class="{'d-block' : register.error_status.companyname}" class="invalid-feedback" role="alert" v-if="register.error_status.companyname">
                            <span>{{register.error_message.companyname}}</span>
                        </span>
                    </div>
                </div>
                <div class="d-block float-left w-100" :class="{'has-error' : register.error_status.phone}">
                    <vue-tel-input :autofocus="false" inputId="phone" v-bind="bindProps" v-model="register.phone"></vue-tel-input>
                    <div class="error_wrapper full-row">
                        <span :class="{'d-block' : register.error_status.phone}" class="invalid-feedback" role="alert" v-if="register.error_status.phone">
                            <span>{{register.error_message.phone}}</span>
                        </span>
                    </div>
                </div>
                <div class="full-row" :class="{'has-error' : register.error_status.email}">
                    <input type="email" placeholder="Email" v-model="register.email">
                    <div class="error_wrapper full-row">
                        <span :class="{'d-block' : register.error_status.email}" class="invalid-feedback" role="alert" v-if="register.error_status.email">
                            <span>{{register.error_message.email}}</span>
                        </span>
                    </div>
                </div>
                <div class="full-row" :class="{'has-error' : register.error_status.password}">
                    <input type="password" id="password" name="password"
                            v-bind:placeholder="$t('main.text.password')" v-model="register.password">
                    <div class="error_wrapper full-row">
                      <span :class="{'d-block' : register.error_status.password}" class="invalid-feedback" role="alert" v-if="register.error_status.password">
                          <span>{{register.error_message.password}}</span>
                      </span>
                    </div>
                </div>
                <div class="full-row" :class="{'has-error' : register.error_status.password_confirmation}">
                    <input type="password" id="password-confirm" name="password_confirmation" v-bind:placeholder="$t('main.text.confirm_password')" v-model="register.password_confirmation">
                    <div class="error_wrapper full-row">
                      <span :class="{'d-block' : register.error_status.password_confirmation}" class="invalid-feedback" role="alert" v-if="register.error_status.password_confirmation">
                          <span>{{register.error_message.password_confirmation}}</span>
                      </span>
                    </div>
                </div>
                <div class="full-row" :class="{'has-error' : register.error_status.current_pms}">
                    <input type="text" placeholder="Current Property Management System(PMS)" v-model="register.current_pms">
                    <div class="error_wrapper full-row">
                        <span :class="{'d-block' : register.error_status.current_pms}" class="invalid-feedback" role="alert" v-if="register.error_status.current_pms">
                            <span>{{register.error_message.current_pms}}</span>
                        </span>
                    </div>
                </div>
                <div class="full-row" :class="{'has-error' : register.error_status.agree}">
                    <label class="m-checkbox">
                        <input name="agree" type="checkbox" v-model="register.agree">
                        <i>
                            I have read and agree to the
                            <a :href="getFrontEndTermsUrl" target="_blank" id="iagree">
                                {{ $t('main.text.agree') }}
                            </a>
                        </i>
                        <span></span>
                    </label>
                    <div class="error_wrapper full-row">
                        <span :class="{'d-block' : register.error_status.agree}" class="invalid-feedback" role="alert" v-if="register.error_status.agree">
                            <span>{{register.error_message.agree}}</span>
                        </span>
                    </div>
                </div>
                <div class="full-row">
                    <button type="submit" name="signupbtn" class="btn btn-sm btn-green float-right" id="signupbtn"  @click.prevent="getStarted()">
                        Get Started
                    </button>
                </div>
            </form>
        </div>
        <div class="form-footer-outer full-row">
            <p class="full-row">
                <span> Already have an account? </span>
                <a href="/login">Login</a>
            </p>
            <p class="full-row">
                <span> For ChargeAutomation V1 (Older Version),</span>
                <a href="https://chargeautomation.com/root_app/login.php">Click here</a>
            </p>
        </div>
    </div>
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>

<style>
    .modal-backdrop {
        z-index: -1 !important;
    }
</style>
<script>
    import VueProgrammaticInvisibleGoogleRecaptcha from 'vue-programmatic-invisible-google-recaptcha';
    import {mapState, mapActions} from "vuex";

    export default {
        data() {
            return {
                bindProps: {
                    autocomplete: "on",
                    autofocus: false,
                    defaultCountry: "",
                    disabled: false,
                    disabledFetchingCountry: false,
                    disabledFormatting: false,
                    dropdownOptions: {disabledDialCode: false, tabindex: 0},
                    dynamicPlaceholder: false,
                    enabledCountryCode: false,
                    enabledFlags: true,
                    ignoredCountries: [],
                    inputClasses: [],
                    inputOptions: {showDialCode: true, tabindex: 0},
                    maxLen: 18,
                    mode: "international",
                    name: "phone_input",
                    onlyCountries: [],
                    placeholder: "Enter Phone Number",
                    preferredCountries: [],
                    required: true,
                    validCharactersOnly: true,
                    wrapperClasses: [],
                },
            }
        }, components: {
            VueProgrammaticInvisibleGoogleRecaptcha,
        },

        methods: {
            ...mapActions('general/', [
                'attemptRegister'
            ]),

            getStarted(){
                // Later On there will be a need to add recaptcha function implementation in this function

                this.attemptRegister();
            },


            /*
     recaptchaCallback (recaptchaToken) {
            // Use the `recaptchaToken` to pass to your backend to verify the token
            var _this = this
            var vm = this.hasErrors
            var _vm = this.errorMessage
            _this.registerData.recaptchaToken = recaptchaToken;

            _vm.name='',
                _vm.companyname='',
                _vm.phone='',
                _vm.email= '',
                _vm.current_pms= '',
                _vm.agree=''

            axios.post('register', _this.registerData)
                .then(function (response) {
                    //console.log(response);
                    //alert(response);
                    if (response.data.registered == 1){
                        mApp.unblock("#m-login__container");
                        toastr.options = {
                            "closeButton": false,
                            "debug": false,
                            "newestOnTop": false,
                            "progressBar": false,
                            "positionClass": "toast-top-center",
                            "preventDuplicates": false,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "timeOut": "5000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut"
                        };

                        // toastr.success("Please Check your email for verification", "Thanks");
                        //$('#ralert').show();
                        $("#registrationForm")[0].reset();
                        myfunc();
                        console.log(response.data.url);
                        window.location = response.data.url;

                    }else {

                    }

                })
                .catch(function (error) {
                    mApp.unblock("#m-login__container");
                    var errors = error.response
                    if (errors.status == 422) {
                        if (errors.data) {
                            if (errors.data.errors.name) {
                                let err = errors.data.errors
                                vm.name = true
                                _vm.name = Array.isArray(err.name) ? err.name[0] : err.name
                            }
                            if (errors.data.errors.companyname) {
                                let err = errors.data.errors
                               vm.companyname = true
                                _vm.companyname = Array.isArray(err.companyname) ? err.companyname[0] : err.companyname
                            }
                            if (errors.data.errors.email) {
                                let err = errors.data.errors
                                vm.email = true
                                _vm.email = Array.isArray(err.email) ? err.email[0] : err.email
                            }
                            if (errors.data.errors.phone) {
                                let err = errors.data.errors
                                vm.phone = true
                                _vm.phone = Array.isArray(err.phone) ? err.phone[0] : err.phone
                            }
                            if (errors.data.errors.current_pms) {
                                let err = errors.data.errors
                                vm.current_pms = true
                                _vm.current_pms = Array.isArray(err.current_pms) ? err.current_pms[0] : err.current_pms
                            }
                            if (errors.data.errors.agree) {
                                let err = errors.data.errors
                                vm.agree = true
                                _vm.agree = 'Please accept Terms and Conditions';//Array.isArray(err.agree) ? err.agree[0] : err.agree
                            }
                            if (errors.data.errors.recaptchaToken) {
                                 toastr.error(errors.data.errors.recaptchaToken[0]);
                            }
                        }
                    }
                });

        }, */

            /*** This Function (registerWithOutRecapcha) is DEPRECATED***/
            registerWithOutRecapcha() {

                var _this = this;
                var vm = _this.hasErrors;
                var _vm = _this.errorMessage;
                _vm.name = '';
                _vm.companyname = '';
                _vm.phone = '';
                _vm.password = '';
                _vm.email = '';
                _vm.current_pms = '';
                _vm.agree = '';
                _vm.password_confirmation = '';

                //var code = document.getElementsByClassName("country-code")[0].innerText;
                //_this.registerData.code = code;
                //_this.registerData.phone = code + _this.registerData.phone;
                //var nooo =  _this.registerData.phone;
                //alert(nooo);
                //return false;
                axios.post('register', _this.registerData)
                    .then(function (response) {
                        //console.log(response);
                        //alert(response);
                        if (response.data.registered == 1) {
                            //create user on Intercom
                            createUserOnIntercom(response.data.user, response.data.user_account, response.data.user_hash);

                            toastr.options = {
                                "closeButton": false,
                                "debug": false,
                                "newestOnTop": false,
                                "progressBar": false,
                                "positionClass": "toast-top-center",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "5000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            };

                            //wait for Intercom request then redirect
                            setTimeout(function() {
                                mApp.unblock("#m-login__container");
                                window.location = response.data.url;
                                $("#registrationForm")[0].reset();
                            }, 2000);

                        } else {
                            console.log('some error');
                            mApp.unblock("#m-login__container");
                        }

                    })
                    .catch(function (error) {
                        mApp.unblock("#m-login__container");
                        var errors = error.response;

                        console.log('error');
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.name) {
                                    let err = errors.data.errors;
                                    vm.name = true;
                                    _vm.name = Array.isArray(err.name) ? err.name[0] : err.name
                                }
                                if (errors.data.errors.companyname) {
                                    let err = errors.data.errors;
                                    vm.companyname = true;
                                    _vm.companyname = Array.isArray(err.companyname) ? err.companyname[0] : err.companyname
                                }
                                if (errors.data.errors.email) {
                                    let err = errors.data.errors;
                                    vm.email = true;
                                    _vm.email = Array.isArray(err.email) ? err.email[0] : err.email
                                }
                                if (errors.data.errors.phone) {
                                    let err = errors.data.errors;
                                    vm.phone = true;
                                    _vm.phone = Array.isArray(err.phone) ? err.phone[0] : err.phone
                                }
                                if (errors.data.errors.password) {
                                    let err = errors.data.errors;
                                    vm.password = true;
                                    _vm.password = Array.isArray(err.password) ? err.password[0] : err.password
                                }
                                if (errors.data.errors.password_confirmation) {
                                    let err = errors.data.errors;
                                    vm.password_confirmation = true;
                                    _vm.password_confirmation = Array.isArray(err.password_confirmation) ? err.password_confirmation[0] : err.password_confirmation
                                }
                                if (errors.data.errors.current_pms) {
                                    let err = errors.data.errors;
                                    vm.current_pms = true;
                                    _vm.current_pms = Array.isArray(err.current_pms) ? err.current_pms[0] : err.current_pms
                                }
                                if (errors.data.errors.agree) {
                                    let err = errors.data.errors;
                                    vm.agree = true;
                                    _vm.agree = 'Please accept Terms and Conditions';//Array.isArray(err.agree) ? err.agree[0] : err.agree
                                }
                                if (errors.data.errors.recaptchaToken) {
                                    toastr.error(errors.data.errors.recaptchaToken[0]);
                                }
                            }
                        }
                    });

            },

            registerPost() {
                mApp.block("#m-login__container", {
                    overlayColor: "#000000",
                    type: "loader",
                    state: "success",
                    message: "Please wait..."
                });
                this.registerWithOutRecapcha();
                //this.$refs.invisibleRecaptcha2.execute();
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                register: (state) =>{
                    return state.general.auth.register
                }
            }),
            getFrontEndTermsUrl() {
                return window.front_end_terms;
            }
        }

    }


</script>

