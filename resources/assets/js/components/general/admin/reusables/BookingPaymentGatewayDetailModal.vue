<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Payment Gateway Detail</h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped- table-bordered table-hover table-checkable">
                            <thead>
                            <tr>
                                <th>Payment Gateway Form ID</th>
                                <th>Payment Gateway Name</th>
                                <th>Property Info ID</th>
                                <th>Property Name</th>
                                <th>PMS Property ID</th>
                                <th>User Account ID</th>
                                <th>User Account Name</th>
                                <th>Is Verified</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{payment_gateway.payment_gateway_form_id}}</td>
                                <td>{{payment_gateway.payment_gateway_name}}</td>
                                <td>{{payment_gateway.property_info_id}}</td>
                                <td>{{payment_gateway.property_name}}</td>
                                <td>{{payment_gateway.pms_property_id}}</td>
                                <td>{{payment_gateway.user_account_id}}</td>
                                <td>{{payment_gateway.user_account_name}}</td>
                                <td>{{payment_gateway.is_verified | isVerified}}</td>
                                <td>{{payment_gateway.status | paymentGatewayStatus}}</td>
                                <td>{{payment_gateway.created_at | formatDateTime}}</td>
                            </tr>
                            <tr class="cchild" v-for="gateway_detail in payment_gateway_detail">
                                <td colspan="24">
                                    <div class="row">
                                        <div class="col-md-1"><strong>URL</strong> <br/>{{gateway_detail.url}}</div>
                                        <div class="col-md-1"><strong>IP Address</strong> <br/>{{gateway_detail.ip_address}}
                                        </div>
                                        <div class="col-md-1"><strong>User Agent</strong> <br/>{{gateway_detail.user_agent}}
                                        </div>
                                        <div class="col-md-1"><strong>Old Values</strong> <br/>
                                            <pre>{{gateway_detail.old_values}}</pre>
                                        </div>
                                        <div class="col-md-1"><strong>New Values</strong> <br/>
                                            <pre>{{gateway_detail.new_values}}</pre>
                                        </div>
                                        <div class="col-md-1"><strong>Created At</strong> <br/>{{gateway_detail.created_at
                                            | formatDateTime}}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="payment_gateway.length == 0">
                                <td class="text-center" colspan="10">
                                    Record not found
                                </td>
                            </tr>
                            <tr v-if="payment_gateway_detail.length == 0">
                                <td class="text-center" colspan="10">
                                    Payment Gateway Detail Not Found
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
            return {}
        },
        methods: {
            //
        },
        computed: {
            ...mapState({
                payment_gateway: (state) => {
                    return state.admin_booking.payment_gateway;
                },
                payment_gateway_detail: (state) => {
                    return state.admin_booking.payment_gateway_detail;
                }
            })
        },
        watch: {
            //
        },
        filters: {
            isVerified(value) {
                if (value == 0) {
                    return "Global";
                } else if (value == 1) {
                    return "Local";
                }
            },
            paymentGatewayStatus(value) {
                if (value == 0) {
                    return "DeActive";
                } else if (value == 1) {
                    return "Active";
                }
            },
            formatDateTime(value) {
                let date = new Date(value);
                let monthNames = [
                    "Jan", "Feb", "Mar",
                    "Apr", "May", "Jun", "Jul",
                    "Aug", "Sep", "Oct",
                    "Nov", "Dec"
                ];

                let day = date.getDate();
                let monthIndex = date.getMonth();
                let year = date.getFullYear();
                let hours = date.getHours();
                let minutes = date.getMinutes();
                let seconds = date.getSeconds();

                return monthNames[monthIndex] + ' ' + day + ' ' + year + ' ' + hours + ':' + minutes + ':' + seconds;
            }
        }
    }

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

</style>
