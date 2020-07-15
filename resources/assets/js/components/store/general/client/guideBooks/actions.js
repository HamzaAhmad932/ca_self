import general from "../../index";

let  actions={
    testing(){
        alert('hello this is a testing method');
    },
    getGuideBookTypes:async ({commit})=>{
        commit('SHOW_LOADER', null, {root : true});
        await axios.get('/client/v2/guide-books-get-types').then((response)=>{
            commit('SET_GUIDE_BOOK_TYPES', response.data.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },
    addGuideBook:({commit},data)=>{
        commit('SHOW_LOADER', null, {root : true});
        let url='/client/v2/';
        let rest_form=true;
        if(data.serve_id!=0){
            url+='guide-books-update';
            rest_form=false;
        }else{
            url+='guide-books-save';
        }
       return new Promise((resolve,reject)=>{
           axios.post(url,data).then((response)=>{
               commit('HIDE_LOADER', null, {root : true});
               if(response.data.status){
                   toastr.success(response.data.message);
                   if(rest_form){
                       commit('RESET_FORM_DATA');
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
    loadGuideBookOldData : async ({state, commit}, serve_id)=> {
        commit('SHOW_LOADER', null, {root : true});
        if (serve_id > 0){
            axios.post('/client/v2/guide-books-old/',{"serve_id":serve_id}).then((response)=>{
                commit('SET_FORM_DATA', response.data.data);
                commit('HIDE_LOADER', null, {root : true});
            }).catch((err)=>{
                console.log(err.response.data.errors);
                if(err.response.status == 422) {
                    $.each(err.response.data.errors, function(key, value){
                        toastr.error(value[0]);
                    });
                }
                commit('HIDE_LOADER', null, {root : true});
            });
        } else {
            commit('RESET_UPSELL_FORM_DATA'); //TODO
        }
    },
    getGuideBookList : async ({state, commit})=> {
        commit('SHOW_LOADER', null, {root : true});
        await axios({
            url : '/client/v2/guide-books-all',
            method : 'POST',
            data: {filters :state.guideBook.filters},
        }).then((response)=>{

            commit('SET_LIST', response.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },
    updateGuideBookStatus : async ({state, commit}, data)=> {
        commit('SHOW_LOADER', null, {root : true});
        return new Promise(resolve => {
            axios({
                url : '/client/v2/guide-books-update-status',
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
