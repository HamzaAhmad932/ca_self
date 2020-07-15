<template>
    <div>
        <div :id="'id_'+booking_id" class="booking-card-body collapse">
            <div v-if="booking_detail">
                <div class="booking-card-details">
                    <div>
                        <div class="small">Booking Date</div>
                        <span>{{booking_detail.booking_date}} </span>
                    </div>
                    <div style="word-wrap: break-word;max-width: 20%;min-width:20%;">
                        <div class="small">Guest Contact</div>
                        <span>{{booking_detail.guest_phone}}</span><br/><span>{{booking_detail.guest_email}}</span>
                    </div>
                    <div>
                        <div class="small">Arrival Time</div>
                        <span>{{booking_detail.arrival_time}}</span>
                    </div>
                    <div>
                        <div class="small">No. of Guests</div>
                        <span>{{booking_detail.guests}}</span>
                    </div>
                    <div>
                        <div class="small">Last Portal Visit</div>
                        <span>{{ booking_detail.last_seen_of_guest != null ? booking_detail.last_seen_of_guest : 'Not Visited' }}</span>
                    </div>
                    <div>
                        <div class="small">Payment Type</div>
                        <span v-if="booking.status!=='Not Supported'">{{ booking_detail.isvc}} </span>
                        <span v-else class="text-center">---</span>
                    </div>
                    <div>
                        <div class="dropdown dropdown-sm ml-auto ml-md-0"
                             v-if="isBookingCapableForGuestExperience(booking_detail.capabilities) && booking_detail.guest_experience">
                            <a aria-expanded="false" aria-haspopup="true" class="btn btn-xs dropdown-toggle" data-toggle="dropdown"
                               href="#" id="goto" role="button">Actions</a>
                            <div aria-labelledby="goto" class="dropdown-menu dropdown-menu-right">
                                <a :href="booking_detail.routes.guest_portal" class="dropdown-item" target="_blank">Guest
                                    portal <i class="fas fa-external-link-alt"></i></a>
                                <a :href="booking_detail.routes.pre_checkin" class="dropdown-item" target="_blank">Pre
                                    check-in wizard <i class="fas fa-external-link-alt"></i></a>
                                <!--                            <a class="dropdown-item" target="_blank" :href="booking_detail.routes.guest_portal_1">Guest portal 1 <i class="fas fa-external-link-alt"></i></a>-->
                                <!--                            <a class="dropdown-item" target="_blank" :href="booking_detail.routes.pre_checkin_1">Pre check-in 1 <i class="fas fa-external-link-alt"></i></a>-->
                                <a @click="resendPreCheckinWizardEmail(booking_detail.id)"
                                   class="dropdown-item" href="JavaScript:void(0);"
                                   v-if="(booking_detail.booking_status != 0) && (booking_detail.booking_exp != 0) && (booking_detail.pre_checkin_completed == false)">Resend Pre Check-in Email</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Payment Schedule Section-->
                <general-payment-schedule :booking_detail="booking_detail"
                                          :booking="booking"
                                          :pms_prefix="'ba'"
                                          :booking_id="booking_id">
                </general-payment-schedule>

                <!--Payment Summary Section-->
                <general-payment-summary :booking_id="booking_id"
                                         :booking_detail="booking_detail"
                                         :pms_prefix="'ba'"
                                         :booking="booking">
                </general-payment-summary>

                <div class="card-section" v-if="isBookingCapableForPayments(booking_detail.capabilities)">
                    <PaymentAttemptsActivityLog :booking_id="booking_id" ></PaymentAttemptsActivityLog>
                </div>
            </div>
        </div>

        <button data-target="#additional_charge_modal" data-toggle="modal" id="trigger_additional_charge"
                style="display: none">hidden for additional charge modal
        </button>
    </div>
</template>
<script>
    import CustomPopover from "../../../../general/client/reusables/CustomPopover";
    import PaymentAttemptsActivityLog from "../../../../general/client/reusables/PaymentAttemptsActivityLog";

    export default {
        props: ['booking_id', 'booking_detail','booking'],
        components: {
            CustomPopover,
            PaymentAttemptsActivityLog
        },
        methods: {
            resendPreCheckinWizardEmail(id) {
                self = this;
                self.$store.commit('SHOW_LOADER', null, {root: true});
                axios({
                    url: "/client/v2/resend-pre-checkin-wizard-email/",
                    method: 'POST',
                    data: {id: id},
                }).then((resp) => {
                    if (resp.data.status_code == 200) {
                        self.$store.commit('HIDE_LOADER', null, {root: true});
                        toastr.success(resp.data.message);
                    } else if (resp.data.status_code == 404) {
                        self.$store.commit('HIDE_LOADER', null, {root: true});
                        toastr.error(resp.data.message);
                    }
                });
            },

            isBookingCapableForPayments(booking_capabilities) {
                return booking_capabilities['AUTO_PAYMENTS']
                    || booking_capabilities['MANUAL_PAYMENTS']
                    || booking_capabilities['SECURITY_DEPOSIT'];

            },

            isBookingCapableForGuestExperience(booking_capabilities) {
                return booking_capabilities['GUEST_EXPERIENCE'];
            }
        }
    }
</script>
<style>
    .span-inline {
        display: inline !important;
    }

    #extras_sub {
        margin: 0 !important;
    }

    #extras_sub td {
        padding: 0 !important;
    }

    .container-shrink {
        width: 100%;
        padding-right: 25px;
        padding-left: 25px;
        margin-right: auto;
        margin-left: auto;
    }

    .top-sticky {
        padding-top: 0 !important;
    }

    .card-section-sub-title {
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.25rem;
        padding-bottom: 0.5rem;
        position: relative;
    }
</style>
