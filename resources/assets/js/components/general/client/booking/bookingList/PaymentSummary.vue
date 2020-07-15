<template>
    <div class="card-section booking-card-payment-status"
         v-if="isBookingCapableForPayments(booking_detail.capabilities)">
        <div class="card-section-title">Payment Summary</div>
        <div class="container-shrink">
            <div class="table-responsive">
                <table class="table table-borderless payment-status-table">
                    <tr style="border: 1px solid #ddd;">
                        <td>
                            <div class="card-section-sub-title">Accommodation</div>
                        </td>
                        <td class="text-right"></td>
                        <td>
                            <div class="small text-right">{{booking_detail.payment_summary.charges}}</div>
                        </td>
                    </tr>
                    <!--                            <tr><td><hr></td></tr>-->
                    <tr style="border: 1px solid #ddd;">
                        <td>
                            <div class="card-section-sub-title"
                                 v-if="isBookingCapableForManualPayment(booking_detail.capabilities)">
                                Extras
                                <!--                                <a href="#" class="btn btn-xs">Update Card</a>-->
                                <a @click.prevent="makeBookingIdReactiveForAdditionalCharge(booking_id, booking_detail.is_payment_gateway_found, booking_detail.is_credit_card_available)"
                                   class="btn btn-xs"><i class="fas fa-plus"></i> Additional
                                    Charge</a>
                            </div>
                        </td>
                        <td class="text-right"></td>
                        <td>
                            <div class="small text-right">{{booking_detail.payment_summary.extras}}</div>
                        </td>
                    </tr>
                    <tr style="border: 1px solid #ddd;"
                        v-for="ext in booking_detail.payment_summary.extras_details"
                        v-if="booking_detail.payment_summary.extras_details.length !== 0">
                        <td colspan="3">
                            <table class="table table-borderless payment-status-table" id="extras_sub">
                                <tr>
                                    <td class="small">
                                        <i class="fas fa-level-up-alt fa-rotate-90"></i> &nbsp;{{ext.label}}
                                    </td>
                                    <td class="small text-right">{{ext.price}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td></td>
                        <td class="text-right">Total</td>
                        <td>
                            <div class="small text-right">{{booking_detail.payment_summary.sub_total}}</div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-right top-sticky">Paid</td>
                        <td class="top-sticky">
                            <div class="small text-right">
                                <a @click.prevent="makeBookingIdReactiveForRefund(booking_id, (booking_detail.total_charged - booking_detail.total_refunded))" class="btn btn-xs ml-1"
                                   data-target="#refund_amount" data-toggle="modal" href="#" title="Refund Amount"
                                   v-if="booking_detail.payment_summary.show_refund"><i
                                        class="icon-border-sm fas fa-reply"></i></a>
                                {{booking_detail.payment_summary.paid}}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-right top-sticky">Amount due</td>
                        <td class="top-sticky">
                            <div class="small text-right">{{booking_detail.payment_summary.amount_due}}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <button data-target="#additional_charge_modal" data-toggle="modal" id="trigger_additional_charge"
                style="display: none">hidden for additional charge modal
        </button>
    </div>

</template>

<script>
    export default {
        props: ['booking_id', 'booking_detail','booking', 'pms_prefix'],
        mounted() {

        },
        methods: {
            makeBookingIdReactiveForRefund(booking_id, amount_valid_to_refund, transaction_id = 0,) {
                this.$store.dispatch('general/refundAmountActiveId', {
                    'booking_id': booking_id,
                    'transaction_id': transaction_id,
                    'amount_valid_to_refund': amount_valid_to_refund
                });
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

            isBookingCapableForPayments(booking_capabilities) {
                return booking_capabilities['AUTO_PAYMENTS']
                    || booking_capabilities['MANUAL_PAYMENTS']
                    || booking_capabilities['SECURITY_DEPOSIT'];

            },

            isBookingCapableForManualPayment(booking_capabilities) {
                return booking_capabilities['MANUAL_PAYMENTS'];
            }
        }
    }
</script>

<style scoped>

</style>