<template>
    <div>
        <div class="guest-portal">
            <div class="gp-page gp-min">
                <div class="gp-title">
                    <h1 class="page-title">Booking {{summary.reference}}</h1>
                    <p class="text-muted">
                        <span :class="'badge '+payment_status.class">{{payment_status.status}}</span>
                    </p>
                </div>
                <div class="gp-box">
                    <div class="gp-box-steps">
                        <div class="gp-property">
                            <div class="gp-property-img">
                                <img v-if="header.property_initial==''" :src="header.property_logo" width="80px">
                                <div v-else class="display-initials-wrapper s6">
                                    <span class="initial_icon">
                                        {{header.property_initial}}
                                    </span>
                                </div>
                            </div>
                            <div class="gp-property-legend">
                                <p class="mb-0">{{header.property_name}}</p>
                                <div class="gp-property-dl small">
                                    <img v-if="header.booking_source_initial==''" :src="header.booking_source_logo" alt="">
                                    <div v-else class="display-initials-wrapper">
                                        <span class="initial_icon">
                                            {{header.booking_source_initial}}
                                        </span>
                                    </div>
                                    <span>{{header.booking_source}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="gp-box-content-wrapper">
                        <div class="gp-box-content">
                            <div class="gp-inset">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-section-title mt-3">
                                            <h4>Booking Summary</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col">
                                        <!--                                    <dl class="gp-dl dl-with-icon">-->
                                        <!--                                        <i class="dl-icon fas fa-hotel"></i>-->
                                        <!--                                        <dt>Reference</dt>-->
                                        <!--                                        <dd>{{summary.reference}}</dd>-->
                                        <!--                                    </dl>-->
                                        <!--                                    <dl class="gp-dl dl-with-icon">-->
                                        <!--                                        <i class="dl-icon fas fa-file"></i>-->
                                        <!--                                        <dt>Booking Type</dt>-->
                                        <!--                                        <dd>{{summary.booking_type}}</dd>-->
                                        <!--                                    </dl>-->
                                        <dl class="gp-dl dl-with-icon">
                                            <i class="dl-icon fas fa-hourglass-start"></i>
                                            <dt>Booking Status</dt>
                                            <dd>{{summary.pms_booking_Status}}</dd>
                                        </dl>
                                        <dl class="gp-dl dl-with-icon">
                                            <i class="dl-icon fas fa-door-open"></i>
                                            <dt>Check-in</dt>
                                            <dd>{{summary.check_in}}</dd>
                                        </dl>
                                    </div>

                                    <div class="col">

                                        <dl class="gp-dl dl-with-icon">
                                            <i class="dl-icon fas fa-clock"></i>
                                            <dt>Booking Time</dt>
                                            <dd>{{summary.booking_time}}</dd>
                                        </dl>
                                        <dl class="gp-dl dl-with-icon">
                                            <i class="dl-icon fas fa-door-closed"></i>
                                            <dt>Check-out</dt>
                                            <dd>{{summary.check_out}}</dd>
                                        </dl>
                                    </div>
                                </div>
                                <div class="row mb-3" v-if="summary.contact_info">
                                    <div class="col-12">
                                        <div class="form-section-title mt-3">
                                            <h4>Contact info</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="summary.contact_info">
                                    <div class="col">
                                        <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-user-circle"></i>
                                            <dt>Full name:</dt>
                                            <dd>{{summary.full_name}}</dd>
                                        </dl>
                                    </div>
                                    <div class="col">
                                        <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-envelope"></i>
                                            <dt>Email:</dt>
                                            <dd>{{summary.email}}</dd>
                                        </dl>
                                    </div>
                                </div>
                                <div class="row" v-if="summary.contact_info">
                                    <div class="col">
                                        <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-phone-volume"></i>
                                            <dt>Phone:</dt>
                                            <dd>{{summary.phone}}</dd>
                                        </dl>
                                    </div>
                                    <div class="col">
                                        <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-user-friends"></i>
                                            <dt>Guests:</dt>
                                            <dd>{{summary.adults}} ({{summary.childern}} child)</dd>
                                        </dl>
                                    </div>
                                </div>
                                <div class="row" v-if="summary.card_info">
                                    <div class="col-12" v-if="summary.card_info">
                                        <div class="form-section-title mt-3">
                                            <h4>Payment method</h4>
                                        </div>
                                    </div>
                                    <div class="col-12" v-if="summary.card_info">
                                        <dl class="gp-dl">
                                            <dt>Card number:</dt>
                                            <dd>**** **** **** {{summary.cc_last_4_digit}}</dd>
                                        </dl>
                                    </div>
                                </div>

                                <div class="row" v-if="summary.card_info">
                                    <div class="col-12" v-if="summary.card_info">
                                        <div class="form-section-title mt-3 hide-border">
                                            <h4>Payment Logs</h4>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table text-md">
                                                <thead>
                                                <tr>
                                                    <th>Sr.</th>
                                                    <th>Transaction Reference</th>
                                                    <th>Event Date</th>
                                                    <th>Description</th>
                                                    <th>Actual Response</th>
                                                    <th>Attempted</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(log, i) in activity_log">
                                                    <td>{{parseInt(i)+parseInt(1)}}</td>
                                                    <td>
                                                        {{log.id}}
                                                    </td>
                                                    <td>{{log.event_date}}</td>
                                                    <td>{{log.desc_cc}}</td>
                                                    <td>{{log.status_msg}}</td>
                                                    <td>{{log.attempted}}</td>
                                                </tr>
                                                <tr v-if="activity_log.length == 0">
                                                    <td colspan="6" style="text-align: center;">No record available</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="footer d-flex align-items-center sticky-footer text-center">
            <div class="container">
                <div class="row">
                    <div class="col text-muted">
                        <button class="btn btn-success btn-confirm" v-if="summary.manual_canceled != 1"
                                @click.prevent="cancelBooking($event)"
                                v-bind:data-id="summary.reference"
                                v-bind:id="summary.reference"
                                v-bind:data-status="booking_id">
                            Cancel Booking Due To Invalid Credit Card
                        </button>
                    </div>
                </div>
            </div>
        </footer>
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';

    export default {
        props: ['booking_id', 'booking_url'],
        mounted() {
            this.fetchStepFiveData(this.booking_id);
            this.fetchActivityLogs(this.booking_id);
        },
        methods: {
            ...mapActions([
                'fetchStepFiveData',
                'fetchActivityLogs'
            ]),

            cancelBooking(event) {
                let self = this;
                let btn_id = event.target.id;
                let booking_info_id = self.booking_id;
                let pms_id = event.target.dataset.id;
                let url = self.booking_url;

                swal.fire({
                    title: "Are you sure, you want to cancel booking #"+pms_id+" on booking.com?",
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, Cancel"
                })
                    .then(function (e) {
                        if (e.value == true) {
                            self.loader.block = true;
                            axios({
                                url: url,
                                method: 'GET',
                            }).then(function (response) {
                                if (response.data.flag === true) {
                                    self.loader.block = false;
                                    toastr.success(response.data.message);
                                }
                                else{
                                    self.loader.block = false;
                                    toastr.error(response.data.message);
                                }
                            }).catch(function (error) {
                                self.loader.block = false;
                                console.log(error);
                            });
                        }
                    });
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                summary: (state) => {
                    return state.pre_checkin.summary;
                },
                header: (state) => {
                    return state.pre_checkin.header;
                },
                activity_log: function (state) {
                    return state.booking_detail.activity_log
                },
                payment_status: function (state) {
                    return state.booking_detail.payment_status
                }
            })
        },
        watch: {
            summary: {
                deep: true,
                immediate: true,
                handler(new_value, old_value) {

                    if (new_value.is_completed) {
                        window.location.href = this.summary.next_link;
                    }
                }
            }
        }
    }
</script>
<style type="text/css" scoped>
    .input-group-prepend button.btn, .input-group-append button.btn {z-index: 0;}
    .gp-dl {text-align: left !important;}
    span.initial_icon {
        background-color: #334e68;
        color: #fff;
        border-radius: 100%;
        width: 32px;
        line-height: 32px;
        text-align: center;
        font-weight: 600;
        font-size: 16px;
        margin-right: 5px;
    }
    .hide-border{border: none !important;}
    .guest-panel-wrapper.cancel-bdc-booking .guest-portal{height:auto!important;min-height:auto!important;}
    .guest-panel-wrapper.cancel-bdc-booking .guest-portal .gp-box .gp-box-content-wrapper {display:block;float:left;overflow:hidden;width:100%;}
    .guest-panel-wrapper.cancel-bdc-booking .guest-portal .gp-page .gp-box .gp-box-content-wrapper .gp-box-content{max-height:inherit!important;overflow:hidden!important;padding:10px!important;}
    .guest-panel-wrapper.cancel-bdc-booking .gp-title{margin:0.75rem 0px;}
    .guest-panel-wrapper.cancel-bdc-booking footer.sticky-footer {padding:40px 0px;width:100%}
</style>