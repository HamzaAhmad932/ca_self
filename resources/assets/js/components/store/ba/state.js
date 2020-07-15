import booking from './client/booking/state';
import dashboard from './client/dashboard/state';
import pre_checkin from './guest/pre_checkin/state';
import guest_portal from './guest/guest_portal/state';
import booking_detail from './client/booking_detail/state';

let module_state = {
    dashboard,
    booking,
    booking_detail,
    pre_checkin,
    guest_portal
};


export default module_state;