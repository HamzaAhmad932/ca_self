let actions = {
    loadDefaultEmails: async ({commit}) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios.get('/admin/get-default-emails').then((resp) => {
            commit('SET_DEFAULT_EMAILS_DATA', resp.data.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    updateDefaultEmail:({commit},data) => {
        commit('SHOW_LOADER', null, {root: true});
        return new Promise((resolve, reject) => {
            axios.post('/admin/update-default-email',data).then((resp) => {
                if(resp.data.status){
                    toastr.success(resp.data.message);
                }else {
                    console.log(resp.data.data);
                }
                commit('HIDE_LOADER', null, {root: true});
               }).catch((err) => {
                   commit('HIDE_LOADER', null, {root: true});
                   if (err.response.status == 422) {
                       resolve(err.response.data.errors);
                   }else{
                       console.log(err)
                   }
               });
         });
    },

};

export default actions;
