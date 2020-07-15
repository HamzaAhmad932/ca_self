let actions = {
    loadDynamicVariables: async ({state,commit}) => {
        if(!state.already_loaded){
            commit('SHOW_LOADER', null, {root: true});
            state.already_loaded=true;
            await axios.get('/admin/template-vars').then((resp) => {
                commit('SET_DYNAMIC_VARS_DATA', resp.data.data);
                commit('HIDE_LOADER', null, {root: true});
            }).catch((err) => {
                console.log(err);
                commit('HIDE_LOADER', null, {root: true});
            });
        }
    },
};

export default actions;
