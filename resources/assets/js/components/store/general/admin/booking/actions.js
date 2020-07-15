let actions = {
    fetchAdminBookingList: async ({commit}, data) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/admin/bookings_data',
            method: 'POST',
            data
        }).then((resp) => {
            commit('FETCH_BOOKING_LIST', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchAdminBookingDetail: async ({commit}, booking_info_id) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/admin/get-booking-details/' + booking_info_id,
            method: 'GET'
        }).then((resp) => {
            if(typeof resp.data.data.status === 'undefined') {
                commit('FETCH_BOOKING_DETAILS', resp.data);
                commit('HIDE_LOADER', null, {root: true});
            } else {
                commit('HIDE_LOADER', null, {root: true});
                toastr.error(resp.data.data.message);
            }

        }).catch((err) => {
            console.log('in catch');
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchBookingTransactionsDetail: async ({commit}, booking_info_id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/admin/bookings/transactions-listing/' + booking_info_id,
            method: 'GET'
        }).then((resp) => {
            commit('BOOKING_TRANSACTION_DETAIL', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchBookingPropertyDetail: async ({commit}, booking_info_id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/admin/bookings/booking-property-listing/' + booking_info_id,
            method: 'GET'
        }).then((resp) => {
            commit('BOOKING_PROPERTY_DETAIL', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchBookingPaymentGatewayDetail: async ({commit}, booking_info_id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/admin/bookings/booking-payment-gateway-detail/' + booking_info_id,
            method: 'GET'
        }).then((resp) => {
            commit('BOOKING_PAYMENT_GATEWAY_DETAIL', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    fetchBookingCCInfoDetail: async ({commit}, booking_info_id) => {
        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/admin/bookings/booking-ccinfo-detail/' + booking_info_id,
            method: 'GET'
        }).then((resp) => {
            commit('BOOKING_CREDIT_CARD_INFO_DETAIL', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },

};

export default actions;
