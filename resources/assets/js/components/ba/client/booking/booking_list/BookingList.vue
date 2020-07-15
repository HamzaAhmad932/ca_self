<template>
    <div>
        <!----Modals Components----------->
        <chat-panel-right calling_id="chat_panel_right"></chat-panel-right>

        <additional-charge-modal calling_id="additional_charge_modal"></additional-charge-modal>

        <refund-amount-modal calling_id="refund_amount"></refund-amount-modal>

        <capture-security-deposit-amount-modal calling_id="capture_security_deposit_amount"></capture-security-deposit-amount-modal>

        <reduce-amount-modal calling_id="reduce_amount_modal"></reduce-amount-modal>

        <guest-id-upload></guest-id-upload>

        <guest-credit-card module_prefix="ba"></guest-credit-card>

        <!-------End Modal Components------>

        <!-- Booking Card-->
        <div
            class="booking-card single_booking_box"
            :class="booking.payment_status.box_class"
            v-for="(booking, index) in booking_list"
            v-if="booking.length !== 0"
        >
            <div :class="booking.booking_status_id == 0 ? 'cancelled' : ''" class="card-pane">
                <div class="for-booking-list-page-only-outer">
                    <div class="row no-gutters for-booking-list-page-only-inner">
                        <div class="col-1 col-style col-lg">
                            <div class="table-box-check">
                                <div>{{booking.pms_booking_id}}</div>
                                <div class="small">
                                    <!--<span class="label label-primary">--><!--</span>-->
                                    <p  title="PMS Status" style="cursor: pointer">{{ booking.booking_status }}</p>
                                    <p><a :href="'/client/v2/booking-detail/'+booking.id" target="_blank">Go to detail</a></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 col-style">
                            <div class="guest-info">
                                <!--<div class="guest-avatar"></div>-->
                                <div class="guest-name">{{booking.guest_name}}
                                    <div :class="booking.booking_status_id == 0 ? '' : booking.guest_identity.class" class="small">
                                        {{booking.guest_identity.message}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3 col-style">
                            <div class="booking-box-dates-mobile d-block d-lg-none">{{booking.check_in.month}}
                                {{booking.check_in.day}}, {{booking.check_in.year}}
                                <div class="small text-muted">Check-In</div>
                            </div>
                            <div class="booking-box-dates d-none d-lg-flex">
                                <div class="booking-box-checkin">
                                    <div class="small text-muted">{{booking.check_in.month}}</div>
                                    {{booking.check_in.day}}
                                    <div class="small text-muted">{{booking.check_in.year}}</div>
                                </div>
                                <div class="booking-box-duration">
                                    <div class="small">&nbsp;{{booking.stay_days}}</div>
                                    <!--<div class="small text-muted">{{booking.left_days}}</div>-->
                                </div>
                                <div class="booking-box-checkout">
                                    <div class="small text-muted">{{booking.check_out.month}}</div>
                                    {{booking.check_out.day}}
                                    <div class="small text-muted">{{booking.check_out.year}}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 col-style col-lg">
                            <div class="booking-box-deposit">{{booking.deposit}}
                                <div :class="booking.deposit_status.class" class="small">
                                    {{booking.deposit_status.deposit_status}}
                                </div>
                            </div>
                        </div>
                        <div class="col-2 col-style col-lg">
                            <div class="booking-box-amount">
                                <span :class="'total_amount_of_'+booking.id">{{booking.amount}}</span>
                                <div class="small text-muted">
                                    Balance <span :class="'balance_for_'+booking.id">{{booking.balance}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 col-style col-lg">
                            <div class="box-status">
                                <custom-popover :show_header="true" :header="booking.pms_booking_id">
                                    <span slot="trigger" :class="booking.payment_status.class" class="badge"><i :class="booking.payment_status.icon"></i> {{booking.payment_status.status}}</span>
                                    <div class="card-body" slot="cardBody">
                                        {{booking.payment_status.message}}
                                        <a class="btn btn-primary btn-sm" v-if="booking.payment_status.url !== ''" :href="booking.payment_status.url">
                                            {{booking.payment_status.url_button_text}}
                                        </a>
                                    </div>
                                </custom-popover>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a
                    :aria-controls="'id_'+booking.id"
                    :href="'#id_'+booking.id"
                    :id="booking.id"
                    @click.prevent="getBookingDetail($event)"
                    aria-expanded="false"
                    class="card-collapse collapsed"
                    data-open="true"
                    data-toggle="collapse"
                    role="button">
                <i :id="booking.id" class="fas fa-chevron-up" data-open="true"
                   style="display: block; height: 100%; width: 100%;"></i>
            </a>
            <ba-booking-list-detail :booking_detail="booking_detail[booking.id]" :booking="booking.payment_status" :booking_id="booking.id"></ba-booking-list-detail>

            <div class="booking-card-info">
                <div class="booking-card-info-property booking_card_room_info mb-1 mt-2">
                    <div class="d-block float-left overflow-hidden">
                        <div class="tooltip_wrapper tooltip_outter">
                            <div class="display-initials-wrapper">
                                <img :src="'/storage/uploads/booking_souce_logo/'+booking.logo"
                                     alt="Logo" v-if="booking.logo.length > 2"/>
                                <span class="initial_icon" v-else>{{ booking.logo }}</span>
                            </div>
                            <div class="tooltip_for_booking_source" :class="[booking.booking_source=='Booking.com' ? 'bdc_channel' : '' ]">
                                <span class="tooltip_text">{{ booking.booking_source }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-block float-left overflow-hidden booking_card_room_detail_portion"
                         style="margin-top:5px">
                        <div class="one_line">
                            <span class="single-line"> {{ booking.property.name | shortName }} - {{ booking.room.room_type | shortName }} {{ booking.room.unit_name}}</span>
                        </div>
                    </div>
                </div>
                <div class="booking-card-info-buttons ml-md-auto mr-1">
                    <custom-popover :show_header="true" :header="booking.upsell_orders.purchased_types">
                        <a slot="trigger" class="btn btn-xs" href="javascript:void(0)">
                            Upsell ({{booking.upsell_orders.total_upsell_items}})
                        </a>
                        <div class="card-body" slot="cardBody">
                            Purchased Upsells ({{booking.upsell_orders.total_upsell_items}}) <br />
                            {{ booking.upsell_orders.purchased_types }} <br />
                            Total amount {{booking.upsell_orders.total_amount}}<br />
                            {{ booking.upsell_general_setting_status ? '' :   " Add On Services (Upsell) not active for " + booking.booking_source }}
                            <a class="btn btn-primary btn-sm" v-if="booking.upsell_general_setting_status == ''" :href="booking.general_settings_url">
                                Settings
                            </a>
                        </div>
                    </custom-popover>

                    <a
                            @click.prevent="makeBookingIdReactiveForCreditCard(booking.id, booking.is_payment_gateway_found)"
                            class="btn btn-xs"
                    >
                        Guest Credit Card ({{booking.total_cc_added}})
                    </a>
                    <a
                            @click.prevent="makeBookingIdReactiveForUploadID(booking.id, booking.documents_required)"
                            class="btn btn-xs"
                    >
                        Guest Documents ({{booking.total_documents}})
                    </a>
                    <a
                            @click.prevent="makeBookingIdReactiveForCommunication(booking.id, booking.pms_booking_id, booking.chat_active)"
                            class="btn btn-xs chat-open"
                            data-target="#chat_panel_right"
                    >
                        Chat
                    </a>
                </div>
            </div>
        </div>

        <div class="booking-card payment-paid" v-if="booking_list.length == 0">
            <div class="card-pane">
                <div class="row no-gutters">
                    <div class="col-12">
                        <!-- Bookings will list here. -->
                        No booking found.
                    </div>
                </div>
            </div>
        </div>
        <a data-target="#chat_panel_right" ref="openChatPanel" style="display: none">-</a>
        <button data-target="#guest_credit_card_modal" data-toggle="modal" id="trigger_credit_card"
                style="display: none">hidden for credit card modal
        </button>
        <button data-target="#uploadGuestID" data-toggle="modal" id="trigger_guest_document" style="display: none">
            hidden for guest document modal
        </button>
    </div>
</template>

<script>

    import {mapState} from 'vuex';
    import CustomPopover from "../../../../general/client/reusables/CustomPopover";

    export default {
        props: ['booking_list', 'redirect_which_record', 'redirect_to_record', 'is_booking_list_page'],
        data() {
            return {};
        },
        components: {
            CustomPopover
        },
        mounted() {
            this.openChatPanel();
            this.redirectToRecord();
        },
        methods: {
            redirectToRecord() {
                if (this.redirect_to_record && this.redirect_which_record) {
                    var required_id = this.redirect_which_record;
                    setTimeout(function () {
                        //do manual click to open details of that booking
                        $("a#" + required_id)[0].click();

                        //now scroll to that booking
                        $([document.documentElement, document.body]).animate({
                            scrollTop: ($("a#" + required_id).offset().top) - 200
                        }, 2000);

                    }, 1800);
                }
            },
            openChatPanel() {
                if (window.location.hash.length > 0) {
                    let bookingHash = window.location.hash.replace('#', '');
                    if ((bookingHash.length > 0) && ((bookingHash !== '0') || (bookingHash != 0))) {
                        bookingHash = bookingHash.split('-');
                        this.$refs.openChatPanel.click();
                        this.makeBookingIdReactiveForCommunication(bookingHash[0], bookingHash[1], true);
                    }
                }
            },
            validBookingToShowByCheckingFilter(booking_info) {
                if ((this.is_booking_list_page !== undefined) && (this.is_booking_list_page === true))
                    return this.$parent.validBookingToShowByCheckingFilter(booking_info);
                else
                    return true;
            },
            getBookingDetail(e) {
                let self = this;
                let id = e.target.id;
                let door = e.target.dataset.open;
                if (door == 'true') {

                    this.$store.dispatch('ba/fetchBookingDetail', id);
                    e.target.dataset.open = 'false';
                } else {
                    e.target.dataset.open = 'true';
                }
            },
            makeBookingIdReactiveForCreditCard(booking_id, is_payment_gateway_found) {

                if (!is_payment_gateway_found) {
                    swal.fire({
                        title: "No payment gateway added!",
                        type: "warning",
                        html: '<p style="font-size: 0.95rem;">Click <a href="/client/v2/pms-setup-step-3">here</a> to add payment gateway.</p>',
                        // showCancelButton: !0,
                        confirmButtonText: "OK"
                    });
                } else {
                    $('#trigger_credit_card').click();
                    this.$store.dispatch('general/guestCreditCardActiveID', booking_id);
                }
            },
            makeBookingIdReactiveForUploadID(booking_id, is_enable) {

                if (!is_enable) {
                    swal.fire({
                        title: "Guest document feature is turned off for this channel!",
                        html: '<p style="font-size: 0.95rem;">You can enable this feature from <a href="/client/v2/online-check-in">here</a></p>',
                        showClass: {
                            popup: 'animated fadeInDown faster'
                        },
                        hideClass: {
                            popup: 'animated fadeOutUp faster'
                        },
                        type: "warning",
                        // showCancelButton: !0,
                        confirmButtonText: "OK"
                    });
                } else {
                    $('#trigger_guest_document').click();
                    this.$store.dispatch('general/guestUploadActiveID', booking_id);
                }
            },
            makeBookingIdReactiveForCommunication(booking_id, pms_booking_id, is_enable) {

                //console.log(is_enable);

                if (!is_enable) {
                    swal.fire({
                        title: "Guest Chat feature is disabled!",
                        html: '<p style="font-size: 0.95rem;">You can enable this feature from <a href="/client/v2/online-check-in">here</a></p>',
                        showClass: {
                            popup: 'animated fadeInDown faster'
                        },
                        hideClass: {
                            popup: 'animated fadeOutUp faster'
                        },
                        type: "warning",
                        // showCancelButton: !0,
                        confirmButtonText: "OK"
                    });
                } else {

                    let payload = {
                        booking_id,
                        pms_booking_id
                    };
                    this.$refs.openChatPanel.click();
                    this.$store.dispatch('general/booking_id_action_chat', payload);
                }
            },
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                booking_detail: (state) => {
                    return state.ba.booking.booking_detail;
                },
            })
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.replace(/([A-Z])/g, ' $1').trim();
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1)
            },
            shortName: function (value) {
                if (!value) {
                    return '';
                } else {
                    //return value.length;
                    if (value.length > 40) {
                        return value.substring(0, 37) + '...';
                    } else {
                        return value
                    }

                }
            },
        }
    }

</script>
<style>
    .booking-box-dates .booking-box-duration {
        flex: none !important;
        flex-grow: initial !important;
    }

    .cancelled {
        background: none !important;
        color: #869AB8 !important;
    }

    /* Tooltip text */
    .tool-tip .tooltiptext {

        visibility: hidden;
        width: 150px !important;
        word-break: break-all !important;
        /*overflow-wrap: break-word !important;*/
        /*overflow:visible;*/
        background-color: #000;
        padding: 10px;
        color: #fff;
        text-align: center;
        padding: 5px 0;
        border-radius: 6px;
        z-index: 10;
        bottom: 36%;
        left: 53.5%;
        margin-left: auto;
        opacity: 0;
        transition: opacity 0.3s;
        position: absolute;
    }

    /* Tooltip arrow */
    .tool-tip .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #000 transparent transparent transparent;
    }

    /* Show the tooltip text when you mouse over the tooltip container */
    .tool-tip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
</style>
