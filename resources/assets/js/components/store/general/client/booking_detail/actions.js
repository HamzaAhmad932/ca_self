let actions = {

    fetchBookingDetailHeader: async function ({commit}, booking_id) {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            /* route name  get.bookings*/
            url: '/client/v2/get-booking-detail-header/' + booking_id,
            method: 'GET',
        }).then((resp) => {
            commit('BOOKING_DETAIL_HEADER', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    // fetchBookingDetails: async function ({commit}, booking_id) {
    //
    //     commit('SHOW_LOADER', null, {root: true});
    //
    //     await axios({
    //         url: '/client/v2/get-booking-detail/' + booking_id,
    //         method: 'GET'
    //     }).then((resp) => {
    //         commit('BOOKING_DETAILS', resp.data);
    //         commit('HIDE_LOADER', null, {root: true});
    //     }).catch((err) => {
    //         console.log(err);
    //         commit('HIDE_LOADER', null, {root: true});
    //     });
    // },
    //
    // saveBookingDetails: async function ({commit}, data) {
    //
    //     commit('SHOW_LOADER', null, {root: true});
    //     commit('SHOW_BOOKING_DETAILS_ERRORS', {error_message: {}, error_status: {}});
    //
    //     await axios({
    //         url: '/client/v2/save-booking-detail/',
    //         method: 'POST',
    //         data
    //     }).then((resp) => {
    //
    //         if (resp.data.status) {
    //             toastr.success(resp.data.message);
    //         } else {
    //             toastr.error(resp.data.message);
    //         }
    //         commit('HIDE_LOADER', null, {root: true});
    //     }).catch((err) => {
    //
    //         let errors = err.response;
    //         let error_message = {};
    //         let error_status = {};
    //         if (errors.status == 422) {
    //             if (errors.data) {
    //                 for (let k1 in errors.data) {
    //                     if (typeof errors.data[k1] == "object") {
    //                         let validation_errors = errors.data[k1];
    //                         for (let k2 in validation_errors) {
    //                             error_message[k2] = validation_errors[k2][0];
    //                             error_status[k2] = true;
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         commit('SHOW_BOOKING_DETAILS_ERRORS', {error_message, error_status});
    //         commit('HIDE_LOADER', null, {root: true});
    //     });
    // },

    fetchGuestExperience: async function ({commit}, booking_id) {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/client/v2/get-guest-experience/' + booking_id,
            method: 'GET'
        }).then((resp) => {
            commit('GUEST_EXPERIENCE_TAB_DATA', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    saveGuestExperience: async function ({commit}, data) {

        commit('SHOW_LOADER', null, {root: true});
        commit('SHOW_GUEST_EXPERIENCE_ERRORS', {error_message: {}, error_status: {}});

        await axios({
            url: '/client/v2/save-guest-experience/',
            method: 'POST',
            data
        }).then((resp) => {

            if (resp.data.status) {
                toastr.success(resp.data.message);
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
            commit('SHOW_GUEST_EXPERIENCE_ERRORS', {error_message, error_status});
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    // fetchPaymentsTabInformation: async function ({commit}, booking_id) {
    //
    //     commit('SHOW_LOADER', null, {root: true});
    //
    //     await axios({
    //         url: '/client/v2/get-payments-information/' + booking_id,
    //         method: 'GET'
    //     }).then((resp) => {
    //         commit('PAYMENT_TAB_DATA', resp.data);
    //         commit('HIDE_LOADER', null, {root: true});
    //     }).catch((err) => {
    //         console.log(err);
    //         commit('HIDE_LOADER', null, {root: true});
    //     });
    // },

    fetchActivityLogs: async function ({commit}, booking_id) {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/client/v2/get-activity-logs/' + booking_id,
            method: 'GET'
        }).then((resp) => {
            commit('ACTIVITY_LOG_TAB_DATA', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    // fetchGuestDocuments: async function ({commit}, booking_id) {
    //
    //     commit('SHOW_LOADER', null, {root: true});
    //
    //     if (booking_id > 0) {
    //         await axios({
    //             url: '/client/v2/get-guest-documents/' + booking_id,
    //             method: 'GET',
    //         }).then((resp) => {
    //             commit('GUEST_DOCUMENTS_TAB_DATA', resp.data);
    //             commit('SET_GUEST_DOCUMENT', resp.data);
    //             commit('HIDE_LOADER', null, {root: true});
    //         }).catch((err) => {
    //             console.log(err);
    //             commit('HIDE_LOADER', null, {root: true});
    //         });
    //     }
    // },
    //
    // updateDocumentStatus: async function ({commit}, data) {
    //
    //     commit('SHOW_LOADER', null, {root: true});
    //
    //     await axios({
    //         url: '/client/v2/updateStatus/',
    //         method: 'POST',
    //         data
    //     }).then((resp) => {
    //         commit('GUEST_DOCUMENTS_TAB_DATA', resp.data);
    //         commit('SET_GUEST_DOCUMENT', resp.data);
    //         commit('HIDE_LOADER', null, {root: true});
    //     }).catch((err) => {
    //         console.log(err);
    //         commit('HIDE_LOADER', null, {root: true});
    //     });
    // },
    //
    // deleteDocument: async function ({commit, dispatch}, data) {
    //
    //     commit('SHOW_LOADER', null, {root: true});
    //
    //     await axios({
    //         url: '/v2/guest-image-delete',
    //         method: 'POST',
    //         data
    //     }).then((resp) => {
    //         dispatch('fetchGuestDocuments', data.booking_id);
    //         commit('HIDE_LOADER', null, {root: true});
    //     }).catch((err) => {
    //         console.log(err);
    //         commit('HIDE_LOADER', null, {root: true});
    //     });
    // },
    //
    // saveDocument: async ({commit, dispatch}, data) => {
    //
    //     commit('SHOW_LOADER', null, {root: true});
    //
    //     await axios({
    //         url: '/v2/guest-images',
    //         method: 'POST',
    //         data,
    //         headers: {
    //             'Content-Type': 'multipart/form-data'
    //         }
    //     }).then((resp) => {
    //         if (resp.data.status == true) {
    //             toastr.success(resp.data.message);
    //             commit('HIDE_LOADER', null, {root: true});
    //             dispatch('fetchGuestDocuments', data.get('booking_id'));
    //         } else {
    //             toastr.error(resp.data.message);
    //             commit('HIDE_LOADER', null, {root: true});
    //         }
    //     }).catch((err) => {
    //
    //         let errors = err.response;
    //         let error_message = {};
    //         let error_status = {};
    //         if (errors.status == 422) {
    //             if (errors.data) {
    //                 for (let k1 in errors.data) {
    //                     if (typeof errors.data[k1] == "object") {
    //                         let validation_errors = errors.data[k1];
    //                         for (let k2 in validation_errors) {
    //                             error_message[k2] = validation_errors[k2][0];
    //                             error_status[k2] = true;
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         commit('SHOW_UPLOAD_DOCUMENT_ERRORS', {error_message, error_status});
    //
    //         commit('HIDE_LOADER', null, {root: true});
    //     });
    // },

    fetchUpsells: async function ({commit}, booking_id){
        commit('SHOW_LOADER', null, {root : true});
        await axios({
            url: '/client/v2/get-booking-upsell-orders',
            method: 'POST',
            data : {'book_id' : booking_id},
        }).then((resp)=>{
            commit('UPSELL_TAB_DATA', resp.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },

    fetchSentEmails: async function ({commit}, url) {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: url,
            method: 'GET'
        }).then((resp) => {
            commit('SENT_EMAILS_TAB_DATA', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

};

export default actions;