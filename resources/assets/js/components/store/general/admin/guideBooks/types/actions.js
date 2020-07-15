let  actions={
    getGuideBooksTypesLists : async ({state, commit})=> {
        commit('SHOW_LOADER', null, {root : true});
        await axios({
            url : '/admin/get-guide-book-types-list',
            method : 'POST',
            data: {filters :state.filters},
        }).then((response)=>{

            commit('SET_GUIDE_BOOKS_TYPES_LISTS', response.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },

};

export default actions;
