let actions = {
    clientLoadDefaultEmails: async ({commit}, id) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios.get('/client/v2/client-get-default-emails/'+id).then((resp) => {
            commit('SET_DEFAULT_EMAILS_DATA', resp.data.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    clientLoadDefaultEmailsTypes: async ({commit}) => {
        commit('SHOW_LOADER', null, {root: true});
        await axios.get('/client/v2/client-get-email-types/').then((resp) => {
            commit('SET_EMAIL_TYPES', resp.data.data);
            commit('HIDE_LOADER', null, {root: true});
        }).catch((err) => {
            console.log(err);
            commit('HIDE_LOADER', null, {root: true});
        });
    },
    clientUpdateDefaultEmail:({commit},data) => {
        commit('SHOW_LOADER', null, {root: true});
        return new Promise((resolve, reject) => {
            axios.post('/client/v2/client-update-default-email',data).then((resp) => {
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
    revertToDefaultEmail:({commit},data) => {
        commit('SHOW_LOADER', null, {root: true});
        return new Promise((resolve, reject) => {
            axios.post('/client/v2/revert-to-default-email',data).then((resp) => {
                resolve(resp.data);
                toastr.success('Default Email Content Applied.');
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
