import booking from './client/booking/mutations';
import dashboard from './client/dashboard/mutations';
import pre_checkin from './guest/pre_checkin/mutations';
import guest_portal from './guest/guest_portal/mutations';
import booking_detail from './client/booking_detail/mutations';

let module_mutations = {
    ...booking,
    ...dashboard,
    ...pre_checkin,
    ...guest_portal,
    ...booking_detail,
};


export default module_mutations;