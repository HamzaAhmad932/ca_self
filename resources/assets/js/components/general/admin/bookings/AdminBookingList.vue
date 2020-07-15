<template>
    <div>

        <!----Modals Components----------->
        <transaction-detail-modal calling_id="transaction_detail_modal"></transaction-detail-modal>
        <booking-property-detail-modal calling_id="booking-property-detail-modal"></booking-property-detail-modal>
        <booking-payment-gateway-detail-modal
                calling_id="booking-payment-gateway-detail-modal"></booking-payment-gateway-detail-modal>
        <booking-ccinfo-detail-modal calling_id="booking-ccinfo-detail-modal"></booking-ccinfo-detail-modal>
        <!-------End Modal Components------>
        <div class="panel-group">
            <div class="panel panel-default" v-for="(booking, index) in booking_list">
                <div class="panel-heading">
                    <div class="row" style="border-top: 1px solid #ebedf2; margin-top: 2rem; padding-top: 2rem;">
                        <div class="col-md-1">
                            <a :aria-controls="'id_'+booking.id"
                               :href="'#id_'+booking.id"
                               :id="booking.id"
                               aria-expanded="false"
                               class="card-collapse collapsed"
                               data-open="true"
                               data-toggle="collapse"
                               role="button">
                                <i :id="booking.id" class="fas fa-chevron-down" data-open="true"
                                   style="display: block; height: 100%; width: 100%;"></i>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a :href="'/admin/booking-details/'+booking.id" target="_blanck" title="View">
                                <strong>ID: </strong> {{ booking.id }}
                            </a><br />
                            <a :href="'/admin/booking-details/'+booking.id" target="_blanck" title="View">
                                <strong>PMS ID: </strong> {{ booking.pms_booking_id }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <!--<strong>User ID: </strong> {{ booking.user.user_id }}<br />
                            <strong>User Name: </strong> {{ booking.user.user_name }}<br />-->
                            <strong>User Account ID: </strong> {{ booking.user.user_account_id }}<br />
                            <strong>Account Name: </strong> {{ booking.user.user_account_name }}
                        </div>
                        <div class="col-md-3">
                            <strong>ID: </strong> {{ booking.property.id }}<br />
                            <strong>PMS ID: </strong> {{ booking.property.pms_property_id }}<br />
                            <strong>Name: </strong> {{ booking.property.name | shortName }}<br />
                            <strong>Room Type: </strong> {{ booking.room.room_type | shortName }}<br />
                            <strong>Unit Name: </strong> {{ booking.room.unit_name}}<br />
                            <strong>Timezone: </strong> {{ booking.property.timezone }}<br />
                        </div>
                        <div class="col-md-1">
                            {{booking.amount}}<br/>
                            <span :class="booking.payment_status.class" class="badge">
                                {{booking.payment_status.status}}
                            </span>
                        </div>
                        <div class="col-md-1">
                            <span v-if="booking.booking_status == 'Confirmed'" class="badge badge-success">
                                {{booking.booking_status}}
                            </span>
                            <span v-else class="badge badge-danger">
                                {{booking.booking_status}}
                            </span>
                        </div>
                        <div class="col-md-1">
                            <span class="dropdown">
                                <a aria-expanded="true"
                                   class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill"
                                   data-toggle="dropdown" href="#">
                                <i class="la la-ellipsis-h"></i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a :href="'/admin/booking-details/'+booking.id" class="dropdown-item"
                                       target="_blanck"><i class="la la-edit"></i> Detail </a>
                                    <a @click="getTransactionsDetail(booking.id)" class="dropdown-item" data-target="#transaction_detail_modal"
                                       data-toggle="modal"
                                       href="javaScript:void(0)"><i class="la la-edit"></i> Transactions </a>
                                    <a @click="getCCInfoDetail(booking.id)" class="dropdown-item" data-target="#booking-ccinfo-detail-modal"
                                       data-toggle="modal"
                                       href="javaScript:void(0)"><i class="la la-user"></i> CC Infos </a>
                                    <a @click="getPropertyDetail(booking.id)" class="dropdown-item" data-target="#booking-property-detail-modal"
                                       data-toggle="modal"
                                       href="javaScript:void(0)"><i class="la la-user"></i> Property </a>
                                    <a @click="getPaymentGatewayDetail(booking.id)" class="dropdown-item" data-target="#booking-payment-gateway-detail-modal"
                                       data-toggle="modal"
                                       href="javaScript:void(0)"><i class="la la-user"></i> Payment Gateway </a>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>

                <div :id="'id_'+booking.id" class="panel-collapse collapse">
                    <div class="panel-body open-div">
                        <div class="row open-div-row">
                            <div class="col-md-3">
                                <strong class="m--margin-bottom-20">Security Amount</strong><br />
                                {{booking.deposit}}<br/>
                                <span :class="booking.deposit_status.class" class="small">
                                    {{booking.deposit_status.deposit_status}}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong class="m--margin-bottom-20">Dates</strong><br />
                                <strong>Check-In: </strong> {{booking.check_in.month}} {{booking.check_in.day}}, {{booking.check_in.year}}<br />
                                <strong>Check-Out: </strong> {{booking.check_out.month}} {{booking.check_out.day}}, {{booking.check_out.year}}<br />
                                <strong>Booking: </strong> {{booking.booking_date}}
                            </div>
                            <div class="col-md-3">
                                <strong class="m--margin-bottom-20">Guest</strong><br/>
                                <strong>Name:</strong> {{booking.guest_name}}<br/>
                                <strong>Phone Number:</strong> {{booking.guest_phone}}<br/>
                                <strong>Email:</strong> {{booking.guest_email}}
                            </div>
                            <div class="col-md-3">
                                <strong class="m--margin-bottom-20">Arrival Time</strong>
                                <br/>{{booking.arrival_time}}
                            </div>
                        </div>
                        <div class="row open-div-row">
                            <div class="col-md-3">
                                <strong class="m--margin-bottom-20">No. of Guests</strong>
                                <br/>{{booking.guests}}
                            </div>
                            <div class="col-md-3">
                                <strong class="m--margin-bottom-20">Last Portal Visit</strong>
                                <br/>{{ booking.last_seen_of_guest != null ? booking.last_seen_of_guest : 'Not Visited'
                                }}
                            </div>
                            <div class="col-md-3">
                                <strong class="m--margin-bottom-20">Payment Type</strong>
                                <br/>{{ booking.isvc}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default" v-if="booking_list.length == 0">
                <div class="panel-heading">
                    <div class="panel-title">No booking found.</div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        props: ['booking_list'],
        data() {
            return {
                //
            }
        },
        mounted() {
        },
        methods: {
            getTransactionsDetail(booking_info_id) {
                this.$store.dispatch('fetchBookingTransactionsDetail', booking_info_id);
            },
            getPropertyDetail(booking_info_id) {
                this.$store.dispatch('fetchBookingPropertyDetail', booking_info_id);
            },
            getPaymentGatewayDetail(booking_info_id) {
                this.$store.dispatch('fetchBookingPaymentGatewayDetail', booking_info_id);
            },
            getCCInfoDetail(booking_info_id) {
                this.$store.dispatch('fetchBookingCCInfoDetail', booking_info_id);
            },
            validBookingToShowByCheckingFilter(booking_info) {
                return this.$parent.validBookingToShowByCheckingFilter(booking_info);
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
            })
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.replace(/([A-Z])/g, ' $1').trim();
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1)
            },
            shortName: function (value) {
                if (!value) {
                    return '';
                } else {
                    //return value.length;
                    if (value.length > 70) {
                        return value.substring(0, 70) + '...';
                    } else {
                        return value
                    }

                }
            },
        }
    }
    $(document).ready(function () {
        $('table').on('click', 'tr.parent .fa-chevron-down', function () {
            $(this).closest('tbody').toggleClass('open');
        });
    });
</script>
<style scoped>
    /*!*Div open*!*/
    /*.open-div {
        background-color: #e6e6e6 !important;
        border: 1px solid #ebedf2 !important;
        padding: 2rem !important;
        margin-top: 2rem !important;
    }*/
    .open-div {
        background-color: #e6e6e6 !important;
    }

    .open-div-row {
        padding: 2rem !important;
        margin-top: 2rem !important;
    }

    .booking-box-dates .booking-box-duration {
        flex: none !important;
        flex-grow: initial !important;
    }

    .cancelled {
        background: none !important;
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