let  actions={
    addUpsellType:({commit},data)=>{
        commit('SHOW_LOADER', null, {root : true});
        let url='/client/v2/';
        let rest_form=true;
        if(data.serve_id!=0){
            url+='upsell-type-update';
            rest_form=false;
        }else{
            url+='upsell-type-save';
        }
       return new Promise((resolve,reject)=>{
           axios.post(url,data).then((response)=>{
               commit('HIDE_LOADER', null, {root : true});
               if(response.data.status){
                   toastr.success(response.data.message);
                   if(rest_form){
                       commit('RESET_GUIDE_BOOK_TYPE_FORM_DATA');
                   }
               }else{
                   toastr.error(response.data.message);
                   rest_form = false;
               }
               return resolve(rest_form);
           }).catch((err)=>{
               commit('HIDE_LOADER', null, {root : true});
               if(err.response.status == 422) {
                   resolve(err.response.data.errors);
               }
           });

       });
    },
    loadUpsellTypeOldData : async ({state, commit}, serve_id)=> {
        commit('SHOW_LOADER', null, {root : true});
        if (serve_id > 0){
            axios.post('/client/v2/upsell-type-old/',{"serve_id":serve_id}).then((response)=>{
                commit('SET_UPSELL_TYPE_FORM_DATA', response.data.data);
                commit('HIDE_LOADER', null, {root : true});
            }).catch((err)=>{
                commit('HIDE_LOADER', null, {root : true});
                console.log(err.response.data.errors);
                if(err.response.status == 422) {
                    $.each(err.response.data.errors, function(key, value){
                        toastr.error(value[0]);
                    });
                }

            });
        }
        commit('HIDE_LOADER', null, {root : true});
    },
    getUpsellTypesList : async ({state, commit})=> {
        commit('SHOW_LOADER', null, {root : true});
        await axios({
            url : '/client/v2/upsell-types-all',
            method : 'POST',
            data: {filters :state.upsellTypes.filters},
        }).then((response)=>{

            commit('SET_UPSELL_TYPES_LIST', response.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },
    updateUpsellTypeStatus : async ({state, commit}, data)=> {
        commit('SHOW_LOADER', null, {root : true});
        return new Promise(resolve => {
            axios({
                url : '/client/v2/upsell-type-update-status',
                method : 'POST',
                data: data,
            }).then((resp)=>{
                if (resp.data.status) {
                    toastr.success(resp.data.message);
                } else {
                    toastr.error(resp.data.message);
                }
                commit('HIDE_LOADER', null, {root : true});
                resolve(resp.data.status);
            }).catch((err)=>{
                console.log(err);
                commit('HIDE_LOADER', null, {root : true});
                resolve(false);
            });
        })
    },

};

export default actions;
