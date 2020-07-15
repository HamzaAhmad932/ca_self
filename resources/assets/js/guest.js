/*
*
* These components will compile under "guest" chunk.
*
* */
const ba_guest_base_path = './components/ba/guest';
const general_guest_base_path = './components/general/guest';

/*
* General registered components
* */
Vue.component('basic-info-step', ()=> import(general_guest_base_path + '/guest_pre_checkin/BasicInfoStep.vue' /* webpackChunkName: "js/guest" */));
Vue.component('arrival-info-step', ()=> import(general_guest_base_path + '/guest_pre_checkin/Arrival.vue' /* webpackChunkName: "js/guest" */));
Vue.component('verification-step', ()=> import(general_guest_base_path + '/guest_pre_checkin/Verification.vue' /* webpackChunkName: "js/guest" */));
Vue.component('add-on-services', ()=> import(general_guest_base_path + '/guest_pre_checkin/AddOnServices.vue' /* webpackChunkName: "js/guest" */));
Vue.component('pre-checkin-thank-you', ()=> import(general_guest_base_path + '/guest_pre_checkin/PreCheckInThankYou.vue' /* webpackChunkName: "js/guest" */));
Vue.component('guest-portal', ()=> import(general_guest_base_path + '/guest_portal/GuestPortal.vue' /* webpackChunkName: "js/guest" */));
Vue.component('guest-chat-panel', ()=> import(general_guest_base_path + '/reuseables/GuestChatPanel.vue' /* webpackChunkName: "js/guest" */));
Vue.component('property-guide-book-detail-model', ()=> import(general_guest_base_path + '/reuseables/PropertyGuideBookDetailModel.vue' /* webpackChunkName: "js/guest" */));
Vue.component('guest-header', ()=> import(general_guest_base_path + '/includes/Header.vue' /* webpackChunkName: "js/guest" */));
Vue.component('header-steps', ()=> import(general_guest_base_path + '/guest_pre_checkin/HeaderSteps.vue' /* webpackChunkName: "js/guest" */));
Vue.component('PreCheckinFooter', ()=> import(general_guest_base_path + '/guest_pre_checkin/PreCheckinFooter.vue' /* webpackChunkName: "js/guest" */));
Vue.component('signature-pad', ()=> import(general_guest_base_path + '/guest_pre_checkin/SignaturePad.vue' /* webpackChunkName: "js/guest" */));
Vue.component('photo-booth', ()=> import(general_guest_base_path + '/guest_pre_checkin/Photobooth.vue' /* webpackChunkName: "js/guest" */));
Vue.component('term-and-condation-popup-modal', ()=> import(general_guest_base_path + '/guest_pre_checkin/TermAndCondationPopupModal.vue' /* webpackChunkName: "js/guest" */));
Vue.component('read-only-mode', ()=> import(general_guest_base_path + '/guest_pre_checkin/ReadOnlyMode.vue' /* webpackChunkName: "js/guest" */));
Vue.component('pre-checkin-chat-button', ()=> import(general_guest_base_path + '/guest_pre_checkin/ChatButton.vue' /* webpackChunkName: "js/guest" */));
Vue.component('summary-step', ()=> import(general_guest_base_path + '/guest_pre_checkin/Summary.vue' /* webpackChunkName: "js/guest" */));
Vue.component('checkout-3ds',() => import(general_guest_base_path+ '/checkout/Checkout3DS.vue' /* webpackChunkName: "js/guest" */));
Vue.component('credit-card-step', ()=> import(general_guest_base_path + '/guest_pre_checkin/CreditCard.vue' /* webpackChunkName: "js/guest" */));
// Vue.component('stripe-add-card',() => import('./components/gatewayTerminals/StripeAddCard.vue' /* webpackChunkName: "js/guest" */));
// Vue.component('dummy-add-card',() => import('./components/gatewayTerminals/DummyAddCard.vue' /* webpackChunkName: "js/guest" */));
// Vue.component('guest-credit-card',() => import('./components/client/reusables/GuestCreditCard.vue' /* webpackChunkName: "js/guest" */));


/*
* Booking automation registered components
* */
Vue.component('guest-pre-checkin', ()=> import(ba_guest_base_path+ '/guest_pre_checkin/GuestPreCheckin.vue' /* webpackChunkName: "js/guest" */));
Vue.component('ba-precheckin-payment-summary', ()=> import(ba_guest_base_path + '/guest_pre_checkin/PaymentSummary.vue' /* webpackChunkName: "js/guest" */));