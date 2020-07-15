<template>
    <div class="card-section booking-card-payment-status"
         v-if="booking_detail.payment_status.length != 0 && isBookingCapableForPayments(booking_detail.capabilities)">
        <div class="card-section-title">Payment Schedule</div>
        <div class="booking-card-status-grid">
            <div class="status-grid-item" v-for="(ps , index) in booking_detail.payment_status">
                <div :class="ps.box_class" class="grid-item-content">
                    <!--<div class="grid-item-icon"><i class="fas fa-exclamation-circle"></i></div>-->
                    <div class="grid-item-title">
                        {{ps.title}} <small style="font-size: 0.70rem; color:#486581"> #{{ps.id}}</small>
                        <span>{{ ps.amount }}  <!-- 12300.98 | numeral('0,0.00') --></span>
                    </div>
                    <div slot="trigger" class="status-grid-item-state" v-if="ps.title != 'Security Deposit Authorization'">
                                    <span :class="ps.status_class" class="badge">
                                        <i :class="ps.icon"></i>
                                        {{ps.status}}
                                    </span>
                        <span class="small text-muted ml-1">{{ps.date}}</span>
                    </div>
                    <custom-popover :show_header="false" :header="ps.id" v-if="ps.title == 'Security Deposit Authorization'">
                        <div slot="trigger" class="status-grid-item-state">
                                        <span :class="ps.status_class" class="badge">
                                            <i :class="ps.icon"></i>
                                            {{ps.status}}
                                        </span>
                            <span class="small text-muted ml-1">{{ps.date}}</span>
                        </div>
                        <div class="card-body" slot="cardBody">
                            {{booking_detail.total_captured_amount}} was captured <br />
                            {{booking_detail.total_captured_refund_amount}} was refund
                        </div>
                    </custom-popover>
                    <div class="grid-item-action"
                         v-if="show_drop_down(ps, (booking_detail.total_charged - booking_detail.total_refunded))">
                        <div class="dropdown dropdown-sm ml-auto ml-md-0">
                            <a aria-expanded="false" aria-haspopup="true" class="btn btn-xs dropdown-toggle"
                               data-toggle="dropdown" href="#" role="button"></a>
                            <div :id="'drop-down-actions'+booking_id" aria-labelledby="moreMenu"
                                 class="dropdown-menu dropdown-menu-right">
                                <a @click.prevent="applyPayment(ps.id)" class="dropdown-item"
                                   href="#" v-if="isValidToShowOption('apply-payment', ps)">Charge Now</a>
                                <a @click.prevent="makeBookingIdReactiveForRefund(booking_id, (booking_detail.total_charged - booking_detail.total_refunded), ps.id)"
                                   class="dropdown-item" data-target="#refund_amount" data-toggle="modal"
                                   href="#"
                                   v-if="isValidToShowOption('refund', ps, (booking_detail.total_charged - booking_detail.total_refunded))">Refund</a>
                                <a @click.prevent="reduceAmount(ps.id, ps.amount)" class="dropdown-item"
                                   data-target="#reduce_amount_modal" data-toggle="modal"
                                   href="#" v-if="isValidToShowOption('reduce-amount', ps)">Change amount</a>
                                <a @click.prevent="markAsPaid(ps.id, booking_id)" class="dropdown-item"
                                   href="#" v-if="isValidToShowOption('mark-as-paid', ps)">
                                    Mark as Paid</a>
                                <a @click.prevent="manuallyVoidTransaction(ps.id, booking_id)" class="dropdown-item"
                                   href="#"
                                   v-if="isValidToShowOption('manually-void-payment', ps)">Void</a>

                                <a @click.prevent="applyAuth(ps.id)" class="dropdown-item"
                                   href="#" v-if="isValidToShowOption('apply-auth', ps)">Apply Auth</a>
                                <a @click.prevent="makeCCAuthIdReactiveForCapture(booking_id, ps.id, ps.amount)" class="dropdown-item"
                                   href="#" v-if="isValidToShowOption('capture-auth', ps)"
                                   data-target="#capture_security_deposit_amount" data-toggle="modal">Capture</a>
                                <a @click.prevent="voidAuth(ps.id, booking_id)" class="dropdown-item"
                                   href="#"
                                   v-if="isValidToShowOption('manually-void-auth', ps)">Void</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import CustomPopover from "../../../../general/client/reusables/CustomPopover";
    export default {
        props: ['booking_id', 'booking_detail','booking', 'pms_prefix'],
        mounted() {
            //
        },
        components: {
            CustomPopover
        },
        data() {
            return {
                transactionChecks: ['refund', 'mark-as-paid', 'manually-void-payment', 'apply-payment', 'reduce-amount'],
                authChecks: ['apply-auth', 'void-auth', 'capture-auth', 'manually-void-auth'],
            }
        },
        methods: {
            applyPayment(id) {
                this.$store.dispatch('general/applyPayment', {
                    t_id: id,
                    booking_id: this.booking_id,
                    pms_prefix: this.pms_prefix
                });
            },
            applyAuth(id) {
                let self = this;
                swal.fire({
                    title: "Are you sure you want to authorize the payment now?",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes"
                }).then(function (e) {
                    if (e.value == true) {
                        self.$store.dispatch('general/applyAuth', {
                            a_id: id,
                            booking_id: self.booking_id,
                            pms_prefix: this.pms_prefix
                        });
                    }
                });
            },
            makeCCAuthIdReactiveForCapture(booking_id, cc_auth_id, amount) {
                this.$store.dispatch('general/captureAmountActiveId', {
                    'booking_id' : booking_id,
                    'cc_auth_id': cc_auth_id,
                    'amount_valid_to_capture': parseFloat(amount.replace(/,/g, '').substring(1)),
                });
            },
            reduceAmount(transaction_id, current_amount) {
                let data = {
                    booking_id: this.booking_id,
                    tran_id: transaction_id,
                    current_amount,
                    pms_prefix: this.pms_prefix
                };
                this.$store.dispatch('general/reduceAmountData', data);
            },
            makeBookingIdReactiveForRefund(booking_id, amount_valid_to_refund, transaction_id = 0,) {
                this.$store.dispatch('general/refundAmountActiveId', {
                    'booking_id': booking_id,
                    'transaction_id': transaction_id,
                    'amount_valid_to_refund': amount_valid_to_refund
                });
            },
            markAsPaid(transaction_id, booking_id) {
                let self = this;
                swal.fire({
                    title: "Are you sure you want to Mark as Paid?",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, do it!"
                }).then(function (e) {
                    if (e.value == true) {
                        self.$store.dispatch('general/markAsPaid', {
                            'booking_info_id': booking_id,
                            'transaction_id': transaction_id,
                            pms_prefix: this.pms_prefix
                        });
                    }
                });
            },
            manuallyVoidTransaction(transaction_id, booking_id) {
                this.$store.dispatch('general/manuallyVoidTransaction', {
                    'booking_info_id': booking_id,
                    'transaction_id': transaction_id,
                    pms_prefix: this.pms_prefix
                });
            },
            voidAuth(transaction_id, booking_id) {
                //ddd
                let self = this;
                swal.fire({
                    title: "Are you sure you want to void authorized payment?",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes"
                }).then(function (e) {
                    if (e.value == true) {
                        self.$store.dispatch('general/voidAuth', {'booking_info_id': booking_id, 'cc_auth_id': transaction_id, pms_prefix: this.pms_prefix});
                    }
                });
            },

            isBookingCapableForPayments(booking_capabilities) {
                return booking_capabilities['AUTO_PAYMENTS']
                    || booking_capabilities['MANUAL_PAYMENTS']
                    || booking_capabilities['SECURITY_DEPOSIT'];

            },

            isValidToShowOption(optionType, transactionObject, amountAvailableToRefund = 0) {
                let isValid = false;
                switch (optionType) {
                    case 'refund':
                        isValid = (transactionObject.status == 'Accepted' && transactionObject.is_auth != true && transactionObject.title != "Refund") && (amountAvailableToRefund > 0);
                        break;
                    case 'mark-as-paid' :
                    case 'manually-void-payment':
                    case 'apply-payment':
                    case 'reduce-amount':
                        isValid = (transactionObject.title == 'Reservation Charge' && (transactionObject.status == 'Scheduled'
                            || transactionObject.status == 'Decline' || transactionObject.status == 'Declined' || transactionObject.status == 'Aborted'));

                        break;

                    /*** Auth ****/
                    case 'apply-auth':
                        isValid = (transactionObject.is_auth == true && (transactionObject.status == 'Scheduled'
                            || transactionObject.status == 'Declined'));
                        break;
                    case 'void-auth':
                        isValid = (transactionObject.is_auth == true && transactionObject.captured != '1'
                            && (transactionObject.status == 'Scheduled' || transactionObject.status == 'Declined' || transactionObject.status == 'Accepted'));
                        break;
                    case 'capture-auth':
                        isValid = (transactionObject.title == 'Security Deposit Authorization') && (transactionObject.is_auth == true
                            && transactionObject.status == 'Accepted'
                            && transactionObject.captured != '1');
                        break;
                    case 'manually-void-auth':
                        isValid = ((transactionObject.is_auth == true) && transactionObject.captured != '1' && (transactionObject.status != 'Voided'));
                        break;
                }
                return isValid;
            },

            show_drop_down(transactionObject, amountAvailableToRefund) {
                let showDropDown = false;
                let self = this;
                // transactionObject.is_auth
                if (transactionObject.is_auth) {
                    $.each(self.authChecks, function (key, optionType) {
                        if (self.isValidToShowOption(optionType, transactionObject, amountAvailableToRefund))
                            showDropDown = true;
                    })
                } else {
                    $.each(self.transactionChecks, function (key, optionType) {
                        if (self.isValidToShowOption(optionType, transactionObject, amountAvailableToRefund))
                            showDropDown = true;
                    })
                }
                return showDropDown;
            },
        }
    }
</script>