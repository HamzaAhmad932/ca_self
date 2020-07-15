<template>
    <div>
        <!--        <div class="alert alert-danger">-->
        <!--            <i class="fas fa-exclamation-triangle">  </i>-->
        <!--            Booking Cancelled 4 hours ago by the Guest. There is another booking by this guest-->
        <!--            <a class="alert-link" href=""> ID59399599.</a>  It may be a duplicate booking.-->
        <!--        </div>-->
        <div class="booking-info-box">
            <div class="booking-info-box-img d-none d-sm-block">
                <div class="display-initials-wrapper s4" v-if="booking_details.property_initial != '' ">
                    <span class="initial_icon">{{booking_details.property_initial}}</span>
                </div>
                <img class="img img-responsive" :src="booking_details.property_logo" v-else>
            </div>
            <div class="booking-info-box-info">
                <p class="mb-0">{{booking_details.property_heading}}</p>
                <p class="text-md text-muted mb-0">{{booking_details.property_sub_heading}}</p>
            </div>
            <div class="booking-info-box-link">
                <!--<a class="text-md" :href="booking_details.source_link" v-if="booking_details.source_link !== ''" target="_blank">
                    <span class="hidden-xs">{{booking_details.source_heading}}  </span>
                    <i class="fas fa-external-link-alt"></i>
                </a>-->
            </div>
        </div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>
                    Booking
<!--                    <span class="small ml-1 text-muted" data-content="There are a lot of things designed and to design." data-html="true" data-toggle="popover"-->
<!--                          data-trigger="focus" tabindex="0"-->
<!--                          title="Booking detail">-->
<!--                        <i class="fas fa-question-circle"></i>-->
<!--                    </span>-->
                </h4>
            </div>
            <div class="form-row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="bookingID">
                            Booking ID
<!--                            <span class="small ml-1 text-success" data-content="&lt;p&gt;There are a lot of things designed and to design.&lt;/p&gt; &lt;a class='btn btn-primary btn-sm' href='http://google.com'&gt;Read More&lt;/a&gt;" data-html="true"-->
<!--                                  data-placement="top" data-toggle="popover" data-trigger="focus"-->
<!--                                  tabindex="0"-->
<!--                                  title="Property management system's unique booking number">-->
<!--                                                                        <i class="fas fa-info-circle"></i>-->
<!--                                                                    </span>-->
                        </label>
                        <input :value="booking_details.pms_booking_id" class="form-control" disabled id="bookingID"
                               type="text">
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="bookingDate">
                            Booking Date
<!--                            <span class="small ml-1 text-success" data-content="Please provide this info asap. &lt;a href='google.com'&gt;Read More&lt;/a&gt;" data-html="true" data-toggle="popover"-->
<!--                                  data-trigger="focus"-->
<!--                                  tabindex="0"-->
<!--                                  title="Date when booking is placed"-->
<!--                            >-->
<!--                                                                        <i class="fas fa-info-circle"></i>-->
<!--                                                                    </span>-->
                        </label>
                        <input :value="booking_details.booking_date" class="form-control" disabled id="bookingDate"
                               type="text">
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="reference">
                            Channel Reference
