let actions = {
    getUpsellTypes: async ({commit},{for_filters,serve_id}) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios({
            url: '/client/v2/get-upsell-types/'+for_filters+"/"+serve_id,
            method: 'GET'
        }).then((resp) => {
            commit('GET_UPSELL_TYPES', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    addUpsell: async ({state, commit, dispatch}, serve_id) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios({
            url: '/client/v2/upsell-store/' + serve_id,
            method: 'POST',
            data: state.upsell.form_data,
        }).then((resp) => {
            if (resp.data.status) {
                toastr.success(resp.data.message);
                dispatch('loadUpsellFormData', serve_id);
                commit('RELOAD_PROPERTIES_WITH_ROOMS', null, {root: true});
            } else {
                toastr.error(resp.data.message);
            }

            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {

            let errors = err.response;
            let error_message = {};
            let error_status = {};
            if (errors.status == 422 || errors.status == 429) {
                if (errors.data) {
                    for (let k1 in errors.data) {
                        if (typeof errors.data[k1] == "object") {
                            let validation_errors = errors.data[k1];
                            for (let k2 in validation_errors) {
                                if(k2.indexOf('.') !== -1){
                                    const keys = k2.split('.');
                                    if(!error_message.hasOwnProperty(keys[0])) {
                                        Object.defineProperty(error_message, keys[0], {
                                            value: {},
                                            writable: true,
                                            enumerable: true,
                                            configurable: true
                                        });
                                    }
                                    if(!error_status.hasOwnProperty(keys[0])){
                                        Object.defineProperty(error_status, keys[0], {
                                            value: {},
                                            writable: true,
                                            enumerable: true,
                                            configurable: true
                                        });
                                    }
                                    Object.defineProperty(error_message[keys[0]], keys[1], {
                                        value: validation_errors[k2][0],
                                        writable: true,
                                        enumerable: true,
                                        configurable: true
                                    });
                                    Object.defineProperty(error_status[keys[0]], keys[1], {
                                        value: true,
                                        writable: true,
                                        enumerable: true,
                                        configurable: true
                                    });
                                }else{
                                    error_message[k2] = validation_errors[k2][0];
                                    error_status[k2] = true;
                                }
                            }
                        }
                    }
                }
            }
            commit('SHOW_ADD_UPSELL_ERRORS', {error_message, error_status});
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    loadUpsellFormData: async ({state, commit, dispatch}, serve_id) => {
        commit('SHOW_LOADER', null, {root: true});
        if (serve_id > 0) {

            dispatch('getList', 'get-upsell-list');

            await axios({
                url: '/client/v2/get-upsell-form-data',
                method: 'POST',
                data: {serve_id: serve_id},
            }).then((resp) => {
                if (resp.data.status) {
                    commit('LOAD_UPSELL_FORM_DATA', resp.data.data);
                }
                commit('HIDE_LOADER', null, {root: true});
            }).catch((err) => {
                console.log(err);
                commit('HIDE_LOADER', null, {root: true});
            });
        } else {
            commit('RESET_UPSELL_FORM_DATA');
            commit('HIDE_LOADER', null, {root: true});
        }
    },
    /**
     *
     * @param state
     * @param commit
     * @param list_type | 'upsell' | 'upsell-orders'
     * @returns {Promise<void>}
     */
    getList: async ({state, commit}, list_type) => {
        commit('SHOW_LOADER', null, {root: true});
        let url = '/client/v2/';
        url += list_type == 'upsell-orders' ? 'get-upsell-order-list' : 'get-upsell-list';
        await axios({
            url: url,
            method: 'POST',
            data: {filters: state.upsell.filters},
        }).then((resp) => {
            commit('SET_UPSELL_LIST', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    connectOrDisconnect: async ({state, commit}, data) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios({
            url: '/client/v2/upsell-status-change',
            method: 'POST',
            data: data,
        }).then((resp) => {
            if (resp.data.status) {
                commit('UPSELL_LIST_STATUS_UPDATE', data);
                toastr.success(resp.data.message);
            } else {
                data.status = data.status ? 0 :1;
                commit('UPSELL_LIST_STATUS_UPDATE', data);
                document.getElementById('checkbox-'+data.id).checked = data.status ? true:false;
                toastr.error(resp.data.message);
            }
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            commit('HIDE_LOADER', null, {root : true});
            data.status = data.status ? 0 :1;
            document.getElementById('checkbox-'+data.id).checked = data.status ? true:false;
            commit('UPSELL_LIST_STATUS_UPDATE', data);
            console.log(err);
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
