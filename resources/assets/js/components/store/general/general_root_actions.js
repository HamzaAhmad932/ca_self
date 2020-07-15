let general_root_actions = {

    refundAmountActiveId: ({commit}, data) => {
        commit('REFUND_AMOUNT_ACTIVE_ID', data.booking_id);
        commit('REFUND_AMOUNT_ACTIVE_TRANSACTION_ID', data.transaction_id);
        commit('REFUND_AMOUNT_MAX_VALID_VALUE', data.amount_valid_to_refund);
    },

    captureAmountActiveId: ({commit}, data) => {
        commit('CAPTURE_AMOUNT_ACTIVE_BOOKING_ID', data.booking_id);
        commit('CAPTURE_AMOUNT_ACTIVE_AUTH_ID', data.cc_auth_id);
        commit('CAPTURE_AMOUNT_MAX_VALID_VALUE', data.amount_valid_to_capture);
    },

    guestUploadActiveID: ({commit}, booking_id) => {
        //console.log(booking_id);
        commit('GUEST_UPLOAD_DOC_ID', booking_id);
    },
    guestCreditCardActiveID: ({commit}, booking_id) => {
        commit('GUEST_CREDIT_CARD_ID', booking_id);
    },
    reduceAmountData: ({commit}, data) => {
        commit('REDUCE_AMOUNT_DATA', data);
    },
    additionalChargeActiveId: ({commit}, booking_id) => {
        commit('ADDITIONAL_CHARGE_ACTIVE_ID', booking_id);
    },

    additionalCharge: async ({state, commit, dispatch}, additional_charge) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/client/v2/charge-more',
            method: 'POST',
            data: {'data': additional_charge}
        }).then((resp) => {

            if (resp.data.status) {

                dispatch(additional_charge.prefix +'fetchBookingDetail', additional_charge.booking_info_id, {root: true});

                //dispatching payment tab data at booking detail page
                dispatch(additional_charge.prefix +'fetchPaymentsTabInformation', additional_charge.booking_info_id, {root: true});

                dispatch('fetchActivityLogs', additional_charge.booking_info_id);

                commit('HIDE_LOADER', null, {root: true});
                toastr.success(resp.data.message);
            } else {
                toastr.error(resp.data.message);
                commit('HIDE_LOADER', null, {root: true});
            }

        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    captureAuthAmount: async ({commit, dispatch}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: "/client/v2/capture-auth",
            method: 'POST',
            data,
        }).then((resp) => {
            if (resp.data.status == true && resp.data.status_code == 200) {
                dispatch(data.pms_prefix+'/fetchBookingDetail', data.booking_info_id, {root: true});
                dispatch('fetchActivityLogs', additional_charge.booking_info_id);
                commit('HIDE_LOADER', null, {root: true});
                toastr.success(resp.data.message);
            } else {
                toastr.error(resp.data.message);
                commit('HIDE_LOADER', null, {root: true});
            }
            $('#force_modal_close').click();
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
            commit('SHOW_CAPTURE_AMOUNT_ERRORS', {error_message, error_status});
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    RefundAmount: async ({commit, dispatch}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        axios({
            url: "/client/v2/refund-amount",
            method: 'POST',
            data,
        }).then((resp) => {
            if (resp.data.status == true && resp.data.status_code == 200) {
                dispatch(data.prefix+'/fetchBookingDetail', data.booking_id, {root:true});
                dispatch(data.prefix+'/fetchPaymentsTabInformation', data.booking_id, {root:true});
                dispatch('fetchActivityLogs', additional_charge.booking_info_id);
                commit('HIDE_LOADER', null, {root: true});
                toastr.success(resp.data.message);
            } else {
                commit('HIDE_LOADER', null, {root: true});
                toastr.error(resp.data.message);
            }
        });

    },

    ReduceAmount: async ({commit, dispatch}, data) => {

        commit('SHOW_LOADER', null, {root: true});
        axios({
            url: "/client/v2/reduce-amount",
            method: 'POST',
            data: {data},
        }).then((resp) => {
            if (resp.data.status == true && resp.data.status_code == 200) {
                commit('BOOKING_NEW_BALANCE_AND_TOTAL', resp.data);
                dispatch(data.pms_prefix+'/fetchBookingDetail', data.booking_info_id, {root: true});
                dispatch('fetchActivityLogs', additional_charge.booking_info_id);
                //commit('HIDE_LOADER', null, {root : true});
                toastr.success(resp.data.message);
            } else {
                commit('HIDE_LOADER', null, {root: true});
                toastr.error(resp.data.message);
            }
        });
    },
    markAsPaid: async ({commit, dispatch}, data) => {
        commit('SHOW_LOADER', null, {root: true});
        axios({
            url: "/client/v2/mark-as-paid",
            method: 'POST',
            data: data,
        }).then((resp) => {
            if (resp.data.status == true && resp.data.status_code == 200) {
                dispatch(data.pms_prefix+'/fetchBookingDetail', data.booking_info_id, {root: true});
                dispatch('fetchActivityLogs', additional_charge.booking_info_id);
                commit('HIDE_LOADER', null, {root: true});
                toastr.success(resp.data.message);
            } else {
                commit('HIDE_LOADER', null, {root: true});
                toastr.error(resp.data.message);
            }
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    manuallyVoidTransaction: async ({commit, dispatch}, data) => {
        commit('SHOW_LOADER', null, {root: true});
        axios({
            url: "/client/v2/manually-void-transaction",
            method: 'POST',
            data: data,
        }).then((resp) => {
            if (resp.data.status == true && resp.data.status_code == 200) {
                dispatch(data.pms_prefix+'/fetchBookingDetail', data.booking_info_id, {root: true});
                dispatch('fetchActivityLogs', additional_charge.booking_info_id);
                commit('HIDE_LOADER', null, {root: true});
                toastr.success(resp.data.message);
            } else {
                commit('HIDE_LOADER', null, {root: true});
                toastr.error(resp.data.message);
            }
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    voidAuth: async ({commit, dispatch}, data) => {
        commit('SHOW_LOADER', null, {root: true});
        axios({
            url: "/client/v2/manually-void-auth",
            method: 'POST',
            data: data,
        }).then((resp) => {
            if (resp.data.status == true && resp.data.status_code == 200) {
                dispatch(data.pms_prefix+'/fetchBookingDetail', data.booking_info_id, {root: true});
                dispatch('fetchActivityLogs', additional_charge.booking_info_id);
                commit('HIDE_LOADER', null, {root: true});
                toastr.success(resp.data.message);
            } else {
                commit('HIDE_LOADER', null, {root: true});
                toastr.error(resp.data.message);
            }
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchGuestDocuments: async function ({commit}, booking_id) {

        commit('SHOW_LOADER', null, {root: true});

        if (booking_id > 0) {
            await axios({
                url: '/client/v2/get-guest-documents/' + booking_id,
                method: 'GET',
            }).then((resp) => {
                commit('GUEST_DOCUMENTS_TAB_DATA', resp.data);
                commit('SET_GUEST_DOCUMENT', resp.data);
                commit('HIDE_LOADER', null, {root: true});
            }).catch((err) => {
                console.log(err);
                commit('HIDE_LOADER', null, {root: true});
            });
        }
    },

    updateDocumentStatus: async function ({commit}, data) {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/client/v2/updateStatus/',
            method: 'POST',
            data
        }).then((resp) => {
            commit('GUEST_DOCUMENTS_TAB_DATA', resp.data);
            commit('SET_GUEST_DOCUMENT', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    deleteDocument: async function ({commit, dispatch}, data) {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/v2/guest-image-delete',
            method: 'POST',
            data
        }).then((resp) => {
            dispatch('fetchGuestDocuments', data.booking_id);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    saveDocument: async ({commit, dispatch}, data) => {

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
                toastr.success(resp.data.message);
                commit('HIDE_LOADER', null, {root: true});
                dispatch('fetchGuestDocuments', data.get('booking_id'));
            } else {
                toastr.error(resp.data.message);
                commit('HIDE_LOADER', null, {root: true});
            }
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
            commit('SHOW_UPLOAD_DOCUMENT_ERRORS', {error_message, error_status});

            commit('HIDE_LOADER', null, {root: true});
        });
    },



    applyPayment: async ({state, commit, dispatch}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/client/v2/pay-now-uri',
            method: 'POST',
            data: {'transaction_init_id': data.t_id}
        }).then((resp) => {
            //console.log({'api' : resp.data});
            if (resp.data.status) {
                dispatch(data.pms_prefix+ '/fetchBookingDetail', data.booking_id, {root: true});
                dispatch('fetchActivityLogs', additional_charge.booking_info_id);
                toastr.success(resp.data.message);
                commit('HIDE_LOADER', null, {root: true});
            } else {
                toastr.error(resp.data.message);
                commit('HIDE_LOADER', null, {root: true});
            }

        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    applyAuth: async ({state, commit, dispatch}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: "/client/v2/pay-now-auth",
            method: 'POST',
            data: {
                'cc_auth_id': data.a_id
            }
        }).then((resp) => {
            if (resp.data.status == true && resp.data.status_code == 200) {
                dispatch(data.pms_prifix+ '/fetchBookingDetail', data.booking_id, {root: true});
                commit('HIDE_LOADER', null, {root: true});
                toastr.success(resp.data.message);
            } else {
                commit('HIDE_LOADER', null, {root: true});
                toastr.error(resp.data.message);
            }
        });
    },

    booking_id_action_chat: async ({commit}, payload) => {
        commit('BOOKIN_ID_ACTION_CHAT', payload);
    },

    canAddSyncTime: async ({commit, dispatch}) => {
        axios({
            url: "/client/v2/can-sync-booking",
            method: 'POST',
        }).then((resp) => {
            commit('CAN_SYNC_BOOKING', resp.data.status);
        }).catch((err) => {
            console.log(err);
        });
    },

    saveSyncTime: async ({commit, dispatch}, data) => {
        commit('SHOW_LOADER', null, {root: true});
        axios({
            url: "/client/v2/save-booking-sync-time",
            method: 'POST',
            data: data,
        }).then((resp) => {
            if (resp.data.status) {
                commit('CAN_SYNC_BOOKING', false);
                toastr.success(resp.data.message);
                document.querySelector('#sync-booking-modal-dismiss').click(); //
                swal.mixin({
                    title: resp.data.message,
                    //text: resp.data.message,
                    showConfirmButton: true,
                    backdrop: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                }).fire();
            } else {
                toastr.error(resp.data.message);
            }
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

};

export default general_root_actions;