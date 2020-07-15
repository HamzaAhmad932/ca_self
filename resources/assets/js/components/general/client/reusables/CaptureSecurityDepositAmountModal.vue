<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" ref="refundModal" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Capture Security Deposit Amount</h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <form v-on:submit.prevent>
                        <div class="form-group">
                            <label>Amount</label>
                            <input :max="amount_valid_to_capture" @keyup="checkCaptureAmountAvailable()"
                                   aria-describedby="firstName" class="form-control form-control-sm" min="0" placeholder="Amount"
                                   type="number" v-model="amount"/>
                            <!--<span class="invalid-feedback text-danger" role="alert" v-if="hasError.amount">
                                <strong>{{errorMessage.amount}}</strong>
                            </span>-->
                            <small class="form-text  text-danger" v-if="capture_amount.error_status.amount">{{capture_amount.error_message.amount}}</small>
                        </div>
                        <div class="form-group">
                            <label for="capture_description">Description</label>
                            <textarea id="capture_description" class="form-control" placeholder="Mention Reason"
                                      v-model="description"></textarea>
                            <!--<span class="invalid-feedback" role="alert" v-if="hasError.description">
                                    <strong>{{errorMessage.description}}</strong>
                            </span>-->
                            <small class="form-text  text-danger" v-if="capture_amount.error_status.description">{{capture_amount.error_message.description}}</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button @click.prevent="formReset()" class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                            id="force_modal_close" type="button">Cancel
                    </button>
                    <button @click.prevent="capture()" class="btn btn-sm btn-success px-3" type="button">Capture
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
        props: ['calling_id'],
        watch: {
            cc_auth_id: {
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
                // hasError: {
                //     amount: false,
                //     description: false
                // },
                // errorMessage: {
                //     amount: '',
                //     description: ''
                // }
            }
        },
        methods: {
            validateAmount() {
                let flag = true;
                if (this.amount == '' || this.amount <= '0') {
                    this.capture_amount.error_status.amount = true;
                    this.capture_amount.error_message.amount = 'Capture Amount is required!';
                    flag = false;
                } else if (this.amount_valid_to_capture < this.amount) {
                    this.capture_amount.error_status.amount = true;
                    this.capture_amount.error_message.amount = 'You can capture maximum ' + this.amount_valid_to_capture + ' of remaining hold amount.';
                    flag = false;
                } else {
                    this.capture_amount.error_status.amount = false;
                    this.capture_amount.error_message.amount = '';
                }

                if (this.description == '') {
                    this.capture_amount.error_status.description = true;
                    this.capture_amount.error_message.description = 'Reason of refund is missing.';
                    flag = false;
                } else {
                    this.capture_amount.error_status.description = false;
                    this.capture_amount.error_message.description = '';
                }

                return flag;
            },
            checkCaptureAmountAvailable() {
                if (this.amount > this.amount_valid_to_capture)
                    this.amount = this.amount_valid_to_capture;
            },
            reset() {
                Object.assign(this.$data, this.$options.data());
            },
            capture() {
                let self = this;
                let valid = self.validateAmount();
                if (valid) {
                    swal.fire({
                        title: "Are you sure you want to capture the authorized amount?",
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, Capture Now!"
                    }).then(function (e) {
                        if (e.value == true) {

                            let data = {
                                'amount': self.amount,
                                'cc_auth_id': self.cc_auth_id,
                                'booking_info_id': self.booking_id,
                                'description': self.description,
                                'pms_prefix': 'ba'
                            };

                            self.$store.dispatch('general/captureAuthAmount', data);
                                // .then(() => {
                                //     self.formReset();
                                //     $('#force_modal_close').click();
                                // });
                        } else {
                            // console.error('in else');
                            self.formReset();
                            $('#force_modal_close').trigger('click');
                        }
                    });
                }
            },
            formReset() {
                this.amount = '';
            }
        },
        computed: {
            ...mapState({
                booking_id: (state) => {
                    return state.general.capture_amount_active_booking_id;
                },
                cc_auth_id: (state) => {
                    return state.general.capture_amount_active_cc_auth_id;
                },
                amount_valid_to_capture: (state) => {
                    return state.general.amount_valid_to_capture;
                },
                capture_amount: (state) => {
                    return state.general.capture_amount;
                },
            })
        }
    }
</script>