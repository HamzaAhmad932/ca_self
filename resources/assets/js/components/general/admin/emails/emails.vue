<template>
    <div>
        <!--Begin-->

        <div class="m-content">
            <div id="check-settings">
                <div class="m-portlet m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Eamil Settings
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="col-xl-12">
                            <!--begin::Content-->
                            <div class="tab-content">

                                <div class="tab-pane active" id="m_widget5_tab2_content" aria-expanded="false">


                                    <!--begin::Section-->
                                    <div  class="m-accordion m-accordion--default" role="tablist" style="padding-top:30px  !important">
                                        <!--begin::Item-->
                                        <div v-for="(head,index) in emails" class="m-accordion__item">
                                            <div class="m-accordion__item-head collapsed"  role="tab"  data-toggle="collapse" :href="'#ac_main_body_'+index" aria-expanded="false">
                                                <span class="m-accordion__item-icon"><i  :class="head.icon" style=""></i></span>
                                                <span class="m-accordion__item-title">{{head.title}}</span>
                                                <span class="m-accordion__item-mode"></span>
                                            </div>

                                            <div class="m-accordion__item-body collapse" role="tabpanel"  :id="'ac_main_body_'+index" >
                                                <div class="m-accordion__item-content">
                                                    <!-- Start Sub Items -->
<!--                                                    <pre>-->
<!--                                                    <p>{{index}}</p>-->
<!--                                                    <p>{{head}}</p>-->
<!--                                                        </pre>-->
                                                    <email_for_whom :contents="head.for_whom" :temp-vars="head.temp_vars" ></email_for_whom>
                                                    <!-- End Sub Items-->
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Item-->

                                    </div>
                                    <!--end::Section-->
                                </div>
                            </div>
                            <!--end::Content-->
                        </div>

                    </div>
                </div>
                <!--end:: Widgets/Best Sellers-->
            </div>
        </div>
        <!-- Block UI Loader-->
        <BlockUI :message="loader.msg" v-if="loader.block === true"  :html="loader.html"></BlockUI>
        <!-- End Block UI-->
        <!--End-->
    </div>
</template>

<script>


    import {mapState,mapActions} from "vuex";
    import Email_for_whom from "./email_for_whom";
    export default {
        name: "emails",
        components: {Email_for_whom},
        computed:{
        ...mapState({
            loader : (state)=>{
                return state.loader;
            },
            emails: (state) => {
                return state.emails_admin.emails;
            },
        }),
            ...mapActions([
                "loadDefaultEmails","loadDynamicVariables",
            ]),

        },
        data: ()=> {
            return{
                emailTypes:[],
            }
        },
        methods:{
        },
        mounted() {
            // this.loadDynamicVariables;
            this.loadDefaultEmails;
        }
    }
</script>

<style scoped>

</style>
