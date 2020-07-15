<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" ref="refundModal" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Refund Amount</h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <form v-on:submit.prevent>
                        <div class="form-group">
                            <label>Amount</label>
                            <input :max="amountValidToRefund" @keypress="number_only" @keyup="checkRefundAmountAvailable()"
                                   aria-describedby="firstName" class="form-control form-control-sm" min="0" placeholder="Amount"
                                   type="text" v-model="amount"/>
                            <span class="invalid-feedback" role="alert" v-if="hasError.amount">
                                <strong>{{errorMessage.amount}}</strong>
                            </span>
                        </div>
                        <div class="form-group">
                            <label :for="['description_RA_' + calling_id]">Description</label>
                            <textarea :id="['description_RA_' + calling_id]" class="form-control" placeholder="Mention Reason(Mandatory)"
                                      v-model="description"></textarea>
                            <span class="invalid-feedback" role="alert" v-if="hasError.description">
                                    <strong>{{errorMessage.description}}</strong>
                            </span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button @click.prevent="formReset()" class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                            id="force_close" type="button">Cancel
                    </button>
                    <button @click.prevent="refundAmount()" class="btn btn-sm btn-success px-3" type="button">Refund
                        now
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        mounted() {

        },
        props: ['calling_id'],
        watch: {
            booking_id: {
                immediate: true,
                handler(newVal, oldVal) {
                    this.reset();
                }
            }
        },
        data() {
            return {
                amount: '',
                description: '',
                hasError: {
                    amount: false,
                    description: false
                },
                errorMessage: {
                    amount: '',
                    description: ''
                }
            }
        },
        methods: {
            number_only(e){
                let keyCode = (e.keyCode ? e.keyCode : e.which);
                if(keyCode === 8 || keyCode === 46 || ( keyCode >= 48 && keyCode <= 57 )){
                    return true;
                } else {
                    e.preventDefault();
                }
            },
            validateAmount() {
                let flag = true;
                this.hasError.amount = false;
                this.hasError.description = false;
                if (this.amount == '' || this.amount <= 0) {
                    this.hasError.amount = true;
                    this.errorMessage.amount = 'Refund Amount is required!';
                    flag = false;
                } else if (this.amountValidToRefund < this.amount) {
                    this.hasError.amount = true;
                    this.errorMessage.amount = 'Refund Amount is greater than Charged Amount.';
                    flag = false;
                }

                if (this.description == '') {
                    this.hasError.description = true;
                    this.errorMessage.description = 'Reason of refund is missing.';
                    flag = false;
                }

                return flag;
            },
            checkRefundAmountAvailable() {
                if (this.amount > this.amountValidToRefund)
                    this.amount = this.amountValidToRefund;
            },
            reset() {
                Object.assign(this.$data, this.$options.data());
            },
            refundAmount() {
                let self = this;
                let valid = self.validateAmount();
                if (valid) {
                    swal.fire({
                        title: "Are you sure you want to process this refund?",
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, Refund Now!"
                    }).then(function (e) {
                        if (e.value == true) {

                            let data = {
                                'amount': self.amount,
                                'booking_id': self.booking_id,
                                'transaction_id': self.transaction_id,
                                'description': self.description,
                                'prefix': 'ba',
                            };
                            self.$store.dispatch('general/RefundAmount', data)
                                .then(() => {
                                    self.formReset();
                                    $('#force_close').click();
                                });
                        } else {
                            self.formReset();
                            $('#force_close').click();
                        }
                    });
                }
            },
            formReset() {
                this.amount = '';
                this.description = '';
            }
        },
        computed: {
            ...mapState({
                booking_id: (state) => {
                    return state.general.refund_amount_active_booking_id;
                },
                transaction_id: (state) => {
                    return state.general.refund_amount_active_transaction_id;
                },
                amountValidToRefund: (state) => {
                    return state.general.amount_valid_to_refund;
                }
            })
        }
    }
</script>