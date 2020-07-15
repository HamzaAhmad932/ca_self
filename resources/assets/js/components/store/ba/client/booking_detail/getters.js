let getters = {
    toggleTab:  (state)=> {
        return state.booking_detail.tab_section.current_tab.component_name;
    },
};

export default getters;