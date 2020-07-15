let mutations = {
    SET_GUIDE_BOOKS_TYPES_LISTS: function (state, data) {
        state.paginationResponse = data;

        //update intercom data
        // updateIntercomData('guidebook_listing_page_loaded');

        return state;
    },

};
export default mutations;
