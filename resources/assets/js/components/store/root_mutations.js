let mutations = {
    SHOW_LOADER: (state) => {
        state.loader.block = true;
        return state;
    },
    HIDE_LOADER: (state) => {
        state.loader.block = false;
        return state;
    },
    RELOAD_PROPERTIES_WITH_ROOMS: (state) => {
        state.reload_properties_with_rooms++;
        return state;
    }
};

export default mutations;