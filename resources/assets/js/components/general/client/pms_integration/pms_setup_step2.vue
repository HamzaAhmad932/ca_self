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
                                    <pms_setup_steps_navbar :step="2"></pms_setup_steps_navbar>
                                    <!-- STEPS NAV-BAR END-->
                                    <!--Modal Pop-up Booking Source Settings Setup Begin-->
                                    <client-booking-source-settings
                                            :action='action'
                                            :bookingSourceFormId='bookingSourceFormId'
                                            :isMasterSettings="true"
                                            :propertyInfoId='propertyInfoId'
                                            :propertyInfoObjectIndex='propertyInfoObjectIndex'>
                                    </client-booking-source-settings>
                                    <!--Modal Pop-up Booking Source  Settings Setup End-->
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
        name: "pms_setup_step2",
        components: {Pms_setup_steps_navbar},

        data() {
            return {
                propertyInfoId: 0,
                propertyInfoObjectIndex: 0,
                bookingSourceFormId: 0,
                action: '',
            }
        },
        methods: {}, //Methods End
        mounted() {
            this.action = 'edit';
        }
    }
</script>

<style scoped>

</style>