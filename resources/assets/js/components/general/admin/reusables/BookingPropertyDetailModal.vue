<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Property Detail</h4>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped- table-bordered table-hover table-checkable">
                            <thead>
                            <tr>
                                <th>Booking Info ID</th>
                                <th>PMS Property ID</th>
                                <th>Name</th>
                                <th>User Account Name</th>
                                <th>Property Key</th>
                                <th>Currency Code</th>
                                <th>Time Zone</th>
                                <th>Address</th>
                                <th>User Payment gateway ID</th>
                                <th>Booking Source Setting</th>
                                <th>Payment Gateway Setting</th>
                                <th>Status</th>
                                <th>Property Email</th>
                                <th>Notify URL</th>
                                <th>Last Sync</th>
                                <th>Available On PMS</th>
                                <th>Created_at</th>
                                <th>Updated At</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{booking_property.id}}</td>
                                <td>{{booking_property.pms_property_id}}</td>
                                <td>{{booking_property.name}}</td>
                                <td>{{booking_property_history.name}}</td>
                                <td>{{booking_property.property_key}}</td>
                                <td>{{booking_property.currency_code}}</td>
                                <td>{{booking_property.time_zone}}</td>
                                <td>{{booking_property.address}}<br/> City: {{booking_property.city}}<br/> Country:
                                    {{booking_property.country}}
                                </td>
                                <td>{{booking_property.user_payment_gateway_id}}</td>
                                <td>{{booking_property.use_bs_settings | bookingSourceSettings}}</td>
                                <td>{{booking_property.use_pg_settings | paymentGatewaySettings}}</td>
                                <td>{{booking_property.status | propertyStatus}}</td>
                                <td>{{booking_property.property_email}}</td>
                                <td>{{booking_property.notify_url}}</td>
                                <td>{{booking_property.last_sync | formatDateTime}}</td>
                                <td>{{booking_property.available_on_pms | availabilityOnPMS}}</td>
                                <td>{{booking_property.created_at | formatDateTime}}</td>
                                <td>{{booking_property.updated_at | formatDateTime}}</td>
                            </tr>
                            <tr class="cchild" v-for="property_history in booking_property_history">
                                <td colspan="18">
                                    <div class="row">
                                        <div class="col-md-1"><strong>URL</strong> <br/>{{property_history.url}}</div>
                                        <div class="col-md-1"><strong>IP Address</strong> <br/>{{property_history.ip_address}}
                                        </div>
                                        <div class="col-md-1"><strong>User Agent</strong> <br/>{{property_history.user_agent}}
                                        </div>
                                        <div class="col-md-1"><strong>Old Values</strong> <br/>
                                            <pre>{{property_history.old_values}}</pre>
                                        </div>
                                        <div class="col-md-1"><strong>New Values</strong> <br/>
                                            <pre>{{property_history.new_values}}</pre>
                                        </div>
                                        <div class="col-md-1"><strong>Created At</strong> <br/>{{property_history.created_at
                                            | formatDateTime}}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="booking_property.length == 0">
                                <td class="text-center" colspan="18">
                                    Record not found
                                </td>
                            </tr>
                            <tr v-if="booking_property_history.length == 0">
                                <td class="text-center" colspan="18">
                                    Property Detail Not Found
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
                booking_property: (state) => {
                    return state.admin_booking.booking_property;
                },
                booking_property_history: (state) => {
                    return state.admin_booking.booking_property_history;
                },
                booking_user_account: (state) => {
                    return state.admin_booking.booking_property_history;
                },
            })
        },
        watch: {
            //
        },
        filters: {
            bookingSourceSettings(value) {
                if (value == 0) {
                    return "Global";
                } else if (value == 1) {
                    return "Local";
                }
            },
            paymentGatewaySettings(value) {
                if (value == 0) {
                    return "Global Payment Gateway";
                } else if (value == 1) {
                    return "Local Payment Gateway";
                }
            },
            propertyStatus(value) {
                if (value == 0) {
                    return "DeActive";
                } else if (value == 1) {
                    return "Active";
                }
            },
            availabilityOnPMS(value) {
                if (value == 0) {
                    return "Not valid or deleted on PMS";
                } else if (value == 1) {
                    return "Available or Valid Property";
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
