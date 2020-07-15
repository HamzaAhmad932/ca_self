let actions = {

    fetchUpcomingArrivals: async ({commit}) => {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: '/client/v2/ba/get-upcoming-arrivals',
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
            url: '/client/v2/ba/sget-dashboard-analytics',
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
};

export default actions;
