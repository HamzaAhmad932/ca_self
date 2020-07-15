let mutations = {

    FETCH_USER_LOGS(state, {payload, index}) {
        state.user_logs = {...state.user_logs, [index]: payload};
        return state;
    },


};

export default mutations;
