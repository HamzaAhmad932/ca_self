let mutations = {
    SET_UPSELL_TYPE_FORM_DATA: function (state, data) {
        data = data[0];
        state.upsellTypes.formData = {
            ...state.upsellTypes.formData, ...{
                serve_id: data.id,
                icon: data.icon,
                title: data.title,
                priority: data.priority,
                status: data.status,
            }
        };
        return state;
    },
    RESET_UPSELL_TYPE_FORM_DATA: function (state) {
        state.upsellTypes.formData = {
            serve_id:0,
            title:'',
            icon:'fas fa-info',
            priority:0,
            status:true,
        };
        return state;
    },
    SET_UPSELL_TYPES_LIST: function (state, data) {
        state.upsellTypes.paginationResponse = data;

        //update intercom data
        updateIntercomData('upsell_listing_page_loaded');

        return state;
    },
    UPDATE_UPSELL_TYPE_STATUS: function (state, data) {
        state.upsellTypes.paginationResponse.data[data.index][data.updateWhat] = data.updateWith;

        //update intercom data
        updateIntercomData('upsell_listing_page_loaded');

        return state;
    },

};
export default mutations;
