<template>
    <div>
        <h4 class="setup-page-title text-muted">MANAGE EMAILS</h4>

        <div class="page-content full-page-content manage-emails-page">
            <!--<div class="col-md-12" style="background: #fff">
                <div class="page-header header-of-setting-pages">
                    <h1 class="page-title px-0 pt-3 pb-2 text-center d-block">
                        <span class="m-0 pb-1">Manage Emails</span>
                    </h1>
                </div>
            </div>-->
            <div class="col-md-12 p-0 overflow-hidden" style="background: #fff">
                <div class="body-wrapper-of-setting-pages">
                    <div class="col-md-12 col-sm-12">
                        <div class="text-center">
                            <select class="email-types-select-field custom-select custom-select-sm mb-2 mr-1" v-model="active_menu">
                                <option v-for="(head,index) in email_types" :value="head.id">
                                    {{head.title}}
                                </option>
                            </select>
                        </div>
                        <div class="page-body body-of-setting-pages">
                            <ToWhomNav v-if="emails.length > 0"  :contents.sync="emails[0].for_whom" :temp-vars="emails[0].temp_vars" ></ToWhomNav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Block UI Loader-->
        <BlockUI :message="loader.msg" v-if="loader.block === true"  :html="loader.html"></BlockUI>
        <!-- End Block UI-->
    </div>
</template>

<script>

    import {mapState,mapActions} from "vuex";
    import ToWhomNav from "./ToWhomNav";
    export default {
        name: "client_emails",
        components: {ToWhomNav},
        data: ()=> {
            return{
                emailTypes:[],
                active_menu:1,
                email_for_whom:[],
            }
        },
        methods:{
            ...mapActions([
                // "clientLoadDefaultEmails",
                // "loadDynamicVariables",
                // "clientLoadDefaultEmailsTypes"
            ]),
        },
        mounted() {
            // this.loadDynamicVariables();
            this.$store.dispatch('general/clientLoadDefaultEmailsTypes');
            //clientLoadDefaultEmailsTypes();
        },
        computed:{

            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },

                emails: (state) => {
                    return state.general.emails_client.client_emails;
                },
                email_types: (state) => {
                    return state.general.emails_client.email_types;
                }
            }),
        },
        watch:{
            active_menu: {
                deep: true,
                immediate: true,
                handler: function (new_value, old_value) {

                    if(new_value !== old_value){
                        this.$store.dispatch('general/clientLoadDefaultEmails', new_value);
                        //this.clientLoadDefaultEmails(new_value);
                    }
                }
            }
        }
    }
</script>

<style scoped>

</style>
