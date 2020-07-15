let actions = {
    getPropertiesWithRooms : async ({commit}, data)=>{
        commit('SHOW_LOADER', null, {root : true});
        await axios({
            url : '/client/v2/all-properties-with-rooms',
            method : 'POST',
            data: data,
        }).then((resp)=>{
            commit('GET_PROPERTIES_WITH_ROOMS', resp.data);
            commit('HIDE_LOADER', null, {root : true});
        }).catch((err)=>{
            console.log(err);
            commit('HIDE_LOADER', null, {root : true});
        });
    },

    getPropertiesWithRoomsForRequest: ({state}) => {
        let properties = [];
         $.each(state.client_list_select_properties.properties_with_rooms, function (key, value) {
            if(value.attach_status) {
                let attach_property = {id: value.id, attach_status: value.attach_status, attached_rooms: value.attached_rooms}
                properties.push(attach_property);
            }
        });
         return properties;
    },
    setPropertiesWithRoomsForRequest:({state})=>{
        let properties = [];
        $.each(state.client_list_select_properties.properties_with_rooms, function (key, value) {
            if(value.attach_status) {
                let attached_rooms = [];
                $.each(value.attached_rooms, function (key2, room) {
                    attached_rooms.push(room.code);
                });
                let attach_property = {id: value.id, attach_status: value.attach_status, attached_rooms: attached_rooms}
                properties.push(attach_property);
            }
        });
        return new Promise((resolve, reject) => {
            resolve(properties);
        });
    },
    resetProperties:({state})=>{
        $.each(state.properties_with_rooms, function (key, value) {
            state.client_list_select_properties.properties_with_rooms[key].attach_status=false;
            state.client_list_select_properties.properties_with_rooms[key].attached_rooms=[{"name":"All Rentals","code":0}];
        });
    }
};

export default actions;
