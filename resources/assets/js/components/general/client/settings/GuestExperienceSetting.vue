<template>
    <div>
        <div :class="account_setup ? 'setup-box account-setup-content' : 'setup-box'">
            <ul class="nav nav-pills custom-pills setup-steps nav-fill" id="channel_list" role="tablist">

                <template v-for="(booking_source, index) in available_booking_sources">
                    <li class="nav-item">
                        <a :aria-controls="'#channel-' + booking_source.channel_code"
                           :class="['nav-link',(booking_source.name === 'Booking.com' ? 'active show': '')]"
                           :data-booking_source_form_id="booking_source.id"
                           :data-channel_code="booking_source.channel_code"
                           :href="'#channel-' + booking_source.channel_code"
                           :id="'channel-' + booking_source.channel_code + '-tab'"
                           aria-selected="true"
                           data-toggle="pill"
                           role="tab">{{ booking_source.name }}</a>
                    </li>
                </template>

                <!-- Others tab title -->
                <li class="nav-item">
                    <a aria-controls="#channel-others"
                       aria-selected="true"
                       class="nav-link"
                       data-booking_source_form_id="0"
                       data-channel_code="0"
                       data-toggle="pill"
                       href="#channel-others"
                       id="channel-others-tab"
                       role="tab">Others</a>
                </li>
                <!-- Others tab title ends -->
            </ul>

            <div class="tab-content setup-body" id="bookings-tabContent">


                <template v-for="(booking_source, index) in available_booking_sources">
                    <div :aria-labelledby="'channel-' + booking_source.channel_code + '-tab'"
                         :class="['tab-pane fade',(booking_source.name === 'Booking.com' ? 'active show': '')]" :id="'channel-' + booking_source.channel_code"
                         role="tabpanel">
                        <template v-for="general_preference in available_general_preferences">
                            <div class="row">
                                <div class="col-9">
                                    <label class="font-weight-bold">{{ general_preference.name }}</label>
                                    <p class="small" v-html="general_preference.description"></p>
                                </div>
                                <div class="col-3">
                                    <div class="checkbox-toggle checkbox-choice float-right">
                                        <input :checked="set_on_off(booking_source.id, general_preference)" :data-activity="general_preference.form_id"
                                               :id="(general_preference.name).toLowerCase().split(' ').join('_')+booking_source.id"
                                               :name="general_preference.name"
                                               :value="set_on_off(booking_source.id, general_preference)"
                                               @change="tickBox"
                                               class="custom-control-input"
                                               type="checkbox">
                                        <label :for="(general_preference.name).toLowerCase().split(' ').join('_')+booking_source.id"
                                               class="checkbox-label"
                                               data-off="OFF" data-on="ON">
                                            <span class="toggle-track">
                                                                    <span class="toggle-switch"></span>
                                                                </span>
                                            <span class="toggle-title"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Others tab content starts here -->
                <div aria-labelledby="channel-others-tab " class="tab-pane fade" id="channel-others" role="tabpanel">
                    <template v-for="general_preference in available_general_preferences">
                        <div class="row">
                            <div class="col-9">
                                <label class="font-weight-bold">{{ general_preference.name }}</label>
                                <p class="small" v-html="general_preference.description"></p>
                            </div>

                            <div class="col-3">
                                <div class="checkbox-toggle checkbox-choice float-right">

                                    <input :checked="set_on_off(0, general_preference)"
                                           :data-activity="general_preference.form_id"
                                           :id="(general_preference.name).toLowerCase().split(' ').join('_')+'0'"
                                           :name="general_preference.name"
                                           :value="set_on_off(0, general_preference)"
                                           @change="tickBox"
                                           class="custom-control-input"
                                           type="checkbox">

                                    <label :for="(general_preference.name).toLowerCase().split(' ').join('_')+'0'"
                                           class="checkbox-label"
                                           data-off="OFF" data-on="ON">
                                       <span class="toggle-track">
                                                                    <span class="toggle-switch"></span>
                                                                </span>
                                        <span class="toggle-title"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <!-- Others tab content ends here -->
            </div>

        </div>
