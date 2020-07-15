<template>
    <div>

        <div v-if="payment_summary.card && payment_summary.card.card_available && !payment_summary.card.card_missing && payment_summary.card.is_invalid_card"
             class="alert custom-alert  alert-mr">
            <div class="d-inline float-left icon-wrapper">
                <i class="fas fa-exclamation-triangle"> </i>
            </div>
            <span class="d-inline float-left">The credit card is declined, please update your card.</span>
        </div>

        <div v-if="payment_summary.card && payment_summary.card.card_available && payment_summary.card.is_payment_failed"
             class="alert custom-alert alert-mr">
            <div class="d-inline float-left icon-wrapper">
                <i class="fas fa-exclamation-triangle"> </i>
            </div>
            <span class="d-inline float-left">Payment failed, please update your credit card.</span>
        </div>

        <div v-if="payment_summary.show_payments">
            <div class="mb-4" style="z-index: 1; position: relative !important;" v-if="payment_summary.payments.length !== 0">
                <div class="card-section-title">
                    <h4>Payment Summary
                        <span class="badge default-badge">{{payment_summary.payments.length}}</span>
                    </h4>
                </div>
                <div class="table-responsive" style="overflow:visible">
                    <table class="table table-middle payment-summary-detail-table bg-white">
                        <tr class="default-row" v-for="pp in payment_summary.payments">
                            <td class="fw-500  text-left" style="width:50%">
                                <span class="d-inline-block float-left">
                                    {{pp.title}}
                                    <div v-if="payment_summary.card && payment_summary.card.sd_auth_present && pp.is_auth && pp.title=='Security Deposit Authorization'" class="d-inline-block guest-side-tool-tip-wrapper">
                                        <custom-popover :show_header="false" :header="pp.id">
                                            <i slot="trigger" class="fa fa-info-circle"></i>
                                            <div class="card-body" slot="cardBody" v-html="payment_summary.card.sd_msg"></div>
                                        </custom-popover>
                                    </div>
                                </span>
                            </td>
                            <td class="text-right default-text fw-500" style="width:25%">{{pp.amount}}</td>
                            <td class="text-left" style="width:25%">
                                <span :class="pp.status_class" class="badge">
                                    <i :class="pp.icon"></i> {{pp.status+' '+pp.date}}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import CustomPopover from "../../../general/client/reusables/CustomPopover";
    import {mapActions, mapState} from "vuex";

    export default {
        props: ['booking_id'],
        components: {
            CustomPopover
        },
        mounted() {
            this.fetchPaymentSummary(this.booking_id);
        },
        methods: {
            ...mapActions('ba/',[
                'fetchPaymentSummary'
            ])
        },
        computed: {
            ...mapState({
                payment_summary: function (state) {
                    return state.ba.pre_checkin.payment_summary;
                }
            })
        }
    }
</script>