let  actions={
    getAllGuideBookTypes:async ({state, commit})=>{
        commit('SHOW_LOADER', null, {root : true});
        await axios.post('/admin/get-all-guide-books-types', {
            'user_account_id':state.filters.user_account_id
        }).then((response)=>{
            commit('SET_GUIDE_BOOK_TYPES', response.data.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },

    getAllGuideBooks : async ({state, commit})=> {
        commit('SHOW_LOADER', null, {root : true});
        await axios({
            url : '/admin/get-all-guide-books',
            method : 'POST',
            data: {filters :state.filters},
        }).then((response)=>{
            commit('SET_GUIDE_BOOK_LIST', response.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },

};

export default actions;
