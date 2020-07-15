let actions = {

    fetchGuestPortalData: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-portal/' + id,
            method: 'GET'
        }).then((resp) => {
            commit('GUEST_PORTAL_DATA', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    
    fetchAddCardTerminalData: async ({commit}, payload) => {
        commit('SHOW_LOADER', null, {root: true});
        
        let result = await axios({
            url: '/v2/fetch-add-card-terminal-data',
            method: 'POST',
            data: payload
        }).then((resp) => {
            commit('HIDE_LOADER', null, {root: true});
            return resp.data.data.pgTerminal;
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
            return 'Something went wrong. Try again.';
        });
        
        return result;
    },

    saveGuestPanelCardData: async ({commit, dispatch}, payload) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/update-guest-card',
            method: 'POST',
            data: payload
        }).then((resp) => {
            if (resp.data.status == true) {
                toastr.success(resp.data.message);
                dispatch('fetchGuestPortalData', payload.booking_info_id);
                commit('GUEST_CARD_UPDATE');
            } else {
                toastr.error(resp.data.message);
            }
            commit('TOGGLE_CARD_ERRORS', {error_message: {}, error_status: {}});
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {

            let errors = err.response;
            let error_message = {};
            let error_status = {};
            if (errors.status == 422) {
                if (errors.data) {
                    for (let k1 in errors.data) {
                        if (typeof errors.data[k1] == "object") {
                            let validation_errors = errors.data[k1];
                            for (let k2 in validation_errors) {
                                error_message[k2] = validation_errors[k2][0];
                                error_status[k2] = true;
                            }
                        }
                    }
                }
            }
            commit('TOGGLE_CARD_ERRORS', {error_message, error_status});
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    saveBasicInfo: async ({commit}, data) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/update-basic-info',
            method: 'POST',
            data
        }).then((resp) => {
            if (resp.data.status) {
                toastr.success(resp.data.message);
                commit('BASIC_INFO_UPDATE', data);
            } else {
                toastr.error(resp.data.message);
            }
            commit('TOGGLE_BASIC_INFO_ERRORS', {error_message: {}, error_status: {}});
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {

            let errors = err.response;
            let error_message = {};
            let error_status = {};
            if (errors.status == 422) {
                if (errors.data) {
                    for (let k1 in errors.data) {
                        if (typeof errors.data[k1] == "object") {
                            let validation_errors = errors.data[k1];
                            for (let k2 in validation_errors) {
                                error_message[k2] = validation_errors[k2][0];
                                error_status[k2] = true;
                            }
                        }
                    }
                }
            }
            commit('TOGGLE_BASIC_INFO_ERRORS', {error_message, error_status});
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    deleteImage: async ({commit}, data) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-image-delete',
            method: 'POST',
            data
        }).then((resp) => {
            if (resp.data.status) {
                commit('IMAGES_UPDATED', resp.data.data);
            } else {
                toastr.error(resp.data.message);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    saveImageDocument: async ({commit}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-images/',
            method: 'POST',
            data,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }).then((resp) => {
            if (resp.data.status == true) {
                toastr.success(resp.data.message);
                commit('IMAGES_UPDATED', { request_response: resp.data.data, image_type: data.get('alert_type') });
            } else {
                toastr.error(resp.data.message);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((error) => {
            console.log(error);
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    purchaseAddOnService: async  ({commit, dispatch }, data)=> {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/purchase-add-on-service/',
            method: 'POST',
            data,
        }).then((resp) => {
            if (resp.data.status == true) {
                toastr.success(resp.data.message);
                dispatch('fetchAddOnServices', data.booking_info_id);
            } else {
                if(resp.data.status_code == 10006){
                    commit('SHOW_3DS_MODAL_BOX_AT_GUEST_PORTAL');
                }
                toastr.error(resp.data.message);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((error) => {
            console.log(error);
            commit('HIDE_LOADER', null, {root: true});
        });

    }

};


export default actions;
