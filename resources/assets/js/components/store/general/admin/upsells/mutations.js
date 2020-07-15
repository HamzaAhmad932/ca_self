let mutations = {
    SET_ALL_UPSELL_TYPES(state, data) {
        return state.upsell_types = data.data;
    },
    SET_ALL_UPSELL_LIST(state, data) {
        state.paginationResponse = data;

        //update intercom data
        // updateIntercomData('upsell_listing_page_loaded');

        return state;
    },
    SET_UPSELL_CONFIG(state, data) {
        state.upsell_config = data;
        return state;
    },
};

export default mutations;
