<template>
    <div>
        <!--begin::Sub - Item-->
        <div v-for="(for_whom,index) in contents" class="m-accordion__item">
            <div class="m-accordion__item-head collapsed"  role="tab"  data-toggle="collapse" :href="'#for_whom_body_'+for_whom.id" aria-expanded="false">
<!--                <span class="m-accordion__item-icon"><i  class="fa flaticon-interface-7" style=""></i></span>-->
                <span class="m-accordion__item-title">For {{for_whom.name}}</span>
                <span class="m-accordion__item-mode"></span>
            </div>

            <div class="m-accordion__item-body collapse" :id="'for_whom_body_'+for_whom.id"  role="tabpanel" >
                <div class="m-accordion__item-content">
                    <email_form  :content="for_whom.content" :temp-vars="tempVars"></email_form>
                </div>
            </div>
        </div>
        <!--end::Sub - Item-->
    </div>
</template>

<script>
    import Email_form from "./email_form";
    export default {
        name: "email_for_whom",
        components: {Email_form},
        props:["contents","tempVars"],
    }
</script>

<style scoped>

</style>
