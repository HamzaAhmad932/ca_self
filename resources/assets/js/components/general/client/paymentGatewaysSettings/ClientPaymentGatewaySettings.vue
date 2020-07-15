<template>
    <div>
        <div class="setup-body">
            <h4 class="setup-page-title">Connect to your payment gateway</h4>
            <form class="pms-setup-form">
                <div class="form-group">
                    <label>Select Payment Gateway </label>
                    <select @change="getPaymentGatewayFormOnChange(propertyInfoId)" class="custom-select custom-select-sm"
                            id="select-gateway-component-input"
                            v-model="selectBoxPaymentGatewayFormID">
                        <option
                                :value="( ((action === 'change') || (action === 'update')) ? selectedPaymentGatewayFormID  : gateway.id )"
                                v-for="gateway in allSupportedPaymentGateways"
                                v-if="((((action === 'change') || (action === 'update')) ? ((gateway.id == selectedPaymentGatewayFormID)) : true))">
                            {{gateway.name}}
                        </option>
                    </select>
                </div>
                <div class="form-group" v-for="credential in paymentGatewayForm.credentials"
                     v-if="paymentGatewayForm.credentials">

                    <label :title="credential.desc"
                           v-if="((credential.type != 'button') && (credential.state != 'hidden'))">{{credential.label}}</label>
                    <a @click="oAuthConnect(credential.name, credential.url)" class="btn btn-primary px-md-4"
                       href="javascript:void(0);" v-if="credential.type == 'button'"> {{ credential.label }} </a>

                    <img src="/images/done.gif" style="height: 40px; filter: hue-rotate(366deg);"
                         v-if="((credential.type == 'button') && (credential.value !== null && credential.value !== '')) && paymentGatewayForm.userGatewayIsVerified == 1"/>

                    <input :name="credential.name"
                           :type="credential.safe ?  credential.type : 'password'"
                           :value="credential.value" class="form-control form-control-sm"
                           v-if="((credential.type != 'button')  && (credential.state != 'hidden'))"/>

                    <div style="margin-top: 0.5rem;"
                         v-if="credential.name == 'stripe_user_id' && (credential.value !== null && credential.value !== '')">

                        <hr>

                        <div v-if="paymentGatewayForm.stripe_account_name != ''">
                            <div class="row" v-if="paymentGatewayForm.userGatewayIsVerified == 0">
                                <div class="col col-11">
                                    <label for="stripe_account_id">Stripe Account:</label>
                                    <span v-html="paymentGatewayForm.stripe_account_name" id="stripe_account_id" style="color: red; text-decoration: underline;"></span>
                                    <p class="text-muted"><strong>Note: </strong><i>Problem with your stripe connection. Please re-connect.</i></p>
                                </div>
                            </div>
                            <div class="row" v-else>
                                <div class="col col-12">
                                    <label for="stripe_account_id">Stripe Account:</label>
                                    <span v-html="paymentGatewayForm.stripe_account_name" id="stripe_account_id" style="text-decoration: underline;"></span>
                                </div>
                            </div>
                        </div>


                    </div>

                </div>
            </form>
        </div>
        <div class="setup-footer d-flex justify-content-center">
            <a class="btn btn-light align-self-start setup-back text-muted" href="/client/v2/pms-setup-step-2"
               v-if="isMasterSettings"> <i class="fas fa-arrow-left"></i><span> Back </span></a>
            <a @click="saveSettings()" class="btn btn-success px-md-4" href="javascript:void(0);"
               v-if="((action === 'change') || (action === 'add') || (action === 'update'))"> {{ isMasterSettings ?
                'Save and Continue' : 'Save'}} <i class="fas fa-arrow-right" v-if="isMasterSettings"></i></a>
            <a class="btn btn-light align-self-start setup-skip text-muted" href="/client/v2/pms-setup-step-4"
               v-if="isMasterSettings"><span> Skip </span> <i class="fas fa-arrow-right"></i></a>
        </div>
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
    </div>

</template>

