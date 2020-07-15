let mutations = {

    GET_PROPERTIES_WITH_ROOMS(state, data) {
        state.client_list_select_properties.properties_with_rooms=[];
        $.each(data.data, function (key, value) {
            data.data[key].all_rooms= (value.all_rooms_available? [{"name":"All Rentals","code":0}].concat(value.all_rooms) : value.all_rooms);
        });
        return state.client_list_select_properties.properties_with_rooms = data.data;
    },
};

export default mutations;
