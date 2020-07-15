let mutations = {

    FETCH_UPCOMING_ARRIVALS(state, data) {
        return state.upcoming_arrivals = data.data;
    },

    FETCH_DASHBOARD_ANALYTICS(state, data) {
        return state.analytics = data.data;
    },

    FETCH_NOTIFICATIONS_DATA(state, data) {
        state.notifications = data.data;
        return state;
    },
    ALERT_ACTION_PERFORMED(state, data) {
        state.notifications = data.data;
        return state;
    }
};
export default mutations;
