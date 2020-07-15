<template>
    <div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Arrival Information</h4>
            </div>
            <div class="form-row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="estArrivalTime">Est. Arrival Time
                            <span class="small ml-1 text-success" v-tooltip.top-center="'Estimated Check-In time of the Guest.'" tabindex="0"
                                  title="Estimated Check-In time of the Guest."><i class="fas fa-info-circle"></i>
                            </span>
                        </label>
                        <select class="form-control" id="estArrivalTime" v-model="guest_experience.arrival_time">
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
                        <small class="form-text text-error" v-if="guest_experience.error_status.arrival_time">{{guest_experience.error_message.arrival_time}}</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="arrivalBy">Arrival By</label>
                        <select class="form-control" id="arrivalBy" v-model="guest_experience.arriving_by">
                            <option value="">Choose..</option>
                            <option value="Car">Car</option>
                            <option value="Plane">Plane</option>
                            <option value="Bus">Bus</option>
                            <option value="Ship">Ship</option>
                            <option value="Train">Train</option>
                            <option value="Other">Other</option>
                        </select>
                        <small class="form-text text-error" v-if="guest_experience.error_status.arriving_by">{{guest_experience.error_message.arriving_by}}</small>
                    </div>
                </div>
                <div class="col-sm-4" v-if="!show_other">
                    <div :class="{'text-muted' : disable_flight_no }" class="form-group">
                        <label for="flightNumber">Flight Number</label>
                        <input :disabled="disable_flight_no" class="form-control" id="flightNumber" type="text"
                               v-model="guest_experience.plane_number">
                        <small class="form-text text-error" v-if="guest_experience.error_status.plane_number">{{guest_experience.error_message.plane_number}}</small>
                    </div>
                </div>
                <div class="col-sm-4" v-if="show_other">
                    <div class="form-group">
                        <label for="flightNumber">Other Detail</label>
                        <input class="form-control" type="text" v-model="guest_experience.other_detail">
                        <small class="form-text text-error" v-if="guest_experience.error_status.other_detail">{{guest_experience.error_message.other_detail}}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Pre-check-in Status</h4>
            </div>
            <div class="form-row" v-if="!guest_experience.is_precheckin_completed">
                <div class="col-8">
                    <div class="alert alert-warning py-2 px-3 text-md"><i class="fas fa-exclamation-triangle"> </i>
                        Pre-check-in Wizard not Complete!
                    </div>
                </div>
                <div class="col-4">
                    <button @click="resendPreCheckinWizardEmail(booking_id)"
                            class="btn btn-success btn-sm btn-block py-2"> Resend<span class="hidden-xs"> Pre-Check-In Wizard</span>
                    </button>
                </div>
            </div>
            <!--            <div class="form-row" v-if="!guest_experience.is_precheckin_completed && !guest_experience.is_confirmation_sent">-->
            <!--                <div class="col-8">-->
            <!--                    <div class="alert alert-warning py-2 px-3 text-md"> <i class="fas fa-exclamation-triangle"> </i>  Auto Confirmation Email sent 3 days ago</div>-->
            <!--                </div>-->
            <!--                <div class="col-4"><a class="btn btn-success btn-sm btn-block py-2" data-toggle="modal" href="" data-target="#confirmationModal"> Resend <span class="hidden-xs"> Confirmation Email</span></a></div>-->
            <!--            </div>-->
            <div class="form-row" v-if="guest_experience.is_precheckin_completed">
                <div class="col">
                    <div class="alert alert-success py-2 px-3 text-md"><i class="fas fa-check"> </i> Pre-check-in Wizard Successfully Completed!</div>
                </div>
            </div>
            <table class="table table-borderless table-sm text-md">
                <tr v-for="scan in guest_experience.scans">
                    <td class="pl-0">{{scan.title}}</td>
                    <td><span class="badge badge-success">Completed</span></td>
                    <td><a style="color:#0779F0; cursor: pointer;" @click.prevent="tab_section.current_tab = {'component_name': 'Documents', 'icon': 'fas fa-passport'}">{{scan.count}} file </a> received</td>
                </tr>
            </table>
        </div>
        <!--        <div class="mt-3 mb-4">-->
        <!--            <div class="card-section-title">-->
        <!--                <h4>Check-In Requirements</h4>-->
        <!--            </div>-->
        <!--            <div class="fw-500 text-normal mb-3">-->
        <!--                <div class="custom-control custom-switch mb-2">-->
        <!--                    <input class="custom-control-input" id="customSwitch1" type="checkbox" checked>-->
        <!--                    <label class="custom-control-label" for="customSwitch1">Override default settings</label>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--            <div class="text-normal">-->
        <!--                <div class="custom-control custom-switch mb-2">-->
        <!--                    <input class="custom-control-input" id="customSwitch2" type="checkbox">-->
        <!--                    <label class="custom-control-label" for="customSwitch2">Require scan passport in pre check-in</label>-->
        <!--                </div>-->
        <!--                <div class="custom-control custom-switch mb-2">-->
        <!--                    <input class="custom-control-input" id="customSwitch3" type="checkbox" checked>-->
        <!--                    <label class="custom-control-label" for="customSwitch3">Require credit card scan in pre check-in</label>-->
        <!--                </div>-->
        <!--                <div class="custom-control custom-switch mb-2">-->
        <!--                    <input class="custom-control-input" id="customSwitch4" type="checkbox" checked>-->
        <!--                    <label class="custom-control-label" for="customSwitch4">Set plane / train arrival details as mandatory in pre check-in</label>-->
        <!--                </div>-->
        <!--                <div class="custom-control custom-switch mb-2">-->
        <!--                    <input class="custom-control-input" id="customSwitch5" type="checkbox">-->
        <!--                    <label class="custom-control-label" for="customSwitch5">Hide full address until reservation code released in pre check-in</label>-->
        <!--                </div>-->
        <!--                <div class="custom-control custom-switch mb-2">-->
        <!--                    <input class="custom-control-input" id="customSwitch6" type="checkbox">-->
        <!--                    <label class="custom-control-label" for="customSwitch6">Require payment for stay in pre check-in</label>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Additional Info</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-sm text-md table-width-fix-md table-middle">
                    <tr>
                        <td class="pl-0">Last Portal visit by guest:</td>
                        <td :class="guest_experience.visit.guest_portal_class">{{guest_experience.visit.guest_portal}}</td>
                        <td><a :href="guest_experience.route.guest_portal" class="btn btn-xs">Visit Guest Portal</a></td>
                    </tr>
                    <tr>
                        <td class="pl-0">Last Pre checkin wizard visit by guest:</td>
                        <td :class="guest_experience.visit.precheckin_class">{{guest_experience.visit.precheckin}}</td>
                        <td><a :href="guest_experience.route.pre_checkin" class="btn btn-xs">Visit Pre-Check-In Wizard</a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="justify-content-center text-center">
            <button @click.prevent="saveGuestExperience({...guest_experience, booking_id})" class="btn btn-success">Save
                Changes
            </button>
        </div>
    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';

    export default {
        name: 'GuestExperience',
        props: ['booking_id'],
        mounted() {
            this.fetchGuestExperience(this.booking_id);
        },

        data() {
            return {
                disable_flight_no: false,
                show_other: false
            }
        },

        methods: {
            ...mapActions('general/', [
                'fetchGuestExperience',
                'saveGuestExperience'
            ]),

            resendPreCheckinWizardEmail(id) {
                self = this;
                self.$store.commit('SHOW_LOADER', null, {root: true});
                axios({
                    url: "/client/v2/resend-pre-checkin-wizard-email/",
                    method: 'POST',
                    data: {id: id},
                }).then((resp) => {
                    if (resp.data.status_code == 200) {
                        self.$store.commit('HIDE_LOADER', null, {root: true});
                        toastr.success(resp.data.message);
                    } else if (resp.data.status_code == 404) {
                        self.$store.commit('HIDE_LOADER', null, {root: true});
                        toastr.error(resp.data.message);
                    }
                });
            },
        },
        computed: {
            ...mapState({
                guest_experience: function (state) {
                    return state.general.booking_detail.guest_experience;
                },
                tab_section: function (state) {
                    return state.general.booking_detail.tab_section;
                }
            })
        },

        watch: {
            guest_experience: {
                deep: true,
                handler(new_val, old_val) {
                    this.disable_flight_no = (new_val.arriving_by !== 'Plane');
                    this.show_other = (new_val.arriving_by === 'Other');
                    this.guest_experience.plane_number = (new_val.arriving_by !== 'Plane') ? '' : this.guest_experience.plane_number;
                }
            }
        }
    }
</script>