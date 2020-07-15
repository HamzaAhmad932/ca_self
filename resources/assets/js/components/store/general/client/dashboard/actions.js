let actions = {

    fetchUpcomingArrivals: async ({commit}) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/client/v2/get-upcoming-arrivals',
            method: 'GET'
        }).then((resp) => {
            //console.log({'api' : resp.data});
            commit('FETCH_UPCOMING_ARRIVALS', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            //console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    fetchDashboardAnalytics: async ({commit}) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/client/v2/get-dashboard-analytics',
            method: 'GET'
        }).then((resp) => {
            //console.log({'api' : resp.data});
            commit('FETCH_DASHBOARD_ANALYTICS', resp.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            //console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });

    },

    fetchNotificationsData: ({commit}, notification_shown) => {
        axios({
            url: '/client/v2/communicationNotifyAlerts',
            method: 'POST',
            data: {'notification_shown': notification_shown},
        }).then((resp) => {
            commit('FETCH_NOTIFICATIONS_DATA', resp.data);
        }).catch((err) => {
            console.log(err);
        });
    },

    alertActionPerformed: ({commit}, params) => {
        var alert_id = $(params.event.target).data('alert-id');
        axios({
            url: '/client/v2/alert-action-performed',
            method: 'POST',
            data: {'alert_id': alert_id, 'notification_shown': params.notification_shown},
        }).then((resp) => {
            Vue.$toast.open({
                message: 'Marked read successfully.',
                duration: 3000,
                type: 'success',
                position: 'top-right',
            });
            commit('ALERT_ACTION_PERFORMED', resp.data);
        }).catch((err) => {
            console.log(err);
        });
    }
};

export default actions;
