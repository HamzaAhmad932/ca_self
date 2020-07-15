import booking from './client/booking/actions';
import dashboard from './client/dashboard/actions';
import pre_checkin from './guest/pre_checkin/actions';
import guest_portal from './guest/guest_portal/actions';
import booking_detail from './client/booking_detail/actions';

let module_actions = {
    ...booking,
    ...dashboard,
    ...pre_checkin,
    ...guest_portal,
    ...booking_detail,
};


export default module_actions;