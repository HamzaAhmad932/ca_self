let actions = {
    addTac: ({commit}, data) => {
        commit('SHOW_LOADER', null, {root: true});
        let url = '/client/v2/';
        let rest_form = true;
        if (data.serve_id != 0) {
            url += 'terms-and-conditions-update';
            rest_form = false;
        } else {
            url += 'terms-and-conditions-save';
        }
        return new Promise((resolve, reject) => {
            axios.post(url, data).then((response) => {
                commit('HIDE_LOADER', null, {root: true});
                toastr.success(response.data.message);
                if (rest_form) {
                    commit('RESET_TAC_FORM_DATA');
                }
                return resolve(rest_form);
            }).catch((err) => {
                commit('HIDE_LOADER', null, {root: true});
                if (err.response.status == 422) {
                    resolve(err.response.data.errors);
                }
            });

        });
    },
    loadOldData: async ({state, commit}, serve_id) => {
        commit('SHOW_LOADER', null, {root: true});
        if (serve_id > 0) {
            axios.post('/client/v2/terms-and-conditions-old/', {"serve_id": serve_id}).then((response) => {
                commit('SET_TAC_FORM_DATA', response.data.data);
                commit('HIDE_LOADER', null, {root: true});
            }).catch((err) => {
                console.log(err);
                if (err.response.status == 422) {
                    $.each(err.response.data.errors, function (key, value) {
                        toastr.error(value[0]);
                    });
                }
                commit('HIDE_LOADER', null, {root: true});
            });
        } else {
            commit('RESET_TAC_FORM_DATA');
        }
    },
    getTacList: async ({state, commit}) => {

        commit('SHOW_LOADER', null, {root: true});
        await axios({
            url: '/client/v2/terms-and-conditions-get-all',
            method: 'POST',
            data: {filters: state.tac.filters},
        }).then((response) => {

            commit('SET_TAC_LIST', response.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    updateTacStatus: async ({state, commit}, data) => {
        commit('SHOW_LOADER', null, {root: true});
        return new Promise(resolve => {
            axios({
                url: '/client/v2/terms-and-conditions-update-status',
                method: 'POST',
                data: data,
            }).then((resp) => {
                if (resp.data.status) {
                    toastr.success(resp.data.message);
                } else {
                    toastr.error(resp.data.message);
                }
                commit('HIDE_LOADER', null, {root: true});
                resolve(resp.data.status);
            }).catch((err) => {
                console.log(err);
                commit('HIDE_LOADER', null, {root: true});
                resolve(false);
            });
        })
    },

};

export default actions;
