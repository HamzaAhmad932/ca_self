let mutations = {
    SET_GUIDE_BOOK_TYPE_FORM_DATA: function (state, data) {
        data = data[0];
        state.guideBookTypes.formData = {
            ...state.guideBookTypes.formData, ...{
                serve_id: data.id,
                icon: data.icon,
                title: data.title,
                priority: data.priority,
            }
        };
        // console.error(state.formData);
        return state;
    },
    RESET_GUIDE_BOOK_TYPE_FORM_DATA: function (state) {
        state.formData = {
            serve_id:0,
            title:'',
            icon:'fas fa-info',
            priority:0,
        };
        return state;
    },
    SET_GUIDE_BOOK_TYPES_LIST: function (state, data) {
        state.guideBookTypes.paginationResponse = data;

        //update intercom data
        updateIntercomData('guidebook_listing_page_loaded');

        return state;
    },

};
export default mutations;
