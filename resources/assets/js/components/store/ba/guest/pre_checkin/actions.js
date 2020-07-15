let actions = {
    fetchGuestDetail: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-detail/' + id,
            method: 'GET'
        }).then((resp) => {
            commit('STEP_0_DETAIL', resp.data);
            commit('general/SET_HEADER_META', resp.data, {root: true});
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchStepOneData: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-data-step-1/' + id,
            method: 'GET'
        }).then((resp) => {
            if (resp.data.status == true) {
                commit('STEP_1_DATA', resp.data);
            }
            commit('UPDATE_META', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });

    },
    fetchStepTwoData: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-data-step-2/' + id,
            method: 'GET'
        }).then((resp) => {
            if (resp.data.status == true) {
                commit('STEP_2_DATA', resp.data);
            }
            commit('UPDATE_META', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    fetchStepFiveData: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-data-step-5/' + id,
            method: 'GET'
        }).then((resp) => {
            if (resp.data.status == true) {
                commit('SUMMARY_DATA', resp.data);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    fetchStepThreeData: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-data-step-3/' + id,
            method: 'GET'
        }).then((resp) => {

            commit('STEP_3_DATA', resp.data.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    fetchStepFourData: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-data-step-4/' + id,
            method: 'GET'
        }).then((resp) => {
            if (resp.data.status == true) {
                commit('CREDIT_CARD_STEP_DATA', resp.data);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchStepSevenData: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-data-step-7/' + id,
            method: 'GET'
        }).then((resp) => {
            if (resp.data.status == true) {
                commit('STEP_7_DATA', resp.data);
            }
            commit('UPDATE_META', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    saveVerificationImage: async ({commit}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-images',
            method: 'POST',
            data,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }).then((resp) => {
            if (resp.data.status == true) {
                commit('STEP_3_DATA', resp.data.data);
            }else{
                toastr.error(resp.data.message);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    saveGuestData: async ({commit}, payload) => {
        commit('SHOW_LOADER', null, {root: true});
        //remove validation error
        commit('SHOW_STEP_1_ERRORS', {error_message: {
                email: '',
                phone: '',
                adults: ''
            }, error_status: {
                email: false,
                phone: false,
                adults: false
            }});
        commit('SHOW_STEP_2_ERRORS', {error_message: {
                arriving_by: '',
                plane_number: '',
                arrival_time: '',
                other: ''
            }, error_status: {
                arriving_by: false,
                plane_number: false,
                arrival_time: false,
                other: false
            }});

        await axios({
            url: '/v2/update-guest-data',
            method: 'POST',
            data: payload
        }).then((resp) => {
            if (resp.data.status == true) {
                if (resp.data.data.meta.current_step == '1') {
                    commit('STEP_1_COMPLETED', resp.data.data);
                }

                if (resp.data.data.meta.current_step == '2') {
                    commit('STEP_2_COMPLETED', resp.data.data);
                }
            } else {
                toastr.error(resp.data.message);
            }
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

            if (payload.current_tab == 1) {
                commit('SHOW_STEP_1_ERRORS', {error_message, error_status});
            } else if (payload.current_tab == 2) {
                commit('SHOW_STEP_2_ERRORS', {error_message, error_status});
            }

            commit('HIDE_LOADER', null, {root: true});
        });

    },

    saveCardData: async ({commit}, payload) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/update-guest-card',
            method: 'POST',
            data: payload
        }).then((resp) => {
            commit('SHOW_CREDIT_CARD_STEP_ERRORS', {error_message: {}, error_status: {}});

            if (resp.data.status == true) {
                commit('CREDIT_CARD_STEP_COMPLETED', resp.data.data);
                if(resp.data.message != null){
                    toastr.success(resp.data.message);
                }
            }else{

                if(resp.data.status_code == 10006){
                    commit('SHOW_3DS_MODAL_BOX');
                }
                toastr.error(resp.data.message);
            }
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
            commit('SHOW_CREDIT_CARD_STEP_ERRORS', {error_message, error_status});
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    saveDigitalSignature: ({commit}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        axios({
            url: '/v2/guest-digital-images',
            method: 'POST',
            data
        }).then((resp) => {
            //commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    saveSelfPortrait: async ({commit, dispatch}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-digital-images',
            method: 'POST',
            data,
        }).then((resp) => {

            if (resp.data.status == true) {
                toastr.success(resp.data.message);
                commit('STEP_7_COMPLETED', resp.data.data);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    completePrecheckin: async ({commit}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/pre-checkin-complete',
            method: 'POST',
            data,
        }).then((resp) => {
            if (resp.data.status == true) {
                commit('PRECHECKIN_COMPLETE', resp.data.data);
            }else{
                toastr.error(resp.data.message);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    goToPreviousStep: async ({commit}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/previous-step-meta',
            method: 'POST',
            data,
        }).then((resp) => {

            commit('UPDATE_STEP_LINK', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    goToNextStep: async ({commit}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/next-step-meta',
            method: 'POST',
            data,
        }).then((resp) => {

            commit('UPDATE_STEP_LINK', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchAddOnServices: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/get-add-on-services/' + id,
            method: 'GET'
        }).then((resp) => {
            if (resp.data.status == true) {
                commit('ADD_ON_SERVICES_DATA', resp.data);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchPaymentDetail: async ({commit}, {id, meta})=> {
        commit('SHOW_LOADER', null, {root : true});

        await axios({
            url : '/checkout/'+id+'/4/json',
            method : 'POST',
            data: {id, meta}
        }).then((resp)=>{
            if(resp.data.status == true){
                commit('_3DS_PAYMENT_DETAIL', resp.data);
            }
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },


    saveAddonsCart: async ({commit, state}, data) => {

        let upsell_listing_ids = [];
        let invalid = false;
        $.each(state.add_on_service.data.available, function (key, value) {

            if(value.in_cart){
                if(value.show_guest_count){
                    if(isNaN(value.guest_count) || value.guest_count === '' || value.guest_count <= '0'){
                        toastr.error('Invalid person value is not acceptable.');
                        invalid = true;
                    }
                }
                upsell_listing_ids.push({id: value.id, persons: value.guest_count, show_guest_count: value.show_guest_count});
            }
        });

        if(!invalid){

            data.upsell_listing_ids = upsell_listing_ids;

            commit('SHOW_LOADER', null, {root: true});

            await axios({
                url: '/v2/save-addons-cart/',
                method: 'POST',
                data
            }).then((resp) => {

                if (resp.data.status == true) {
                    toastr.success(resp.data.message);
                    commit('ADD_ON_SERVICES_STEP_COMPLETED', resp.data.data);
                } else {
                    toastr.error(resp.data.message);
                }
                commit('HIDE_LOADER', null, {root: true});
            }).catch((err) => {
                console.log(err);
                commit('HIDE_LOADER', null, {root: true});
            });
        }
    },

    modifyTotalPrice: ({commit, state, dispatch}, index)=>{

        commit('MODIFY_TOTAL_PRICE', index);

        dispatch('setCartTotalAmount');
    },

    setIncartAmount: ({commit, state, dispatch}, {index, event})=>{

        commit('UPDATE_IN_CART_STATUS', {index, event});

        dispatch('setCartTotalAmount');
    },

    setCartTotalAmount: ({commit, state})=>{

        let total_in_cart_amount = 0;
        $.each(state.add_on_service.data.available, function (key, value) {
            if(value.in_cart){
                total_in_cart_amount += parseFloat(value.total_price);
            }
        });
        commit('SET_INCART_AMOUNT', Math.abs(total_in_cart_amount));
    },

    changeMode : async ({commit}, data)=> {
        commit('SHOW_LOADER', null, {root : true});

        await axios({
            url : '/v2/change-mode',
            method : 'POST',
            data
        }).then((resp)=>{
            if(resp.data.status == true){
                toastr.success(resp.data.message);
                commit('UPDATE_READ_WRITE_MODE', resp.data.data);
            }
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },

    deletePreCheckinImages: async ({commit,dispatch}, data)=> {
        commit('SHOW_LOADER', null, {root : true});
        await axios({
            url : '/v2/pre-checkin-delete-document-image',
            method : 'POST',
            data
        }).then((resp)=>{
            if(resp.data.status == true){
                toastr.success(resp.data.message);
                dispatch('fetchStepThreeData',data.booking_id);
                //window.location.reload();
            } else {
                toastr.error(resp.data.message);
            }
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },

    fetchPaymentSummary: async ({commit}, id) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/ba-payment-summary/' + id,
            method: 'GET'
        }).then((resp) => {
            commit('SET_PAYMENT_SUMMARY', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

};


export default actions;
