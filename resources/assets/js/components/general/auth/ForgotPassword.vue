<template>
    <div>
        <div class="forms-portion-outer full-row">
            <div class="form-body-outer full-row">
                <div class="m-login__head full-row">
                    <h3 class="d-block float-left overflow-hidden w-100"> Request Password Reset </h3>
                </div>
                <div class="error_wrapper full-row"></div>
                <form class="full-row" role="form">
                    <div class="full-row" :class="{'has-error' : errorsEmail}">
                        <input type="email" v-bind:placeholder="$t('main.text.email')" v-model="IdDetails.email">
                        <div class="error_wrapper full-row">
                            <span :class="{'d-block' : errorsEmail}" class="invalid-feedback" role="alert" v-if="errorsEmail">
                                <span>{{emailError}}</span>
                            </span>
                        </div>
                    </div>
                    <div class="full-row">
                        <button type="reset" class="btn btn-sm btn-gray float-right" id="m_login_forget_password_cancel">
                            {{ $t('main.text.cancel') }}
                        </button>
                        <button class="btn btn-sm btn-green float-right mr-2" id="m_login_forget_password_submit" @click.prevent="IdPost">
                            {{ $t('main.text.request') }}
                        </button>
                    </div>
                    <div class="full-row">
                        <p v-html="waitmsg" style="color: rgb(153, 153, 153); float: left; font-size: 10px; margin: 7px 0 0 0; text-align: center; width: 100%;">&nbsp;</p>
                    </div>
                </form>
            </div>
            <div class="form-footer-outer full-row">
                <p class="full-row">
                    <span> Already have an account? </span>
                    <a href="/login">Login</a>
                </p>
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
    import {mapState} from "vuex";

    export default {
        data() {
            return {
                IdDetails: {
                    email: '',
                },
                errorsEmail: false,
                emailError: '',
                waitmsg: '&nbsp;',
            }
        },
        methods: {
            IdPost() {
                let _this = this;
                _this.emailError = '';
                _this.$store.commit('SHOW_LOADER', null, {root: true});
                axios.post('password/email', _this.IdDetails)
                    .then(function (response) {
                        if (response.data.status) {
                            let msg = response.data.status;
                            toastr.success(msg);
                            _this.$store.commit('HIDE_LOADER', null, {root: true});
                            var e = document.getElementById("m_login_forget_password_submit");
                            e.disabled = true;
                            e.classList.add("not_send");
                            var seconds = 16;
                            var countdown = setInterval(function () {
                                seconds--;
                                _this.waitmsg = 'Button will activate after ' + seconds + ' seconds';
                                if (seconds <= 0) clearInterval(countdown);
                            }, 1000);
                            setTimeout(function () {
                                e.disabled = false;
                                e.classList.remove("not_send");
                                clearInterval(countdown);
                                _this.waitmsg = '&nbsp;';
                            }, 16000);
                        } else if (response.data.email) {
                            let msg = response.data.email;
                            window.toastr.error(msg);
                            _this.$store.commit('HIDE_LOADER', null, {root: true});
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        if (errors.status == 422) {
                            if (errors.data.errors) {
                                if (errors.data.errors.email) {
                                    let err = errors.data.errors;
                                    _this.errorsEmail = true;
                                    _this.emailError = Array.isArray(err.email) ? err.email[0] : err.email;
                                }
                            }
                        }
                        _this.$store.commit('HIDE_LOADER', null, {root: true});
                    });
            },
            displaySignup() {

                var ParientDiv = document.getElementById("m_login");
                var ClSignup = document.getElementsByClassName("m-login__signup");

                ParientDiv.classList.remove("m-login--signin", "m-login--signup", "m-login--forget-password");
                ClSignup[0].classList.add("animated", "flipInX");
                ParientDiv.classList.add("m-login--signup");

                //var ClLogin = document.getElementsByClassName("m-login__signin");
                //var ClSignup = document.getElementsByClassName("m-login__signup");
                //var ClPassword = document.getElementsByClassName("m-login__forget-password");

                //ClLogin[0].classList.remove("animated", "flipInX");
                //ClSignup[0].classList.remove("animated", "flipInX");
                //ClPassword[0].classList.remove("animated", "flipInX");

                //ClSignup[0].style.display = "block";
                //ClLogin[0].style.display = "none";
                //ClPassword[0].style.display = "none";
            },
            displayLogin() {

                var ParientDiv = document.getElementById("m_login");
                var ClSignup = document.getElementsByClassName("m-login__signin");

                ParientDiv.classList.remove("m-login--signin", "m-login--signup", "m-login--forget-password");
                ClSignup[0].classList.add("animated", "flipInX");
                ParientDiv.classList.add("m-login--signin");
            },
            displayPage(p) {

                var ParientDiv = document.getElementById("m_login");
                var ClSignup = document.getElementsByClassName("m-login__" + p);

                ParientDiv.classList.remove("m-login--signin", "m-login--signup", "m-login--forget-password");
                ClSignup[0].classList.add("animated", "flipInX");
                ParientDiv.classList.add("m-login--" + p);
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                }
            })
        }
    }
</script>