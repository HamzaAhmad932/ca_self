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
                                    <pms_setup_steps_navbar :step="4"></pms_setup_steps_navbar>
                                    <!-- STEPS NAV-BAR END-->
                                    <guest-experience-setting :account_setup="true"></guest-experience-setting>
                                    <div class="setup-footer d-flex justify-content-center">
                                        <a class="btn btn-light align-self-start setup-back text-muted"
                                           href="/client/v2/pms-setup-step-3"> <i class="fas fa-arrow-left"></i><span> Back </span></a>
                                        <a class="btn btn-success px-md-4" href="/client/v2/pms-setup-step-5">Save and
                                            Continue <i class="fas fa-arrow-right"></i></a>
                                        <a class="btn btn-light align-self-start setup-skip text-muted"
                                           href="/client/v2/pms-setup-step-5"><span> Skip </span> <i
                                                class="fas fa-arrow-right"></i></a>
                                    </div>
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
        name: "pms_setup_step4",
        components: {Pms_setup_steps_navbar},
    }
</script>

<style scoped>

</style>