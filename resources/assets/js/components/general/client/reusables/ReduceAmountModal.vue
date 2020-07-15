<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" ref="refundModal" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Reduce Transaction Amount</h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <form v-on:submit.prevent>
                        <div class="form-group">
                            <label>Current Amount</label>
                            <input :value="tran_data.current_amount" aria-describedby="current_amount" class="form-control form-control-sm"
                                   disabled type="text"/>
                            <!--                            <span v-if="hasError.amount" role="alert" class="invalid-feedback">-->
                            <!--                                    <strong>{{errorMessage.amount}}</strong>-->
                            <!--                            </span>-->
                        </div>
                        <div class="form-group">
                            <label>New Amount</label>
                            <input aria-describedby="new_amount" class="form-control form-control-sm" placeholder="New Amount"
                                   type="number" v-model="new_amount"/>
                            <span class="invalid-feedback" role="alert" v-if="hasError.amount">
                                    <strong>{{errorMessage.amount}}</strong>
                            </span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button @click.prevent="formReset()" class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                            id="force_close_reduce_amount" type="button">Cancel
                    </button>
                    <button @click.prevent="reduceAmount()" class="btn btn-sm btn-success px-3" type="button">
                        Reduce...
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
            //console.log(this.tran_data);
        },
        props: ['calling_id'],
        watch: {
            tran_data: {
                deep: true,
                immediate: true,
                handler(newVal, oldVal) {
                    if (this.tran_data.new_amount && this.tran_data.new_balance) {
                        $('.total_amount_of_' + this.tran_data.booking_id).text(this.tran_data.new_amount);
                        $('.balance_for_' + this.tran_data.booking_id).text(this.tran_data.new_balance);
                    }
                    this.reset();
                }
            }
        },
        data() {
            return {
                new_amount: '',
                hasError: {
                    amount: false
                },
                errorMessage: {
                    amount: ''
                }
            }
        },
        methods: {
            validateAmount() {
                let flag = true;
                if (this.new_amount == '' || this.amount < '0') {
                    this.hasError.amount = true;
                    this.errorMessage.amount = 'New Amount is required!';
                    flag = false;
                }
                return flag;
            },
            reset() {
                Object.assign(this.$data, this.$options.data());
            },
            reduceAmount() {
                let self = this;
                let valid = self.validateAmount();
                if (valid) {
                    swal.fire({
                        title: "Are you sure to reduce the transaction amount?",
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, do it!"
                    }).then(function (e) {
                        if (e.value == true) {

                            let data = {
                                'booking_info_id': self.tran_data.booking_id,
                                'transaction_init_id': self.tran_data.tran_id,
                                'newAmount': self.new_amount
                            };
                            self.$store.dispatch('general/ReduceAmount', data)
                                .then(() => {
                                    self.formReset();
                                    $('#force_close_reduce_amount').click();
                                });
                        } else {
                            self.formReset();
                            $('#force_close_reduce_amount').click();
                        }
                    });
                }
            },
            formReset() {
                this.new_amount = '';
            }
        },
        computed: {
            ...mapState({
                tran_data: (state) => {
                    return state.general.reduce_amount_data;
                }
            })
        }
    }
</script>
