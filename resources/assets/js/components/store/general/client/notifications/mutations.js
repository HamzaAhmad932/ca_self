let mutations = {

    FETCH_NOTIFICATIONS_DATA(state, data) {
        state.notification = data.data;
        return state;
    },
    ALERT_ACTION_PERFORMED(state, data) {
        state.notification = data.data;
        return state;
    }
};

export default mutations;