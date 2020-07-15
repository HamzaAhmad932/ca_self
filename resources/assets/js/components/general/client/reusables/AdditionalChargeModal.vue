<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Additional Charge</h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <form v-on:submit.prevent>
                        <div class="form-group">
                            <label for="additional_amount">Amount</label>
                            <input aria-describedby="additional_amount" class="form-control form-control-sm"
                                   id="additional_amount" placeholder="Amount" type="text" min="1" v-model="additional_charge.amount" @keypress="amount_only"/>
                            <span class="invalid-feedback" role="alert" v-if="hasError.amount">
                                    <strong>{{errorMessage.amount}}</strong>
                            </span>
                        </div>
                        <div class="form-group">
                            <label :for="['description_AC_' + calling_id]">Description</label>
                            <textarea :id="['description_AC_' + calling_id]" class="form-control"
                                      placeholder="Mention Reason(mandatory)"
                                      v-model="additional_charge.description"></textarea>
                            <span class="invalid-feedback" role="alert" v-if="hasError.description">
                                    <strong>{{errorMessage.description}}</strong>
                            </span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button @click.prevent="formReset()" class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                            id="force_close_additional_charge" type="button">Cancel
                    </button>
                    <button @click.prevent="additionalCharge()" class="btn btn-sm btn-success px-3" type="button">
                        Charge
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {mapState} from 'vuex';

    export default {
        props: ['calling_id'],
        mounted() {

        },
        data() {
            return {
                additional_charge: {
                    booking_info_id: '',
                    amount: '',
                    description: ''
                },
                hasError: {
                    refund_amount: {
                        amount: false
                    },
                    amount: false,
                    booking_info_id: false,
                    description: false
                },
                errorMessage: {
                    refund_amount: {
                        amount: ''
                    },
                    amount: '',
                    booking_info_id: '',
                    description: ''
                },
            }
        },
        methods: {
            reset() {
                Object.assign(this.$data, this.$options.data());
            },
            validateAdditionalCharge() {
                let flag = false;
                this.hasError.amount = false;
                this.hasError.description = false;
                if (this.additional_charge.booking_info_id == '') {
                    this.hasError.booking_info_id = true;
                    this.errorMessage.booking_info_id = 'Booking No. is missing.';
                    flag = true;
                }
                if(this.additional_charge.amount < 1){
                    this.hasError.amount = true;
                    this.errorMessage.amount = 'Amount can not be less than 1.';
                    flag = true;
                }
                if (this.additional_charge.amount == '') {
                    this.hasError.amount = true;
                    this.errorMessage.amount = 'Amount is missing.';
                    flag = true;
                }
                if (this.additional_charge.description == '') {
                    this.hasError.description = true;
                    this.errorMessage.description = 'Reason of additional charge is missing.';
                    flag = true;
                }
                return flag;
            },
            additionalCharge() {
                let self = this;
                this.additional_charge.booking_info_id = this.booking_id;
                // PMS Prefix is required to call specific actions after success of "additionalCharge" action
                this.additional_charge.prefix = 'ba/';
                if (!this.validateAdditionalCharge()) {
                    swal.fire({
                        title: "Are you sure you want to process this charge?",
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, do it!"
                    }).then(function (e) {
                        if (e.value == true) {
                            self.$store.dispatch('general/additionalCharge', self.additional_charge)
                                .then(() => {
                                    self.formReset();
                                    $('#force_close_additional_charge').click();
                                });
                        } else {
                            self.formReset();
                            $('#force_close_additional_charge').click();
                        }
                    });
                }
            },
            formReset() {
                this.additional_charge.amount = '';
                this.additional_charge.description = '';
                this.additional_charge.booking_info_id = '';
            },
            amount_only(e){
                //console.log(e.keyCode);
                //console.log(e.target.value);
                let keyCode = (e.keyCode ? e.keyCode : e.which);
                if(keyCode === 8 || keyCode === 46 || ( keyCode >= 48 && keyCode <= 57 )){
                    return true;
                }
                else {
                    e.preventDefault();
                }
            }
        },
        computed: {
            ...mapState({
                booking_id: (state) => {
                    return state.general.additional_charge_active_booking_id;
                }
            })
        },
        watch: {
            booking_id: {
                immediate: true,
                handler(newVal, oldVal) {
                    this.reset();
                }
            }
        }
    }
</script>
