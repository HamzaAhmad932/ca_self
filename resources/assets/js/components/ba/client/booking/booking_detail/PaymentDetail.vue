<template>
    <div>

        <additional-charge-modal calling_id="additional_charge_modal"></additional-charge-modal>

        <refund-amount-modal calling_id="refund_amount"></refund-amount-modal>

        <guest-credit-card module_prefix="ba"></guest-credit-card>

        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Payment Summary</h4>
            </div>
            <div class="card-inset-table">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th scope="row">Accommodation</th>
                            <td class="text-right">{{payments.payment_summary.charges}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="card-inset-table" v-if="isBookingCapableForManualPayment(payments.capabilities)">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th scope="row">Extras</th>
                            <td class="text-right">{{payments.payment_summary.extras}}</td>
                        </tr>
                        <tr v-for="ext in payments.payment_summary.extras_details"
                            v-if="payments.payment_summary.extras_details.length !== 0">
                            <th scope="row"><i class="fas fa-arrow-alt-circle-right ml-2"></i> {{ext.label}}</th>
                            <td class="text-right">{{ext.price}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <a @click.prevent="makeBookingIdReactiveForAdditionalCharge(booking_id, payments.is_payment_gateway_found, payments.is_credit_card_available)"
                       class="btn btn-sm btn-info mb-1 mt-1">Charge
                        Additional Payment</a>&nbsp;
                    <a @click.prevent="makeBookingIdReactiveForRefund(booking_id, (payments.total_charged - payments.total_refunded))" class="btn btn-sm btn-outline-danger mb-1 mt-1"
                       data-target="#refund_amount" data-toggle="modal"
                       v-if="payments.payment_summary.show_refund">Make
                        Refund</a></div>
                <div class="col-md-4">
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th scope="row">Total</th>
                                <td class="text-right">{{payments.payment_summary.sub_total}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Paid</th>
                                <td class="text-right">{{payments.payment_summary.paid}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Amount due</th>
                                <td class="text-right">{{payments.payment_summary.amount_due}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Payment Methods</h4>
                <button @click.prevent="makeBookingIdReactiveForCreditCard(booking_id, true)"
                        class="btn btn-secondary btn-sm helper-btn">Add <span
                        class="hidden-xs"> Payment Method</span></button>
            </div>
            <div class="row">
                <div class="col-md-6" v-for="cc in payments.cc_infos">
                    <div class="current-card">
                        <div class="text-muted mb-3"><i class="fas fa-credit-card"></i>
                            <!--                            <span class="fw-500 small ml-1">Default payment method</span>-->
                        </div>
                        <div class="row">
                            <div class="col">
                                <dl class="text-md mb-0">
                                    <dt>Card Number</dt>
                                    <dd>
                                        ****
                                        ****
                                        ****
                                        {{cc.cc_last_4_digit}}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <dl class="text-md mb-0">
                                    <dt>Name</dt>
                                    <dd>{{cc.card_name}}</dd>
                                </dl>
                            </div>
                            <div class="col-sm-6">
                                <dl class="text-md mb-0">
                                    <dt>Expiry</dt>
                                    <dd>{{cc.cc_exp_month}}/{{cc.cc_exp_year}}</dd>
                                </dl>
                            </div>
                        </div>
                        <hr>
                        <div class="card-status" v-if="cc.is_vc == '1'">VC</div>
                    </div>
                </div>
            </div>
            <div class="row" style="margin: 0px">
            <div v-if="payments.cc_infos != undefined && payments.cc_infos.length == 0">Payment method not found.</div>
            </div>
        </div>
        <div class="mt-3 mb-4" v-if="payments.pending_payments.length !== 0">
            <div class="card-section-title">
                <h4>Pending Payments<span class="badge badge-danger">{{payments.pending_payments.length}}</span></h4>
            </div>
            <div class="table-responsive">
                <table class="table table-middle border-top-remove">
                    <tr v-for="pp in payments.pending_payments">
                        <td class="fw-500  text-left" style="width: 50%">{{pp.title}}</td>
                        <td class="text-right fw-500" style="width: 20%">{{pp.amount}}</td>
                        <td class="text-left" style="width: 27%"><span class="badge badge-warning"> <i :class="pp.icon"></i> {{pp.status+' '+pp.date}}</span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="mt-3 mb-4" v-if="payments.declined_payments.length !== 0">
            <div class="card-section-title">
                <h4>Declined Payments<span class="badge badge-warning">{{payments.declined_payments.length}}</span></h4>
            </div>
            <div class="table-responsive">
                <table class="table table-middle border-top-remove">
                    <tr v-for="pp in payments.declined_payments">
                        <td class="fw-500 text-left" style="width: 50%">{{pp.title}}</td>

                        <td class="text-right fw-500" style="width: 20%">{{pp.amount}}</td>
                        <td class="text-left" style="width: 27%"><span class="badge badge-danger" > <i :class="pp.icon"></i> {{pp.status+' '+pp.date}}</span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="mt-3 mb-4" v-if="payments.accepted_payments.length !== 0">
            <div class="card-section-title">
                <h4>Accepted Payments<span class="badge badge-success">{{payments.accepted_payments.length}}</span></h4>
            </div>
            <div class="table-responsive">
                <table class="table table-middle border-top-remove">
                    <tr v-for="pp in payments.accepted_payments">
                        <td class="fw-500 text-left" style="width:50%;">{{pp.title}}</td>
                        <td class="text-right fw-500" style="width:20%;">{{pp.amount}}</td>
                        <td class="text-left" style="width:27%;"><span class="badge badge-success" > <i :class="pp.icon"></i> {{pp.status+' '+pp.date}}</span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="mt-3 mb-4" v-if="payments.other_payments.length !== 0">
            <div class="card-section-title">
                <h4>Others<span class="badge badge-warning">{{payments.other_payments.length}}</span></h4>
            </div>
            <div class="table-responsive">
                <table class="table table-middle border-top-remove">
                    <tr v-for="pp in payments.other_payments">
                        <td class="fw-500 text-left" style="width: 50%">{{pp.title}}</td>
                        <td class="text-right fw-500" style="width:20%;">{{pp.amount}}</td>
                        <td class="text-left" style="width:27%;"><span class="badge badge-warning" > <i :class="pp.icon"></i> {{pp.status+' '+pp.date}}</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <button data-target="#additional_charge_modal" data-toggle="modal" id="trigger_additional_charge"
                style="display: none">hidden for additional charge modal
        </button>

        <button data-target="#guest_credit_card_modal" data-toggle="modal" id="trigger_credit_card"
                style="display: none">hidden for credit card modal
        </button>

    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';

    export default {
        name: 'Payments',
        props: ['booking_id'],
        mounted() {
            this.fetchPaymentsTabInformation(this.booking_id);
        },

        methods: {

            ...mapActions('ba/',[
                'fetchPaymentsTabInformation'
            ]),

            isBookingCapableForManualPayment(booking_capabilities) {
                return booking_capabilities['MANUAL_PAYMENTS'];
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

            makeBookingIdReactiveForAdditionalCharge(booking_id, is_payment_gateway_found, is_credit_card_available) {

                if (!is_payment_gateway_found) {
                    swal.fire({
                        title: "No payment gateway added!",
                        type: "warning",
                        html: '<p style="font-size: 0.95rem;">Click <a href="/client/v2/pms-setup-step-3">here</a> to add payment gateway.</p>',
                        // showCancelButton: !0,
                        confirmButtonText: "OK"
                    });
                    return;
                }
                if (!is_credit_card_available) {

                    swal.fire({
                        title: "Payment method not found.",
                        type: "warning",
                        html: '<p style="font-size: 0.95rem;">Please attach a Credit Card</p>',
                        // showCancelButton: !0,
                        confirmButtonText: "OK"
                    });
                    return;
                }

                $('#trigger_additional_charge').click();
                this.$store.dispatch('general/additionalChargeActiveId', booking_id);

            },

            makeBookingIdReactiveForRefund(booking_id, amount_valid_to_refund) {
                this.$store.dispatch('general/refundAmountActiveId', {
                    'booking_id': booking_id,
                    'amount_valid_to_refund': amount_valid_to_refund
                });
            },
        },

        computed: {
            ...mapState({
                payments: function (state) {
                    return state.ba.booking_detail.payments
                }
            })
        }
    }
</script>