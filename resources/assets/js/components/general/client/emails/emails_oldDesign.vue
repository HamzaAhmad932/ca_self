<template>
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-header header-of-setting-pages mb-3">
                            <h1 class="page-title">Manage Emails</h1>
                        </div>
                        <div class="page-body">
                            <div class="content-box">
                                <div role="tablist" class="m-accordion m-accordion--default">
                                    <!--begin::Item-->
                                    <div v-for="(head,index) in emails" class="m-accordion__item">
                                        <div class="m-accordion__item-head collapsed" role="tab" data-toggle="collapse"
                                             :href="'#ac_main_body_'+index" aria-expanded="false">
                                            <span class="m-accordion__item-icon"><i :class="head.icon"
                                                                                    style=""></i></span>
                                            <span class="m-accordion__item-title">{{head.title}}</span>
                                            <span class="m-accordion__item-mode"></span>
                                        </div>

                                        <div class="m-accordion__item-body collapse" role="tabpanel"
                                             :id="'ac_main_body_'+index">
                                            <div class="m-accordion__item-content">
                                                <!-- Start Sub Items -->
                                                <email_for_whom :contents.sync="head.for_whom"></email_for_whom>
                                                <!-- End Sub Items-->
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Item-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Block UI Loader-->
        <BlockUI :message="loader.msg" v-if="loader.block === true" :html="loader.html"></BlockUI>
        <!-- End Block UI-->
        <!--End-->
    </div>
</template>

<script>


    import {mapState, mapActions} from "vuex";
    import Email_for_whom from "./email_for_whom";

    export default {
        name: "client_emails",
        components: {Email_for_whom},
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                emails: (state) => {
                    return state.general.emails_client.client_emails;
                },
            }),
        },
        data: () => {
            return {
                emailTypes: [],
            }
        },
        methods: {},
        mounted() {
            this.$store.dispatch('general/loadDynamicVariables');
            this.$store.dispatch('general/clientLoadDefaultEmails');
        }
    }
</script>

<style scoped>

</style>
