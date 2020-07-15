let actions = {
    getTermAndConditionList: async ({state, commit}) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios({
            url: '/admin/get-terms-and-conditions',
            method: 'POST',
            data: {filters: state.filters},
        }).then((response) => {

            commit('SET_TERM_AND_CONDITION_LIST', response.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            commit('HIDE_LOADER', null, {root: true});
        });
    },

};

export default actions;
