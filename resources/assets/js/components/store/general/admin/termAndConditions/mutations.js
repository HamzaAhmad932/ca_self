let mutations = {
    SET_TERM_AND_CONDITION_LIST(state, data) {
        state.paginationResponse = data;

        //update intercom data
        // updateIntercomData('terms_listing_page_loaded');

        return state;
    },

};
export default mutations;