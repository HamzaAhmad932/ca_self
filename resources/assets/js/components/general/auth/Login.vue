<template>
    <div>
        <div class="forms-portion-outer full-row">
            <div class="form-body-outer full-row">
                <div class="m-login__head full-row">
                    <h3 class="d-block float-left overflow-hidden w-100"> {{ $t('main.text.login') }} </h3>
                </div>
                <div class="error_wrapper full-row">
                    <span :class="{'d-block' : login.error_status.recaptchaToken}" class="invalid-feedback" role="alert" v-if="login.error_status.recaptchaToken">
                        <span>{{login.error_message.recaptchaToken}}</span>
                    </span>
                </div>
                <form class="full-row" role="form">
                    <div class="full-row" :class="{'has-error' : login.error_status.email}">
                        <input type="email" name="email" placeholder="Email" v-model="login.email">
                        <div class="error_wrapper full-row">
                            <span :class="{'d-block' : login.error_status.email}" class="invalid-feedback" role="alert" v-if="login.error_status.email">
                                <span>{{login.error_message.email}}</span>
                            </span>
                        </div>
                    </div>
                    <div  class="full-row" :class="{'has-error' : login.error_status.password}">
                        <input type="password" name="password" placeholder="Password" v-model="login.password">
                        <div class="error_wrapper full-row">
                            <span :class="{'d-block' : login.error_status.password}" class="invalid-feedback" role="alert" v-if="login.error_status.password">
                                <span>{{login.error_message.password}}</span>
                            </span>
                        </div>
                    </div>
                    <!-- This is in the component you want to have the reCAPTCHA -->
                    <!--<vue-programmatic-invisible-google-recaptcha
                    ref="invisibleRecaptcha1"
                    :sitekey="'6Lc_kKwUAAAAAPIIaIkV0q7A6InjgnV6IXJVNjRm'"
                    :elementId="'invisibleRecaptcha1'"
                    :badgePosition="'right'"
                    :showBadgeMobile="false"
                    :showBadgeDesktop="true"
                    @recaptcha-callback="recaptchaCallback"></vue-programmatic-invisible-google-recaptcha>-->
                    <!-- Where you want to invoke the reCAPTCHA -->
                    <div class="full-row">
                        <label class="m-checkbox">
                            <input name="remember" type="checkbox" v-model="login.remember">
                            <i>Remember Me</i>
                            <span></span>
                        </label>
                        <a class="m-link float-right" href="/password-reset">
                            {{ $t('main.text.forgotten_password') }}
                        </a>
                    </div>
                    <div class="full-row">
                        <button type="submit" name="login" class="btn btn-sm btn-green float-right" id="loginbtn" @click.prevent="attemptLogin()">
                            {{ $t('main.text.login') }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="form-footer-outer full-row">
                <p class="full-row">
                    <span> Do not have an account yet? </span>
                    <a href="/register">Sign Up</a>
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
<script>
    import VueProgrammaticInvisibleGoogleRecaptcha from 'vue-programmatic-invisible-google-recaptcha';
    import {mapState, mapActions} from "vuex";


    export default {

        data() {
            return {
                site_logo: '/images/favicon.png',
            }
        },
        components: {
            VueProgrammaticInvisibleGoogleRecaptcha,
        },
        methods: {
            ...mapActions('general/', [
                'attemptLogin'
            ]),
            //The reCAPTCHA's registered callback. This is where you'll get your token.
            /* recaptchaCallback (recaptchaToken) {
                 // Use the `recaptchaToken` to pass to your backend to verify the token
                 let vm = this;
                 vm.loginDetails.recaptchaToken = recaptchaToken;

                 axios.post('login', vm.loginDetails)
                     .then(function (response) {
                         window.location = "client/dashboard";
                     }) .catch(function (error) {
                     mApp.unblock("#m-login__container");
                     var errors = error.response;
                     //console.log(errors);
                     if(errors.status == 422){

                         if(errors.data) {

                             vm.errorsEmail = false;
                             vm.errorsPassword = false;
                             vm.emailError = null;
                             vm.passwordError = null;

                             if(errors.data.errors.email){
                                 let err = errors.data.errors
                                 vm.errorsEmail = true
                                 vm.emailError = Array.isArray(err.email) ? err.email[0]: err.email
                                 toastr.error(vm.emailError);
                             }

                             if(errors.data.errors.password){
                                 let err = errors.data.errors
                                 vm.errorsPassword = true
                                 vm.passwordError = Array.isArray(err.password) ? err.password[0] : err.password
                                 toastr.error(vm.passwordError);
                             }
                             if(errors.data.errors.recaptchaToken){
                                 toastr.error(errors.data.errors.recaptchaToken[0]);
                             }
                         }
                     }  else if(errors.status == 303){
                                  window.location = "client/dashboard";
                             }

                 });*/

            loginWithOutRecapcha() {

                // Use the `recaptchaToken` to pass to your backend to verify the token
                let vm = this;

                axios.post('/login', vm.loginDetails)
                    .then(function (response) {
                        window.location = "client/v2/dashboard";
                    }).catch(function (error) {
                    mApp.unblock("#m-login__container");
                    var errors = error.response;
                    //console.log(errors);
                    if (errors.status == 422) {

                        if (errors.data) {

                            vm.errorsEmail = false;
                            vm.errorsPassword = false;
                            vm.emailError = null;
                            vm.passwordError = null;

                            if (errors.data.errors.email) {
                                let err = errors.data.errors;
                                vm.errorsEmail = true;
                                vm.emailError = Array.isArray(err.email) ? err.email[0] : err.email;
                                toastr.error(vm.emailError);
                            } else if (errors.data.errors.password) {
                                let err = errors.data.errors;
                                vm.errorsPassword = true;
                                vm.passwordError = Array.isArray(err.password) ? err.password[0] : err.password;
                                toastr.error(vm.passwordError);
                            }
                            if (errors.data.errors.recaptchaToken) {
                                toastr.error(errors.data.errors.recaptchaToken[0]);
                            }
                        }
                    }
                });

            },
            loginPost() {

                mApp.block("#m-login__container", {
                    overlayColor: "#000000",
                    type: "loader",
                    state: "success",
                    message: "Please wait..."
                });
                this.loginWithOutRecapcha();
                //this.$refs.invisibleRecaptcha1.execute();
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                login: (state) =>{
                    return state.general.auth.login
                }
            })
        }
    }
</script>
