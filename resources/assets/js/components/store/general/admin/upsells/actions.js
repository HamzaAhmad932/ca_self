let actions = {
    getAllUpsellTypes: async ({state, commit},{for_filters,serve_id}) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios({
            url: '/admin/get-all-upsell-types/'+for_filters+"/"+serve_id+"/"+state.filters.user_account_id,
            method: 'GET'
        }).then((resp) => {
            commit('SET_ALL_UPSELL_TYPES', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    /**
     *
     * @param state
     * @param commit
     * @param list_type | 'upsell' | 'upsell-orders'
     * @returns {Promise<void>}
     */
    getAllList: async ({state, commit}, list_type) => {
        commit('SHOW_LOADER', null, {root: true});
        let url = '/admin/';
        url += list_type == 'upsell-orders' ? 'get-upsell-order-list' : 'get-all-upsell-list';

        await axios({
            url: url,
            method: 'POST',
            data: {filters: state.filters},
        }).then((resp) => {
            commit('SET_ALL_UPSELL_LIST', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    getUpsellConfig: async ({commit}) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios({
            url: '/client/v2/get-upsell-config',
            method: 'POST',
        }).then((resp) => {
            commit('SET_UPSELL_CONFIG', resp.data.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            commit('HIDE_LOADER', null, {root : true});
            console.log(err);
        });
    },

};

export default actions;