<!--                            <span class="small ml-1 text-success" data-content="And here's some amazing content. It's very engaging. Right?" data-placement="top"-->
<!--                                  data-toggle="popover" data-trigger="focus" tabindex="0"-->
<!--                                  title="Reference number on the booking channel (Booking source)"><i class="fas fa-info-circle"></i>-->
<!--                            </span>-->
                        </label>
                        <input :value="booking_details.channel_reference" class="form-control" disabled
                               id="reference" type="text">
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="source">Source</label>
                        <input :value="booking_details.source" class="form-control" disabled id="source" type="text">
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Accomodation
                    <!--                    <span class="badge badge-info">{{booking_details.left_days}}</span>-->
                </h4>
            </div>
            <div class="form-row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="checkinDate">Check-In Date</label>
                        <input class="form-control" disabled id="checkinDate" type="text"
                               v-model="booking_details.checkin_date">
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="checkoutDate">Check-Out Date</label>
                        <input class="form-control" disabled id="checkoutDate"
                               type="text" v-model="booking_details.checkout_date">
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="checkinTime">
                            Est. Check-In Time
                            <span class="small ml-1 text-success" v-tooltip.top-center="'Estimated Check-In time of the Guest.'"  tabindex="0"
                                  title="Estimated Check-In time of the Guest."><i class="fas fa-info-circle"></i>
                            </span>
                        </label>
                        <select class="form-control" id="checkinTime" v-model="booking_details.arrival_time">
                            <option selected value="">Select Time</option>
                            <option value="15:00">15:00</option>
                            <option value="15:30">15:30</option>
                            <option value="16:00">16:00</option>
                            <option value="16:30">16:30</option>
                            <option value="17:00">17:00</option>
                            <option value="17:30">17:30</option>
                            <option value="18:00">18:00</option>
                            <option value="18:30">18:30</option>
                            <option value="19:00">19:00</option>
                            <option value="19:30">19:30</option>
                            <option value="20:00">20:00</option>
                            <option value="20:30">20:30</option>
                            <option value="21:00">21:00</option>
                            <option value="21:30">21:30</option>
                            <option value="22:00">22:00</option>
                            <option value="22:30">22:30</option>
                            <option value="23:00">23:00</option>
                            <option value="23:30">23:30</option>
                            <option value="00:00">00:00</option>
                            <option value="00:30">00:30</option>
                            <option value="01:00">01:00</option>
                            <option value="01:30">01:30</option>
                            <option value="02:00">02:00</option>
                            <option value="02:30">02:30</option>
                            <option value="03:00">03:00</option>
                            <option value="03:30">03:30</option>
                            <option value="04:00">04:00</option>
                            <option value="04:30">04:30</option>
                            <option value="05:00">05:00</option>
                            <option value="05:30">05:30</option>
                            <option value="06:00">06:00</option>
                            <option value="06:30">06:30</option>
                            <option value="07:00">07:00</option>
                            <option value="07:30">07:30</option>
                            <option value="08:00">08:00</option>
                            <option value="08:30">08:30</option>
                            <option value="09:00">09:00</option>
                            <option value="09:30">09:30</option>
                            <option value="10:00">10:00</option>
                            <option value="10:30">10:30</option>
                            <option value="11:00">11:00</option>
                            <option value="11:30">11:30</option>
                            <option value="12:00">12:00</option>
                            <option value="12:30">12:30</option>
                            <option value="13:00">13:00</option>
                            <option value="13:30">13:30</option>
                            <option value="14:00">14:00</option>
                            <option value="14:30">14:30</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label>Guests </label>
                        <div class="input-group mb-3">
                            <select class="custom-select" id="guestsAdults" v-model="booking_details.adults">
                                <option selected="" value="">Adults</option>
                                <option :value="adult" v-for="adult in 1000">{{adult}}</option>
                            </select>
                            <select class="custom-select" id="guestsChildren" v-model="booking_details.children">
                                <option selected="" value="">Children</option>
                                <option :value="children" v-for="children in 1000">{{children}}</option>
                            </select>
                        </div>
                        <small class="form-text text-error" v-if="booking_details.error_status.adults">{{booking_details.error_message.adults}}</small>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="comments">Guest Comments</label>
                        <textarea class="form-control" disabled id="comments" rows="3"
                                  v-model="booking_details.guest_comments"></textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="notes">Internal Notes</label>
                        <textarea class="form-control" id="notes" rows="3"
                                  v-model="booking_details.internal_notes"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Guest Info and Contacts</h4>
            </div>
            <div class="form-row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input :value="booking_details.first_name" class="form-control" disabled id="firstName"
                               type="text">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input :value="booking_details.last_name" class="form-control" disabled id="lastName"
                               type="text">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="form-control" id="email" type="email" v-model="booking_details.email">
                        <small class="form-text text-error" v-if="booking_details.error_status.email">{{booking_details.error_message.email}}</small>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input class="form-control" id="phone" type="text" v-model="booking_details.phone">
                        <small class="form-text text-error" v-if="booking_details.error_status.phone">{{booking_details.error_message.phone}}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="justify-content-center text-center">
            <button @click.prevent="saveBookingDetails({...booking_details, booking_id})" class="btn btn-success">Save
                Changes
            </button>
        </div>
    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';

    export default {
        name: 'BookingDetails',
        props: ['booking_id'],
        mounted() {
            this.fetchBookingDetails(this.booking_id);
        },
        methods: {
            ...mapActions('ba/', [
                'fetchBookingDetails',
                'saveBookingDetails'
            ])
        },
        computed: {
            ...mapState({
                booking_details: function (state) {
                    return state.ba.booking_detail.booking_details;
                }
            })
        }
    }
</script>