<!--        <loader v-show="preLoader"></loader>-->
        <BlockUI :html="'<i class=`fa fa-spinner fa-spin fa-3x fa-fw`></i>'" :message="'Please Wait'" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    export default {
        name: "guest-experience-setting",
        props: ['account_setup'],
        data() {
            return {
                // preLoader: false,
                block: false,
                available_general_preferences: [],
                user_preferences_settings: [],
                available_booking_sources: [],

                boxvl: {
                    name: '',
                    status: '',
                    form_id: '',
                    selected_channel_code: '',
                    selected_booking_source_form: ''
                }
            }
        },
        filters: {},
        methods: {

            /**
             * Get List of Supported Gateways
             */
            get_options() {
                this.block = true;
                let _this = this;
                axios.get('/client/v2/general-settings-data')
                    .then(function (response) {
                        if (response.data.status) {
                            _this.available_general_preferences = response.data.data.available_general_preferences;
                            _this.available_booking_sources = response.data.data.booking_sources;

                            _this.user_preferences_settings = response.data.data.user_preferences_settings;
                            _this.preLoader = false;
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    _this.block = false;
                });
            },

            /**
             * Get List of Supported Gateways
             */
            set_on_off(booking_source_id, current_general_setting_processing) {
                if (this.user_preferences_settings[current_general_setting_processing['form_id']] !== undefined && this.user_preferences_settings[current_general_setting_processing['form_id']][booking_source_id]) {
                    var user_setting_for_preference = JSON.parse(this.user_preferences_settings[current_general_setting_processing['form_id']][booking_source_id]['form_data']);
                    return user_setting_for_preference.status;
                } else
                    return current_general_setting_processing.status;
            },

            /**
             * Turn on/off single setting at time
             */
            tickBox(e) {
                let th = this;
                th.boxvl.name = e.target.name;
                th.boxvl.status = e.target.value;
                th.boxvl.form_id = e.target.dataset.activity;
                th.boxvl.selected_channel_code = $("ul#channel_list li a.active").data('channel_code');
                th.boxvl.selected_booking_source_form = $("ul#channel_list li a.active").data('booking_source_form_id');

                if (e.target.checked == false) {
                    th.boxvl.status = 0;
                    e.target.value = 0
                } else {
                    th.boxvl.status = 1;
                    e.target.value = 1
                }

                //send request to update new setting
                axios.post('/client/v2/general-preferences-box-settings', th.boxvl)
                    .then(function (response) {

                        if (response.data.status == true) {

                            //update intercom data
                            updateIntercomData('guest_experience');

                            //disable all options on frontend if Email To Guest is disabled
                            if(e.target.dataset.activity == 1 && e.target.checked == false) {
                                //disable all option for current active tab
                                $("#bookings-tabContent .tab-pane.active input[type=checkbox]").prop("checked", false);
                            }
                            else if(e.target.dataset.activity != 1 && e.target.checked == true){
                                //enable email to guest option for current active channel
                                $("#bookings-tabContent .tab-pane.active input[type=checkbox][data-activity=1]").prop("checked", true);
                            }

                            toastr.success(response.data.msg);
                        } else {

                            if (e.target.checked == false)
                                e.target.checked = true;
                            else
                                e.target.checked = false;

                            toastr.error(response.data.msg);
                            e.target.value = response.data.value;
                            e.target.checked = (response.data.value == 1 ? true : false);
                        }

                    }).catch(function (error) {
                    if (error.response.status == 401)
                        toastr.error("You don't have permission");
                    else
                        toastr.error('Failed to save settings');

                    if (e.target.checked == false)
                        e.target.checked = true;
                    else
                        e.target.checked = false
                });

            }

            /**
             * Save and validate PMS form keys
             */


        },//Methods End

        mounted() {
            this.get_options();
        },

    }
</script>
