<template>
    <div>
        <div class="card-section booking-card-payment-status"
             v-if="booking_detail.payment_status.length != 0 && isBookingCapableForPayments(booking_detail.capabilities)">
            <div class="card-section-title">Payment Schedule</div>
            <div class="booking-card-status-grid">
                <div class="status-grid-item" v-for="(ps , index) in booking_detail.payment_status">
                    <div :class="ps.box_class" class="grid-item-content">
                        <!--                            <div class="grid-item-icon"><i class="fas fa-exclamation-circle"></i></div>-->
                        <div class="grid-item-title">
                            {{ps.title}} <small style="font-size: 0.70rem; color:#486581"> #{{ps.id}}</small>
                            <span>{{ ps.amount }}  <!-- 12300.98 | numeral('0,0.00') --></span>
                        </div>
                        <div class="status-grid-item-state">
                            <i :class="ps.icon"></i>
                            <span :class="ps.status_class" class="badge">{{ps.status}} </span>
                            <span class="small text-muted ml-1">{{ps.date}}</span>
                        </div>
                        <div class="grid-item-action"
                             v-if="show_drop_down(ps, (booking_detail.total_charged - booking_detail.total_refunded))">
                            <div class="dropdown dropdown-sm ml-auto ml-md-0">
                                <a aria-expanded="false" aria-haspopup="true" class="btn btn-xs dropdown-toggle"
                                   data-toggle="dropdown" href="#" role="button"></a>
                                <div :id="'drop-down-actions'+booking_id" aria-labelledby="moreMenu"
                                     class="dropdown-menu dropdown-menu-right">
                                    <a
                                            @click.prevent="applyPayment(ps.id)"
                                            class="dropdown-item"
                                            href="#"
                                            v-if="isValidToShowOption('apply-payment', ps)"
                                    >Charge Now</a>
                                    <a
                                            @click.prevent="makeBookingIdReactiveForRefund(booking_id, (booking_detail.total_charged - booking_detail.total_refunded))"
                                            class="dropdown-item"
                                            data-target="#refund_amount"
                                            data-toggle="modal"
                                            href="#"
                                            v-if="isValidToShowOption('refund', ps, (booking_detail.total_charged - booking_detail.total_refunded))"
                                    >Refund</a>
                                    <a
                                            @click.prevent="reduceAmount(ps.id, ps.amount)"
                                            class="dropdown-item"
                                            data-target="#reduce_amount_modal"
                                            data-toggle="modal"
                                            href="#"
                                            v-if="isValidToShowOption('reduce-amount', ps)"
                                    >Change amount</a>
                                    <a
                                            @click.prevent="markAsPaid(ps.id, booking_id)"
                                            class="dropdown-item"
                                            href="#"
                                            v-if="isValidToShowOption('mark-as-paid', ps)"
                                    >Mark as Paid</a>
                                    <a
                                            @click.prevent="manuallyVoidTransaction(ps.id, booking_id)"
                                            class="dropdown-item"
                                            href="#"
                                            v-if="isValidToShowOption('manually-void-payment', ps)"
                                    >Void</a>

                                    <a
                                            @click.prevent="applyAuth(ps.id)"
                                            class="dropdown-item"
                                            href="#"
                                            v-if="isValidToShowOption('apply-auth', ps)"
                                    >Apply Auth</a>
                                    <a
                                            @click.prevent="capture(ps.id)"
                                            class="dropdown-item"
                                            href="#"
                                            v-if="isValidToShowOption('capture-auth', ps)"
                                    >Capture</a>
                                    <a
                                            @click.prevent="voidAuth(ps.id, booking_id)"
                                            class="dropdown-item"
                                            href="#"
                                            v-if="isValidToShowOption('manually-void-auth', ps)"
                                    >Void</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['booking_id']
    }
</script>