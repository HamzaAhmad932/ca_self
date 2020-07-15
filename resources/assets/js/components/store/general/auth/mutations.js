let mutations = {

    SHOW_AUTH_ERRORS(state, {error_message, error_status, source_state}) {

        if (error_message !== {}) {
            state.auth[source_state].error_message = error_message;
        }
        if (error_status !== {}) {
            state.auth[source_state].error_status = error_status;
        }
        return state;
    },
};

export default mutations;