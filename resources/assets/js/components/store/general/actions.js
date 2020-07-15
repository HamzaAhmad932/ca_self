
import admin_booking from './admin/booking/actions';
import admintac from './admin/termAndConditions/actions';
import adminGuideBook from './admin/guideBooks/actions';
import adminGuideBookType from './admin/guideBooks/types/actions';
import adminUpsells from './admin/upsells/actions';
import adminUpsellType from './admin/upsells/types/actions';
import tac from './client/termAndConditions/actions'
import guideBook from './client/guideBooks/actions';
import guideBookTypes  from  './client/guideBooks/types/actions'
import upsell from './client/upsell/actions';
import upsellTypes from './client/upsell/types/actions';
import client_list_select_properties from './client/client_list_select_properties/actions'
import emails_admin from "./admin/emails/actions";
import adv from  "./admin/dynamic_variables/actions";
import emails_client from "./client/emails/actions";
import auth from './auth/actions';
import team_member from './client/teammember/actions';
import notification from './client/notifications/actions';
import chat from './client/chat/actions';
import pre_checkin from './guest/pre_checkin/actions';
import guest_portal from './guest/guest_portal/actions';
import booking_detail from './client/booking_detail/actions';
import actions from './general_root_actions';

let module_actions = {
    ...actions,
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
    ...notification,
    ...chat,
    ...pre_checkin,
    ...guest_portal,
    ...booking_detail
};

export default module_actions;