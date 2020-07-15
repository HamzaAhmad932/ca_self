let  actions={
    getUpsellsTypesLists : async ({state, commit})=> {
        commit('SHOW_LOADER', null, {root : true});
        await axios({
            url : '/admin/upsell-type-list',
            method : 'POST',
            data: {filters :state.filters},
        }).then((response)=>{

            commit('SET_UPSELLS_TYPES_LISTS', response.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },

};

export default actions;
