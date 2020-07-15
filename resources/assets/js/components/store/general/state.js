
import admin_booking from './admin/booking/state';
import admintac from './admin/termAndConditions/state';
import adminGuideBook from './admin/guideBooks/state';
import adminGuideBookType from './admin/guideBooks/types/state';
import adminUpsells from './admin/upsells/state';
import adminUpsellType from './admin/upsells/types/state';
import tac from './client/termAndConditions/state'
import guideBook from './client/guideBooks/state';
import guideBookTypes  from  './client/guideBooks/types/state'
import upsell from './client/upsell/state';
import upsellTypes from './client/upsell/types/state';
import client_list_select_properties from './client/client_list_select_properties/state'
import emails_admin from "./admin/emails/state";
import adv from  "./admin/dynamic_variables/state";
import emails_client from "./client/emails/state";
import auth from './auth/state';
import team_member from './client/teammember/state';
import notification from './client/notifications/state';
import chat from './client/chat/state';
import pre_checkin from './guest/pre_checkin/state';
import guest_portal from './guest/guest_portal/state';
import booking_detail from './client/booking_detail/state';
import state from './general_root_state';

let module_state = {
    ...state,
    admin_booking,
    admintac,
    adminGuideBook,
    adminGuideBookType,
    adminUpsells,
    adminUpsellType,
    team_member,
    tac,
    upsell,
    client_list_select_properties,
    upsellTypes,
    guideBook,
    guideBookTypes,
    emails_admin,
    emails_client,
    adv,//Admin Dynamic Variables,
    auth,
    notification,
    chat,
    pre_checkin,
    guest_portal,
    booking_detail
};

export default module_state;