let mutations = {

    SET_DEFAULT_EMAILS_DATA(state,data) {
        state.emails_client.client_emails = data;
        return state;
    },
    SET_EMAIL_TYPES(state, data){
        state.emails_client.email_types = data;
        return state;
    }
};

export default mutations;
