/*
*
* These components will compile under "client" chunck.
*
* */
const general_client_base_path = './components/general/client';
const ba_client_base_path = './components/ba/client';

Vue.component('pagination',() => import('laravel-vue-pagination' /* webpackChunkName: "js/client" */));
Vue.component('edit-full-client',() => import(general_client_base_path+ '/reusables/editFullClient.vue' /* webpackChunkName: "js/client" */));
Vue.component('edit-team-member',() => import(general_client_base_path+ '/reusables/editTeamMember.vue' /* webpackChunkName: "js/client" */));
Vue.component('notification-sidebar',() => import(general_client_base_path+ '/NotificationSidebar.vue' /* webpackChunkName: "js/client" */));
/** Account Setup Steps */
Vue.component('pms-setup-step1',() => import(general_client_base_path + '/pms_integration/pms_setup_step1.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('pms-setup-step2',() => import(general_client_base_path + '/pms_integration/pms_setup_step2.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('pms-setup-step3',() => import(general_client_base_path + '/pms_integration/pms_setup_step3.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('pms-setup-step4',() => import(general_client_base_path + '/pms_integration/pms_setup_step4.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('pms-setup-steps-navbar', () => import(general_client_base_path+ '/pms_integration/pms_setup_steps_navbar.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('loader',() => import(general_client_base_path+ '/Loader.vue' /* webpackChunkName: "js/client" */));
Vue.component('manageteam',() => import(general_client_base_path+ '/team/Manageteam.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('userLogs',() => import(general_client_base_path+ '/team/userLogs.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('guest-id-upload',() => import(general_client_base_path+ '/reusables/GuestDocumentUploadModalBox.vue' /* webpackChunkName: "js/client" */));
Vue.component('guest-credit-card',() => import(general_client_base_path+ '/reusables/GuestCreditCard.vue' /* webpackChunkName: "js/client" */));
Vue.component('refund-amount-modal',() => import(general_client_base_path+ '/reusables/RefundAmountModal.vue' /* webpackChunkName: "js/client" */));
Vue.component('capture-security-deposit-amount-modal',() => import(general_client_base_path+ '/reusables/CaptureSecurityDepositAmountModal.vue' /* webpackChunkName: "js/client" */));
Vue.component('client-booking-source-settings',() => import(general_client_base_path+ '/bookingSourceSetings/ClientBookingSourceSettings.vue' /* webpackChunkName: "js/client" */));
Vue.component('client-payment-gateway-settings',() => import(general_client_base_path+ '/paymentGatewaysSettings/ClientPaymentGatewaySettings.vue' /* webpackChunkName: "js/client" */));
Vue.component('additional-charge-modal',() => import(general_client_base_path+ '/reusables/AdditionalChargeModal.vue' /* webpackChunkName: "js/client" */));
Vue.component('reduce-amount-modal',() => import(general_client_base_path+ '/reusables/ReduceAmountModal.vue' /* webpackChunkName: "js/client" */));
Vue.component('general-settings',() => import(general_client_base_path+ '/settings/GeneralSetting.vue' /* webpackChunkName: "js/client" */));
Vue.component('preferences-template-var',() => import(general_client_base_path+ '/settings/PreferencesTemplateVar.vue' /* webpackChunkName: "js/client" */));
Vue.component('chat-panel-right',() => import(general_client_base_path+ '/chat/ChatPanelRight.vue' /* webpackChunkName: "js/client" */));
Vue.component('all-notifications',() => import(general_client_base_path+ '/allNotifications/AllNotifications.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('booking-channel-settings',() => import(general_client_base_path+ '/settings/BookingChannelSetting.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('notification-settings',() => import(general_client_base_path+ '/settings/NotificationSetting.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('phone-input',() => import(general_client_base_path+ '/reusables/phoneInput.vue' /* webpackChunkName: "js/client" */));
Vue.component('account-enable-disable-button',() => import(general_client_base_path+ '/pms_integration/AccountEnableDisableButton.vue' /* webpackChunkName: "js/client" */));
Vue.component('guest-experience-setting',() => import(general_client_base_path+ '/settings/GuestExperienceSetting.vue' /* webpackChunkName: "js/client_02" */));
/** Upsell Components*/
Vue.component('add-upsell',() => import(general_client_base_path+ '/upsell/AddUpsell.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('upsell-list-page',() => import(general_client_base_path+ '/upsell/UpsellListPage.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('upsell-order-list-page',() => import(general_client_base_path+ '/upsell/UpsellOrderListPage.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('upsell-types-add',() => import(general_client_base_path+ '/upsell/types/upsell-types-add.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('upsell-types-list-page',() => import(general_client_base_path+ '/upsell/types/upsell-types-list-page.vue' /* webpackChunkName: "js/client_02" */));
/** Term and Conditions Components*/
Vue.component('terms-and-conditions-add',() => import(general_client_base_path+ '/terms_and_conditions/terms-and-conditions-add.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('terms-and-conditions-list-page',() => import(general_client_base_path+ '/terms_and_conditions/terms-and-conditions-list-page.vue' /* webpackChunkName: "js/client_02" */));
/** GuideBooks Components*/
Vue.component('guide-books-add',() => import(general_client_base_path+ '/guideBooks/guide-books-add.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('guide-books-list-page',() => import(general_client_base_path+ '/guideBooks/guide-books-list-page.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('guide-books-types-add',() => import(general_client_base_path+ '/guideBooks/types/guide-books-types-add.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('guide-books-types-list-page',() => import(general_client_base_path+ '/guideBooks/types/guide-books-types-list-page.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('booking-sync-time-popup-modal',() => import(general_client_base_path+ '/booking/BookingSyncTimePopupModal.vue' /* webpackChunkName: "js/client" */));
Vue.component('email-types-nav',() => import(general_client_base_path+ '/emails/EmailTypesNav.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('cancel-bdc-booking-detail',() => import(general_client_base_path+ '/booking/CancelBDCBookingDetail.vue' /* webpackChunkName: "js/client" */));
Vue.component('stripe-add-card',() => import('./components/general/gatewayTerminals/StripeAddCard.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('dummy-add-card',() => import('./components/general/gatewayTerminals/DummyAddCard.vue' /* webpackChunkName: "js/client_02" */));

Vue.component('general-property-gateway-settings', () => import(general_client_base_path + '/properties/GatewaySettingDetails.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('general-payment-schedule',() => import(general_client_base_path+ '/booking/bookingList/PaymentSchedule.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('general-payment-summary',() => import(general_client_base_path+ '/booking/bookingList/PaymentSummary.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('general-guest-experience', ()=> import(general_client_base_path+ '/booking/booking_detail/GuestExperience.vue'/* webpackChunkName: "js/client_02" */));
Vue.component('general-documents-detail', ()=> import(general_client_base_path+ '/booking/booking_detail/DocumentDetail.vue'/* webpackChunkName: "js/client_02" */));
Vue.component('general-sent-email-detail', ()=> import(general_client_base_path+ '/booking/booking_detail/SentEmailDetail.vue'/* webpackChunkName: "js/client_02" */));
Vue.component('general-upsell-detail', ()=> import(general_client_base_path+ '/booking/booking_detail/UpsellDetail.vue'/* webpackChunkName: "js/client_02" */));
Vue.component('general-activity-log', ()=> import(general_client_base_path+ '/booking/booking_detail/ActivityLog.vue'/* webpackChunkName: "js/client_02" */));
Vue.component('general-payment-attempts-activity-log', ()=> import(general_client_base_path+ '/reusables/PaymentAttemptsActivityLog.vue'/* webpackChunkName: "js/client_02" */));


Vue.component('booking-detail-page',() => import(general_client_base_path+ '/booking/booking_detail/BookingDetailPage.vue' /* webpackChunkName: "js/client" */));

/** Booking Automation Components **/
Vue.component('ba-pms-setup-step5',() => import(ba_client_base_path + '/pms_integration/pms_setup_step5.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('ba-client-properties-list',() => import(ba_client_base_path+ '/properties/PropertiesList.vue' /* webpackChunkName: "js/client" */));
Vue.component('ba-booking-list-page',() => import(ba_client_base_path+ '/booking/booking_list/BookingListPage.vue' /* webpackChunkName: "js/client" */));
Vue.component('ba-booking-list-detail',() => import(ba_client_base_path+ '/booking/booking_list/BookingListDetail.vue' /* webpackChunkName: "js/client" */));
Vue.component('ba-dashboard',() => import(ba_client_base_path+ '/dashboard/Dashboard.vue' /* webpackChunkName: "js/client_03" */));
Vue.component('ba-preference-settings',() => import(ba_client_base_path+ '/preferences/PreferenceSettingPage.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('ba-pms-modification-settings',() => import(ba_client_base_path+ '/preferences/PmsModificationSetting.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('ba-booking-detail', ()=> import(ba_client_base_path+ '/booking/booking_detail/BookingDetails.vue' /* webpackChunkName: "js/client_02" */));
Vue.component('ba-payment-detail', ()=> import(ba_client_base_path+ '/booking/booking_detail/PaymentDetail.vue' /* webpackChunkName: "js/client_02" */));
