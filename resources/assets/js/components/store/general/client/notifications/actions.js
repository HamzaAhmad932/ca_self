let actions = {

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