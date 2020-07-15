let mutations = {
    SET_UPSELLS_TYPES_LISTS: function (state, data) {
        state.paginationResponse = data;

        //update intercom data
        // updateIntercomData('upsell_listing_page_loaded');

        return state;
    },

};
export default mutations;
