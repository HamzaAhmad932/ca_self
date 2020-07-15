<template>
    <div>
        <div aria-labelledby="bookingSources-tab" class="tab-pane fade active show" id="bookingSources" role="tabpanel">
            <h4 class="setup-page-title text-muted">
                FETCH BOOKING SETTINGS
                <small class="blockquote-footer" style="background: none; color: #486581;">
                    Yon can turn On & Off Fetch-Booking feature for the following booking sources
                </small>
            </h4>

            <div class="row justify-space-between" style="margin-bottom: 15px !important;">
                <div class="col-md-4">
                </div>
                <div class="col-md-8 col-lg-6 ml-auto">
                    <div class="connection-btn-stack">
                        <div class="btn-group">
                            <a @click="onOffBookingChannelAll(true)" class="btn btn-sm btn-secondary px-3"
                               href="javaScript:void(0)">Connect All</a>
                            <a @click="onOffBookingChannelAll(false)" class="btn btn-sm btn-secondary px-3"
                               href="javaScript:void(0)">Disconnect All</a></div>
                    </div>
                </div>
            </div>

            <template v-for="(bookingSource, index) in bookingSources">
                <!-- Start of Booking Chanel Setting card -->
                <div :id="'sourceBooking_'+index" :title="bookingSource.desc" class="accordion mb-2">
                    <div class="card">
                        <div class="card-header">
                            <a :aria-controls="'collapse_'+index" :data-target="'#collapse_'+index" :id="'click_id_'+index"
                               :title="bookingSource.desc" aria-expanded="true" class="booking-accordion-title collapsed"
                               data-toggle="collapse"
                               style="float:left">
                                <div class="booking-source-logo">
                                    <img :src="bookingSource.logo" v-if="bookingSource.logo.length > 2"/>
                                    <div class="display-initials-wrapper" v-if="bookingSource.logo.length < 3">
                                        <span class="initial_icon">{{ bookingSource.logo }}</span>
                                    </div>
                                </div>
                                {{bookingSource.name}}
                            </a>


                            <div class="checkbox-toggle checkbox-choice bookingSources-settings-page-active-deactive-button"
                                 style="float: right; padding: 0.85rem 10px 0.75rem 1rem;">
                                <input :id="'on_off_for_bs_'+bookingSource.id" @click="onOffBookingChannel($event, index)"
                                       class="custom-control-input"
                                       type="checkbox"
                                       v-model="bookingSource.fetch_booking">
                                <label :for="'on_off_for_bs_'+bookingSource.id" class="checkbox-label" data-off="OFF"
                                       data-on="ON">
									<span class="toggle-track">
										<span class="toggle-switch"></span>
									</span>
                                    <span class="toggle-title"></span>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- End of Booking Chanel Setting card -->
            </template>
        </div>
        <loader v-show="preLoader"></loader>
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    export default {
        name: "bookingSource-settings",
        created() {
            this.getBookingSourcesWithUserSetting();
        },

        data() {
            return {
                preLoader: false,
                msg: 'Please Wait',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                bookingSources: {},

            }
        },
        methods: {
            getBookingSourcesWithUserSetting() {
                let self = this;
                self.block = true;
                axios.post('/client/v2/get-booking-channels-settings')
                    .then(function (response) {
                        if (response.data.status)
                            self.bookingSources = response.data.data;

                        self.block = false;
                    })
                    .catch(function (error) {
                        //stop loader
                        console.log(error);
                        self.block = false;
                        toastr.error('Failed to Load settings');
                    });
            },

            onOffBookingChannel(e, index) {
                let self = this;
                self.block = true;
                let status = e.target.checked;
                axios.post('/client/v2/bookingSource-on-off',
                    {'booking_source_form_id': self.bookingSources[index].id, 'status': status})
                    .then(function (response) {
                        if (response.data.status) {
                            toastr.success(response.data.message);

                            //update intercom data
                            updateIntercomData('booking_fetch_changed');

                        } else {
                            toastr.error(response.data.msg);
                            self.bookingSources[index].fetch_booking = status ? false : true;
                        }
                        //stop loader
                        self.block = false;
                    })
                    .catch(function (error) {
                        self.bookingSources[index].fetch_booking = status ? false : true;
                        console.log(error);
                        self.block = false;
                        toastr.error('Failed to save settings');
                    });
            },
            onOffBookingChannelAll(status) {
                let self = this;
                self.block = true;
                axios.post('/client/v2/bookingSource-on-off-all', {'status': status})
                    .then(function (response) {
                        if (response.data.status) {
                            toastr.success(response.data.message);
                            self.bookingSources = response.data.data;

                            //update intercom data
                            updateIntercomData('booking_fetch_changed');
                        } else {
                            toastr.error(response.data.msg);
                        }
                        //stop loader
                        self.block = false;
                    }).catch(function (error) {
                    console.log(error);
                    self.block = false;
                    toastr.error('Failed to save settings');
                });
            },
        },
        mounted() {
        }

    }
</script>
