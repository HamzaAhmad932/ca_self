<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Credit Card Detail</h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped- table-bordered table-checkable">
                            <thead>
                            <tr>
                                <th>Detail</th>
                                <th>Booking Info ID</th>
                                <th>User Account Name</th>
                                <th>Is VC</th>
                                <th>Card Name</th>
                                <th>Name</th>
                                <th>CC Last 4 Digit</th>
                                <th>CC Exp Month</th>
                                <th>CC Exp Year</th>
                                <th>System Usage</th>
                                <th>Auth Token</th>
                                <th>Status</th>
                                <th>Attempts</th>
                                <th>Error Message</th>
                                <th>Due Date</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Country</th>
                                <th>Is 3ds</th>
                                <th>Type</th>
                                <th>Is Default</th>
                            </tr>
                            </thead>
                            <tbody v-for="cc_info_detail in booking_cc_info">
                            <tr class="parent">
                                <td><i class="fa fa-chevron-down"></i></td>
                                <td>{{cc_info_detail.booking_info_id}}</td>
                                <td>{{booking_cc_info_user_account.name}}</td>
                                <td>{{cc_info_detail.is_vc | cardType}}</td>
                                <td>{{cc_info_detail.card_name}}</td>
                                <td>{{cc_info_detail.f_name}} {{booking_cc_info.l_name}}</td>
                                <td>{{cc_info_detail.cc_last_4_digit}}</td>
                                <td>{{cc_info_detail.cc_exp_month}}</td>
                                <td>{{cc_info_detail.cc_exp_year}}</td>
                                <td>{{cc_info_detail.system_usage}}</td>
                                <td>{{cc_info_detail.auth_token}}</td>
                                <td>{{cc_info_detail.status | cardStatus}}</td>
                                <td>Total Attempts: {{cc_info_detail.attempts}}</td>
                                <td>{{cc_info_detail.error_message}}</td>
                                <td>{{cc_info_detail.due_date}}</td>
                                <td>{{cc_info_detail.created_at | formatDateTime}}</td>
                                <td>{{cc_info_detail.updated_at | formatDateTime}}</td>
                                <td>{{cc_info_detail.country}}</td>
                                <td>{{cc_info_detail.is_3ds}}</td>
                                <td>{{cc_info_detail.type}}</td>
                                <td>{{cc_info_detail.is_default}}</td>
                            </tr>
                            <tr class="cchild" v-for="cc_info_history in booking_cc_info_detail">
                                <td colspan="21" v-if="cc_info_history.auditable_id == cc_info_detail.id">
                                    <div class="row">
                                        <div class="col-md-1"><strong>URL</strong> <br/>{{cc_info_history.url}}</div>
                                        <div class="col-md-1"><strong>IP Address</strong> <br/>{{cc_info_history.ip_address}}
                                        </div>
                                        <div class="col-md-1"><strong>User Agent</strong> <br/>{{cc_info_history.user_agent}}
                                        </div>
                                        <div class="col-md-1"><strong>Old Values</strong> <br/>
                                            <pre>{{cc_info_history.old_values}}</pre>
                                        </div>
                                        <div class="col-md-1"><strong>New Values</strong> <br/>
                                            <pre>{{cc_info_history.new_values}}</pre>
                                        </div>
                                        <div class="col-md-1"><strong>Created At</strong> <br/>{{cc_info_history.created_at
                                            | formatDateTime}}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="cchild" v-if="booking_cc_info_detail.length == 0">
                                <td class="text-center" colspan="21">
                                    CC Info Detail Not Found
                                </td>
                            </tr>
                            </tbody>
                            <tbody v-if="booking_cc_info.length == 0">
                            <tr>
                                <td class="text-center" colspan="21">
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
            return {}
        },
        methods: {
            //
        },
        computed: {
            ...mapState({
                booking_cc_info: (state) => {
                    return state.admin_booking.booking_cc_info;
                },
                booking_cc_info_detail: (state) => {
                    return state.admin_booking.booking_cc_info_detail;
                },
                booking_cc_info_user_account: (state) => {
                    return state.admin_booking.booking_cc_info_user_account;
                }
            })
        },
        watch: {
            //
        },
        filters: {
            cardType(value) {
                if (value == 0) {
                    return "CC";
                } else if (value == 1) {
                    return "VC";
                }
            },
            cardStatus(value) {
                if (value == 1) {
                    return "Created";
                } else if (value == 2) {
                    return "Scheduled";
                } else if (value == 3) {
                    return "In-Retry";
                } else if (value == 4) {
                    return "Failed";
                } else if (value == 5) {
                    return "Void";
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
