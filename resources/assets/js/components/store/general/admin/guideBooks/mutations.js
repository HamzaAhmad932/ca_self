let mutations = {
    SET_GUIDE_BOOK_TYPES: function (state, data) {
        state.types = data;
        return state;
    },
    SET_GUIDE_BOOK_LIST: function (state, data) {
        state.paginationResponse = data;

        //update intercom data
        // updateIntercomData('guidebook_listing_page_loaded');

        return state;
    },

};
export default mutations;