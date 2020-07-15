<template v-if="$can('accountSetup')">
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <account-enable-disable-button></account-enable-disable-button>
                        <div class="page-body pmsintegration_page_content">
                            <div class="content-box">
                                <div class="setup-box account-setup-content">
                                    <!-- STEPS NAV-BAR BEGIN-->
                                    <pms_setup_steps_navbar :step="3"></pms_setup_steps_navbar>
                                    <!-- STEPS NAV-BAR END-->
                                    <!--Modal Pop-up Payment Gateways Settings Setup Begin-->
                                    <client-payment-gateway-settings
                                            :action="action"
                                            :isMasterSettings="true" :propertyInfoId="propertyInfoId"
                                            :propertyInfoObjectIndex="propertyInfoObjectIndex"
                                            :selectedPaymentGatewayFormID="selectedPaymentGatewayFormID"></client-payment-gateway-settings>
                                    <!--Modal Pop-up Payment Gateways Settings Setup End-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Pms_setup_steps_navbar from "./pms_setup_steps_navbar";

    export default {
        name: "pms_setup_step3",
        components: {Pms_setup_steps_navbar},
        data() {
            return {
                propertyInfoId: 0,
                propertyInfoObjectIndex: 0,
                bookingSourceFormId: 0,
                action: '',
                selectedPaymentGatewayFormID: 0,
            }
        },
        methods: {
            /**
             * Response For getting Current Gateways for this property
             */
            getCurrentPaymentGateway() {
                let _this = this;
                axios.post('/client/v2/pms-get-master-pg-id-or-first-form-id/')
                    .then(function (response) {
                        if (response.data.status) {
                            _this.selectedPaymentGatewayFormID = response.data.data.paymentGatewayFormId;
                        }
                        _this.action = 'add';
                    }).catch(function (error) {
                    console.log(error);
                });
            },
        }, //Methods End
        mounted() {
            this.getCurrentPaymentGateway();
        }
    }
</script>

<style scoped>

</style>