<script>
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';

    Vue.use(VueToast);
    export default {

        name: "ClientPaymentGatewaySettings",
        props: ['propertyInfoId', 'propertyInfoObjectIndex', 'selectedPaymentGatewayFormID', 'action', 'isMasterSettings'],

        data() {
            return {
                msg: 'Please Wait...',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                paymentGatewayForm: {},
                allSupportedPaymentGateways: {},
                db_const_stripe_key_input_name: 'stripe_user_id',
                selectBoxPaymentGatewayFormID: '',
            }
        },
        components: {},
        methods: {
            getPaymentGatewayFormOnChange(propertyInfoId) {

                if (this.propertyInfoId != '' && this.propertyInfoId != 0) {
                    var self = this;
                    swal.fire({
                        title: "If you are changing Payment Gateway, refunds for already collected payments will not work through ChargeAutomation.",
                        type: "question",
                        showCancelButton: !0,
                        confirmButtonText: "I understand"
                    }).then(function (e) {
                        if (e.value) {
                            self.getPaymentGatewayForm(propertyInfoId);
                        } else {
                            self.selectBoxPaymentGatewayFormID = self.selectedPaymentGatewayFormID;
                            self.getPaymentGatewayForm(propertyInfoId);
                        }
                    });
                }
            },

            getPaymentGatewayForm(propertyInfoId) {
                if ((this.action != null) && (this.action !== '') && (this.propertyInfoId !== '')) {
                    this.paymentGatewayForm = {};
                    let selectBox = document.querySelector("#select-gateway-component-input");
                    let _this = this;
                    if ((_this.selectBoxPaymentGatewayFormID == '') || (_this.selectBoxPaymentGatewayFormID == 0)) {
                        $.each(_this.allSupportedPaymentGateways, function ($key, $value) {
                            _this.selectBoxPaymentGatewayFormID = $value.id;

                        });
                    }
                    if (_this.selectBoxPaymentGatewayFormID != '') {
                        let _this = this;
                        _this.block = true;
                        axios.post('/client/v2/get-payment-gateway-with-keys/', {
                            'propertyInfoId': propertyInfoId,
                            'paymentGatewayFormId': _this.selectBoxPaymentGatewayFormID
                        }).then(function (response) {
                            if (response.data.status)
                                _this.paymentGatewayForm = response.data.data;
                            else
                                _this.toasterView('Fail to load Payment Gateway', false);
                            _this.block = false;
                        }).catch(function (error) {
                            console.log(error);
                            _this.block = false;
                            _this.toasterView('Fail to load Payment Gateway', false);
                        });
                    }
                }
            },

            /**
             * Api Response For getting all Gateways List
             */
            getAllSupportedPaymentGateways() {
                let _this = this;
                axios.get('/api/get-payment-gateways/active')
                    .then(function (response) {
                        _this.allSupportedPaymentGateways = response.data.data;
                        _this.getPaymentGatewayForm(_this.propertyInfoId);
                    }).catch(function (error) {
                    console.log(error);
                });
            },

            saveSettings() {
                if (Object.keys(this.paymentGatewayForm).length > 0) {

                    let _this = this;
                    _this.block = true;
                    _this.$set(_this.paymentGatewayForm, 'propertyInfoId', _this.propertyInfoId);
                    axios.post('/client/v2/save-payment-gateway-keys/', {'data': _this.paymentGatewayForm})
                        .then(function (response) {
                            if (response.data.status) {
                                _this.toasterView('Payment Gateway Updated Successfully', true);

                                updateIntercomData('payment_gateway_saved');


                                if ((_this.isMasterSettings !== undefined) && (_this.isMasterSettings)) {
                                    setTimeout(function() {
                                        window.location.href = '/client/v2/pms-setup-step-4';
                                    }, 1000);
                                } else {
                                    _this.$emit('saved', _this.propertyInfoObjectIndex, 'paymentGateway');
                                }
                            } else {
                                _this.toasterView(response.data.message, false);
                                _this.$emit('saved', _this.propertyInfoObjectIndex, 'paymentGateway');

                            }
                            _this.block = false;
                        }).catch(function (error) {
                        _this.block = false;
                        _this.toasterView('Fail to Save Payment Gateway Settings', false);
                        console.log(error);
                    });

                }
            },

            /**
             * Redirect To Stripe Connect  Url
             * @param name
             * @param url
             */
            oAuthConnect(name, url) {

                //Checking for stripe connect standard
                if (name == this.db_const_stripe_key_input_name) {
                    let _this = this;
                    _this.block = true;
                    axios.post('/client/v2/pg-store-without-auth-test/' + _this.propertyInfoId, {
                        'data': _this.paymentGatewayForm,
                        'url': url
                    })
                        .then(function (response) {
                            if (response.data.status == true)
                                window.location.href = response.data.data.url;
                            else
                                _this.toasterView('Fail to Save Payment Gateway Settings', false);
                        }).catch(function (error) {
                        console.log(error);
                        _this.block = false;
                        _this.toasterView('Fail to Save Payment Gateway Settings', false);
                    });
                }
            },
            /**
             * Toaster View on any action
             * @param msg
             * @param status
             */
            toasterView(msg, status = false) {
                let type = (status ? 'success' : 'error');
                Vue.$toast.open({message: msg, duration: 3000, type: type, position: 'top-right',});
            },
        },//Methods End
        watch: {
            action: function () {
                this.selectBoxPaymentGatewayFormID = this.selectedPaymentGatewayFormID;
                this.getPaymentGatewayForm(this.propertyInfoId);
            },
            selectedPaymentGatewayFormID: function () {
                this.selectBoxPaymentGatewayFormID = this.selectedPaymentGatewayFormID;
                this.getPaymentGatewayForm(this.propertyInfoId);
            },
        },
        mounted() {
            this.paymentGatewayForm = {};
            this.getAllSupportedPaymentGateways(); // Fetch List of Gateways results
        },
    }
</script>

<style scoped>

</style>
