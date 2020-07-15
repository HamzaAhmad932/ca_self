
import admin_booking from './admin/booking/mutations';
import admintac from './admin/termAndConditions/mutations';
import adminGuideBook from './admin/guideBooks/mutations';
import adminGuideBookType from './admin/guideBooks/types/mutations';
import adminUpsells from './admin/upsells/mutations';
import adminUpsellType from './admin/upsells/types/mutations';
import tac from './client/termAndConditions/mutations'
import guideBook from './client/guideBooks/mutations';
import guideBookTypes  from  './client/guideBooks/types/mutations'
import upsell from './client/upsell/mutations';
import upsellTypes from './client/upsell/types/mutations';
import client_list_select_properties from './client/client_list_select_properties/mutations'
import emails_admin from "./admin/emails/mutations";
import adv from  "./admin/dynamic_variables/mutations";
import emails_client from "./client/emails/mutations";
import auth from './auth/mutations';
import team_member from './client/teammember/mutations';
import chat from './client/chat/mutations';
import pre_checkin from './guest/pre_checkin/mutations';
import guest_portal from './guest/guest_portal/mutation';
import booking_detail from './client/booking_detail/mutations';
import mutations from './general_root_mutations';
import notifications from './client/notifications/mutations';


let module_mutations = {
    ...mutations,

    ...admin_booking,
    ...admintac,
    ...adminGuideBook,
    ...adminGuideBookType,
    ...adminUpsells,
    ...adminUpsellType,
    ...team_member,
    ...tac,
    ...upsell,
    ...client_list_select_properties,
    ...upsellTypes,
    ...guideBook,
    ...guideBookTypes,
    ...emails_admin,
    ...emails_client,
    ...adv,//Admin Dynamic Variables,
    ...auth,
    ...chat,
    ...notifications,
    ...pre_checkin,
    ...guest_portal,
    ...booking_detail
};

export default module_mutations;