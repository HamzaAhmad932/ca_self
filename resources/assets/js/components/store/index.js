import Vue from 'vue';
import Vuex from 'vuex';
import state from "./root_state";
import mutations from "./root_mutations";
import actions from "./root_actions";
import general from "./general/index";
import ba from "./ba/index";
//import smx_general from "./smx/general/index";


Vue.use(Vuex);
export default new Vuex.Store({
    state,
    mutations,
    // actions,
    modules : {
        general,                     // CA General Components and re usable  //
        ba,                         // Booking Automation Custom Components //
        //smx_general,             // SMX (General PMS) Custom Components  //
    }
});
