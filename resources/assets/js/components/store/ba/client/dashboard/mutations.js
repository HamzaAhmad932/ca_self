let mutations = {

    FETCH_UPCOMING_ARRIVALS(state, data) {
        return state.dashboard.upcoming_arrivals = data.data;
    },

    FETCH_DASHBOARD_ANALYTICS(state, data) {
        return state.dashboard.analytics = data.data;
    },
};
export default mutations;
