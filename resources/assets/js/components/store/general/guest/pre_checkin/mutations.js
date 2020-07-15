let mutations = {

    // STEP_0_DETAIL(state, payload) {
    //     state.step_0 = payload.data;
    //     state.header = payload.header;
    //     state.meta = payload.meta;
    //     return state;
    // },

    SET_HEADER_META (state, payload){
        return state.pre_checkin.meta = {...state.pre_checkin.meta, ...payload.meta};
    },

    STEP_COMPLETED(state, payload) {
        state.pre_checkin.meta.next_link = payload.meta.next_link;
        state.pre_checkin.meta.is_completed = payload.meta.is_completed;
        return state;
    },

    STEP_1_DATA(state, payload) {
        return state.pre_checkin.step_1 = {...state.pre_checkin.step_1, ...payload};
    },

    UPDATE_META(state, payload) {
        return state.pre_checkin.meta = {...state.pre_checkin.meta, ...payload.meta};
    },

    STEP_2_DATA(state, payload) {
        return state.pre_checkin.step_2 = {...state.pre_checkin.step_2, ...payload};
    },

    STEP_3_DATA(state, payload) {

        state.pre_checkin.step_3.images = payload.step_3;
        state.pre_checkin.guest_images_status = payload.guest_images_status;
        return state;
    },

    CREDIT_CARD_STEP_DATA(state, payload) {

        return state.pre_checkin.credit_card_step = {...state.credit_card_step, ...payload};
    },

    SUMMARY_DATA(state, payload) {
        state.pre_checkin.summary = {...state.pre_checkin.summary, ...payload.data};
        state.pre_checkin.header = {...state.pre_checkin.header, ...payload.header};
        return state;
    },

    STEP_7_DATA(state, payload) {

        state.pre_checkin.step_7 = {...state.pre_checkin.step_7, ...payload};
        //state.header = {...state.header, ...payload.header};

        return state;
    },
    //
    //
    // STEP_1_COMPLETED(state, payload) {
    //
    //     state.step_1.next_link = payload.meta.next_link;
    //     state.step_1.is_completed = payload.meta.is_completed;
    //
    //     return state;
    // },
    //
    // STEP_2_COMPLETED(state, payload) {
    //
    //     state.pre_checkin.step_2.next_link = payload.meta.next_link;
    //     state.pre_checkin.step_2.is_completed = payload.meta.is_completed;
    //     return state;
    // },
    //
    // CREDIT_CARD_STEP_COMPLETED(state, payload) {
    //
    //     state.credit_card_step.next_link = payload.meta.next_link;
    //     state.credit_card_step.is_completed = payload.meta.is_completed;
    //     return state;
    // },
    //
    // STEP_7_COMPLETED(state, payload) {
    //
    //     state.step_7.next_link = payload.meta.next_link;
    //     state.step_7.is_completed = payload.meta.is_completed;
    //     return state;
    // },

    SHOW_STEP_1_ERRORS(state, {error_message, error_status}) {

        if (error_message !== {}) {
            state.pre_checkin.step_1.error_message = {...state.pre_checkin.step_1.error_message, ...error_message};
        }
        if (error_status !== {}) {
            state.pre_checkin.step_1.error_status = {...state.pre_checkin.step_1.error_status, ...error_status};
        }
        return state;
    },

    SHOW_STEP_2_ERRORS(state, {error_message, error_status}) {

        state.pre_checkin.step_2.error_message = {...state.pre_checkin.step_2.error_message, ...error_message};
        state.pre_checkin.step_2.error_status = {...state.pre_checkin.step_2.error_status, ...error_status};
        return state;
    },

    SHOW_CREDIT_CARD_STEP_ERRORS(state, {error_message, error_status}) {

        if (error_message !== {}) {
            state.pre_checkin.credit_card_step.error_message = error_message;
        }
        if (error_status !== {}) {
            state.pre_checkin.credit_card_step.error_status = error_status;
        }
        return state;
    },

    ADD_ON_SERVICES_DATA(state, payload) {
        state.pre_checkin.add_on_service = {...state.pre_checkin.add_on_service, ...payload};
        return state;
    },
    //
    // ADD_ON_SERVICES_STEP_COMPLETED(state, payload) {
    //
    //     state.add_on_service.next_link = payload.meta.next_link;
    //     state.add_on_service.is_completed = payload.meta.is_completed;
    //     return state;
    // },
    //
    // PRECHECKIN_COMPLETE(state, payload) {
    //
    //     state.summary.next_link = payload.meta.next_link;
    //     state.summary.is_completed = payload.meta.is_completed;
    //     return state;
    // },
    //
    adultsIncrement(state) {

        let previous_state = state;
        previous_state.pre_checkin.step_1.adults = parseInt(previous_state.pre_checkin.step_1.adults) + 1;
        let adult_count = parseInt(previous_state.pre_checkin.step_1.adults) || 0;
        let child_count = parseInt(previous_state.pre_checkin.step_1.childern) || 0;
        previous_state.pre_checkin.step_1.guests = adult_count + child_count;

        state = previous_state;
        return state;
    },

    adultsDecrement(state) {

        let new_state = state;
        new_state.pre_checkin.step_1.adults = parseInt(new_state.pre_checkin.step_1.adults) - 1 > -1 ? parseInt(new_state.pre_checkin.step_1.adults) - 1 : 0;
        let adult_count = parseInt(new_state.pre_checkin.step_1.adults) || 0;
        let child_count = parseInt(new_state.pre_checkin.step_1.childern) || 0;
        new_state.pre_checkin.step_1.guests = adult_count + child_count;

        return new_state;
    },

    childIncrement(state) {

        let previous_state = state;
        previous_state.pre_checkin.step_1.childern = parseInt(previous_state.pre_checkin.step_1.childern) + 1;
        let adult_count = parseInt(previous_state.pre_checkin.step_1.adults) || 0;
        let child_count = parseInt(previous_state.pre_checkin.step_1.childern) || 0;
        previous_state.pre_checkin.step_1.guests = adult_count + child_count;

        state = previous_state;
        return state;
    },
    childDecrement(state) {

        let new_state = state;
        new_state.pre_checkin.step_1.childern = parseInt(new_state.pre_checkin.step_1.childern) - 1 > -1 ? parseInt(new_state.pre_checkin.step_1.childern) - 1 : 0;
        let adult_count = parseInt(new_state.pre_checkin.step_1.adults) || 0;
        let child_count = parseInt(new_state.pre_checkin.step_1.childern) || 0;
        new_state.pre_checkin.step_1.guests = adult_count + child_count;

        return new_state;
    },

    inputGuest(state, {e, other}) {

        let new_state = state;
        let value = parseInt(Math.abs(e.target.value));
        let name = e.target.name;
        new_state.pre_checkin.step_1[name] = parseInt(value);//(value > -1) ? value : 0;
        let count_1 = parseInt(value) || 0;
        let count_2 = parseInt(new_state.pre_checkin.step_1[other]) || 0;
        new_state.pre_checkin.step_1.guests = parseInt(count_1) + parseInt(count_2);

        return new_state;
    },

    SHOW_3DS_MODAL_BOX(state){
        state.pre_checkin.credit_card_step._3ds_modal = true;
    },

    HIDE_3DS_MODAL_BOX(state){
        state.pre_checkin.credit_card_step._3ds_modal = false;
    },

    _3DS_PAYMENT_DETAIL(state, payload){
        state.pre_checkin._3ds = {...state.pre_checkin._3ds, ...payload.data};
    },
    //
    //
    // UPDATE_UPSELL_IN_CART(state, payload){
    //     return state.in_cart_upsells = {...state.in_cart_upsells, ...payload};
    // },
    //
    MODIFY_TOTAL_PRICE(state, index){
        let add_on = state.pre_checkin.add_on_service.data.available[index];
        let total_price = parseFloat(add_on.total_price);

        if(add_on.period_label.value == 2){
            total_price = (add_on.price_label.value == 2) ? (parseInt(add_on.days) * parseInt(add_on.guest_count) * add_on.price) : (parseInt(add_on.days) * parseFloat(add_on.price));
        }else{
            total_price = (add_on.price_label.value == 2) ? (parseInt(add_on.guest_count) * parseFloat(add_on.price)) : parseFloat(add_on.price);
        }
        add_on.total_price = total_price;
        return state.pre_checkin.add_on_service.data.available[index] = add_on;
    },

    UPDATE_IN_CART_STATUS(state, {index, event}){
        return state.pre_checkin.add_on_service.data.available[index].in_cart = event.target.checked;
    },

    SET_INCART_AMOUNT(state, total_in_cart_amount){

        state.pre_checkin.add_on_service.in_cart_due_amount = parseFloat(total_in_cart_amount);
        return state;
    },

    UPDATE_READ_WRITE_MODE(state, payload){
      return state.pre_checkin.meta.read_only_mode = payload.status;
    }
};

export default mutations;
