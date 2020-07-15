<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Transaction Detail</h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped- table-bordered table-checkable">
                            <thead>
                            <tr>
                                <th>Detail</th>
                                <th>Booking Info Id</th>
                                <th>PMS Name</th>
                                <th>Due Date</th>
                                <th>Next Attempt Time</th>
                                <th>Price</th>
                                <th>Payment Status</th>
                                <th>User Account Name</th>
                                <th>Charge Ref No</th>
                                <th>Lets Process</th>
                                <th>System Remarks</th>
                                <th>Against Charge Ref No</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Transaction Type</th>
                                <th>Client Remarks</th>
                                <th>Auth Token</th>
                                <th>Error Code Id</th>
                                <th>Attempt</th>
                                <th>Remarks</th>
                                <th>Created_at</th>
                                <th>Updated At</th>
                                <th>Payment Intent Id</th>
                                <th>In Processing</th>
                            </tr>
                            </thead>
                            <tbody v-for="transaction_data in transaction">
                            <tr class="parent">
                                <td><i class="fa fa-chevron-down"></i></td>
                                <td>{{transaction_data.booking_info_id}}</td>
                                <td>{{transaction_data.pms_name}}</td>
                                <td>{{transaction_data.due_date}}</td>
                                <td>{{transaction_data.next_attempt_time}}</td>
                                <td>{{transaction_data.price}}</td>
                                <td>{{transaction_data.payment_status}}</td>
                                <td>{{transaction_data.user_account_name}}</td>
                                <td>{{transaction_data.charge_ref_no}}</td>
                                <td>{{transaction_data.lets_process}}</td>
                                <td>{{transaction_data.system_remarks}}</td>
                                <td>{{transaction_data.against_charge_ref_no}}</td>
                                <td>{{transaction_data.type}}</td>
                                <td>{{transaction_data.status}}</td>
                                <td>{{transaction_data.transaction_type}}</td>
                                <td>{{transaction_data.client_remarks}}</td>
                                <td>{{transaction_data.auth_token}}</td>
                                <td>{{transaction_data.error_code_id}}</td>
                                <td>{{transaction_data.attempt}}</td>
                                <td>{{transaction_data.remarks}}</td>
                                <td>{{transaction_data.created_at}}</td>
                                <td>{{transaction_data.updated_at}}</td>
                                <td>{{transaction_data.payment_intent_id}}</td>
                                <td>{{transaction_data.in_processing}}</td>
                            </tr>
                            <tr class="cchild" v-for="transaction_detail_data in transaction_data.transactions_detail">
                                <td colspan="24">
                                    <div class="row">
                                        <div class="col-md-1"><strong>Transaction Init ID</strong> <br/>{{transaction_detail_data.transaction_init_id}}
                                        </div>
                                        <div class="col-md-1"><strong>CC Info ID</strong> <br/>{{transaction_detail_data.cc_info_id}}
                                        </div>
                                        <div class="col-md-1"><strong>Name</strong> <br/>{{transaction_detail_data.name}}
                                        </div>
                                        <div class="col-md-1"><strong>Payment Gateway Name</strong> <br/>{{transaction_detail_data.payment_gateway_name}}
                                        </div>
                                        <div class="col-md-1"><strong>Payment Status</strong> <br/>{{transaction_detail_data.payment_status}}
                                        </div>
                                        <div class="col-md-1"><strong>Charge Ref No</strong> <br/>{{transaction_detail_data.charge_ref_no}}
                                        </div>
                                        <div class="col-md-1"><strong>Client Remarks</strong> <br/>{{transaction_detail_data.client_remarks}}
                                        </div>
                                        <div class="col-md-1"><strong>Error Message</strong> <br/>{{transaction_detail_data.error_msg}}
                                        </div>
                                        <div class="col-md-1"><strong>Order ID</strong> <br/>{{transaction_detail_data.order_id}}
                                        </div>
                                        <div class="col-md-1"><strong>Created At</strong> <br/>{{transaction_detail_data.created_at}}
                                        </div>
                                        <div class="col-md-1"><strong>Updated At</strong> <br/>{{transaction_detail_data.updated_at}}
                                        </div>
                                        <div class="col-md-1"><strong>Amount</strong> <br/>{{transaction_detail_data.amount}}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="cchild" v-if="transaction_data.transactions_detail.length == 0">
                                <td class="text-center" colspan="24">
                                    Transaction Detail Not Found
                                </td>
                            </tr>
                            </tbody>
                            <tbody v-if="transaction.length == 0">
                            <tr>
                                <td class="text-center" colspan="24">
                                    Record not found
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal" id="force_close_additional_charge"
                            type="button">Close
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
                if (this.additional_charge.booking_info_id == '') {
                    this.hasError.booking_info_id = true;
                    this.errorMessage.booking_info_id = 'Booking No. is missing.';
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
                this.additional_charge.booking_info_id = this.booking_id;
                if (!this.validateAdditionalCharge()) {
                    this.$store.dispatch('additionalCharge', this.additional_charge)
                        .then(() => {
                            $('#force_close_additional_charge').click();
                        });
                }
            },
        },
        computed: {
            ...mapState({
                transaction: (state) => {
                    return state.admin_booking.transaction;
                },
            })
        },
        watch: {
            booking_id: {
                immediate: true,
                handler(newVal, oldVal) {
                    this.reset();
                }
            }
        },
    }

    $(document).ready(function () {
        $('table').on('click', 'tr.parent .fa-chevron-down', function () {
            $(this).closest('tbody').toggleClass('open');
        });
    });
</script>
<style scoped>
    .modal-dialog {
        max-width: 100% !important;
        width: 100% !important;
        /*height: 80% !important;*/
        /*margin: 0 !important;*/
        /*padding: 0 !important;*/
    }

    /*.modal-content {*/
    /*    height: auto !important;*/
    /*    min-height: 80% !important;*/
    /*    border-radius: 0 !important;*/
    /*}*/
    .modal-open .modal {
        overflow-x: auto;
        overflow-y: auto;
    }

    /*Table Collaps style*/
    .parent ~ .cchild {
        display: none;
    }

    .open .parent ~ .cchild {
        display: table-row;
    }

    .parent {
        cursor: pointer;
    }

    tbody {
        color: #212121;
    }

    .open {
        background-color: #e6e6e6;
    }

    .open .cchild {
        background-color: #999;
        color: white;
    }

    .parent > *:last-child {
        width: 30px;
    }

    .parent i {
        transform: rotate(0deg);
        transition: transform .3s cubic-bezier(.4, 0, .2, 1);
        margin: -.5rem;
        padding: .5rem;

    }

    .open .parent i {
        transform: rotate(180deg)
    }
</style>
