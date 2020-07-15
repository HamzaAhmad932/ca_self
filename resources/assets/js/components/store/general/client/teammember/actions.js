let actions = {

    fetchUserLogs: async ({commit}, {log_filter, user_id}) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios.post('/client/v2/v2user_log', {
            'filters': log_filter,
            user_id
        })
            .then((resp) => {
                commit('FETCH_USER_LOGS', {'payload': resp.data, 'index': user_id});
                commit('HIDE_LOADER', null, {root: true});
            }).catch((err) => {
                console.log(err);
                commit('HIDE_LOADER', null, {root: true});
            });
    },


};

export default actions;
