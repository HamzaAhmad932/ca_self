/*
*
* These components will compile under "admin" chunck.
*
* */
const admin_base_path = './components/general/admin';

Vue.component('admin-booking-list-page', ()=> import(admin_base_path + '/bookings/AdminBookingListPage.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('admin-booking-detail', ()=> import(admin_base_path + '/bookings/AdminBookingDetail.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('transaction-detail-modal', ()=> import(admin_base_path + '/reusables/TransactionDetailModal.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('booking-property-detail-modal', ()=> import(admin_base_path + '/reusables/BookingPropertyDetailModal.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('booking-payment-gateway-detail-modal', ()=> import(admin_base_path + '/reusables/BookingPaymentGatewayDetailModal.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('booking-ccinfo-detail-modal', ()=> import(admin_base_path + '/reusables/BookingCCInfoDetailModal.vue'/* webpackChunkName: "js/admin"*/));

//properties
Vue.component('property-list', ()=> import(admin_base_path + '/properties/PropertyList.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('property-detail', ()=> import(admin_base_path + '/properties/PropertyDetail.vue'/* webpackChunkName: "js/admin"*/));

Vue.component('emails', ()=> import(admin_base_path + '/emails/emails.vue'/* webpackChunkName: "js/admin"*/));

//Stripe
Vue.component('assign-plans-to-user', ()=> import(admin_base_path + '/stripeCommissionBilling/AssignPlansToUser.vue'/* webpackChunkName: "js/admin"*/));

//User Accounts
Vue.component('user-account-list', ()=> import(admin_base_path + '/userAccounts/UserAccountList.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('user-list', ()=> import(admin_base_path + '/userAccounts/UserList.vue'/* webpackChunkName: "js/admin"*/));

//Admin Listings
Vue.component('admin-list', ()=> import(admin_base_path + '/admins/AdminList.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('admin-admin-modal', ()=> import(admin_base_path + '/admins/AddAdminModal.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('edit-admin-modal', ()=> import(admin_base_path + '/admins/EditAdminModal.vue'/* webpackChunkName: "js/admin"*/));

//Terms And Conditions
Vue.component('term-list', ()=> import(admin_base_path + '/termsAndConditions/termAndConditionList.vue'/* webpackChunkName: "js/admin"*/));

//Guide Books
Vue.component('guide-book-list', ()=> import(admin_base_path + '/guideBooks/guideBookList.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('guide-book-type-list', ()=> import(admin_base_path + '/guideBooks/guideBookTypeList.vue'/* webpackChunkName: "js/admin"*/));

//Upsells
Vue.component('upsell-list', ()=> import(admin_base_path + '/upsells/upsellList.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('upsell-type-list', ()=> import(admin_base_path + '/upsells/upsellTypeList.vue'/* webpackChunkName: "js/admin"*/));
Vue.component('upsell-order-list', ()=> import(admin_base_path + '/upsells/upsellOrderList.vue'/* webpackChunkName: "js/admin"*/));

//Audits
Vue.component('audit-page', ()=> import(admin_base_path + '/audits/AuditPage.vue'/* webpackChunkName: "js/admin"*/));