<template>
    <div>
        <!--begin::Sub - Item-->
        <section id="tabs">
            <vue-tabs :activeTabColor="'black'" :activeTextColor="'white'">
                <v-tab :title="for_whom.name == 'Client' ? 'Host' : for_whom.name" v-for="(for_whom,index) in contents" v-bind:key="for_whom.id">
                    <div slot="title">{{for_whom.name == 'Client' ? 'Host' : for_whom.name}}</div>
                    <EmailForm :content.sync="for_whom.content" :temp-vars="tempVars"></EmailForm>
                </v-tab>
            </vue-tabs>
        </section>
        <!--end::Sub - Item-->
    </div>
</template>

<script>
    import EmailForm from "./EmailForm";
    import {VueTabs, VTab} from 'vue-nav-tabs/dist/vue-tabs.js';
    import 'vue-nav-tabs/themes/vue-tabs.css';
    export default {
        name: "client_email_for_whom",
        components: {
            EmailForm,
            VueTabs,
            VTab
        },
        props:["contents","tempVars"],
    }
</script>

<style scoped>

</style>
