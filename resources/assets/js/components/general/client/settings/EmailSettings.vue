<template>
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-header header-of-setting-pages mb-3">
                            <h1 class="page-title">Preferences Settings</h1>
                        </div>
                        <div class="page-body">
                            <div class="content-box">
                                <div class="setup-box">
                                    <div>
                                        <nav class=" nav setup-steps">

                                            <!--Email Settings-->
                                            <a aria-controls="#preferences" aria-selected="true"
                                               class="nav-link setup-step-item active show" data-toggle="pill" href="#preferences"
                                               id="preferences-tab" role="tab">
                                                <div class="step-icon"><i class="fas fa-user-cog"></i></div>
                                                <div class="setup-title">Email Settings</div>
                                            </a>

                                            <!--Notifications-->
                                            <a aria-controls="#notifications" aria-selected="true" class="nav-link setup-step-item"
                                               data-toggle="pill" href="#notifications"
                                               id="notifications-tab" role="tab">
                                                <div class="step-icon"><i class="fas fa-envelope"></i></div>
                                                <div class="setup-title">Notifications</div>
                                            </a>

                                            <!--Booking Sources-->
                                            <a aria-controls="#bookingSources" aria-selected="true" class="nav-link setup-step-item"
                                               data-toggle="pill" href="#bookingSources"
                                               id="booking-sources-tab" role="tab">
                                                <div class="step-icon"><i class="fas fa-cogs"></i></div>
                                                <div class="setup-title">Booking Sources</div>
                                            </a>
                                        </nav>
                                    </div>

                                    <div class="tab-content setup-body" id="bookings-tabContent">
                                        <div aria-labelledby="preferences-tab" class="tab-pane fade active show" id="preferences"
                                             role="tabpanel">
                                            <h4 class="setup-page-title text-muted">
                                                CHANGE BOOKING COLOR &amp; FLAG TEXT ON YOUR PMS
                                                <small class="blockquote-footer"
                                                       style="background: none; color: #486581;">
                                                    You can use following codes to make your information objects dynamic
                                                    [paymentSplitType] , [chargedAtDateTime] , [transactionAmount]
                                                    <a href="/client/v2/preferences-template-var-v2" target="_blank">(More
                                                        Template Variables . . . )</a>
                                                </small>
                                            </h4>


                                            <div role="tablist" class="m-accordion m-accordion--default">
                                                <div class="m-accordion__item">
                                                    <div class="m-accordion__item-head collapsed"  role="tab"  data-toggle="collapse" href="#ac_main_body_1" aria-expanded="false">
                                                        <span class="m-accordion__item-icon"><i class="fa fa-envelope"></i></span>
                                                        <span class="m-accordion__item-title">Parent Title</span>
                                                        <span class="m-accordion__item-mode"></span>
                                                    </div>
                                                    <div class="m-accordion__item-body collapse" role="tabpanel" id="ac_main_body_1">
                                                        <div class="m-accordion__item-content">

                                                            <div class="m-accordion__item">
                                                                <div class="m-accordion__item-head collapsed"  role="tab"  data-toggle="collapse" href="#ac_child_body_1" aria-expanded="false">
                                                                    <span class="m-accordion__item-title">Child Title</span>
                                                                    <span class="m-accordion__item-mode"></span>
                                                                </div>
                                                                <div class="m-accordion__item-body collapse" role="tabpanel" id="ac_child_body_1">
                                                                    <div class="m-accordion__item-content">

                                                                        <div class="row">
                                                                            <div class="col-md-9">
                                                                                <div class="form-group m-form__group">
                                                                                    <label for="subject" class="text-bold">Subject:</label>
                                                                                    <input id="subject" name="Subject" placeholder="Enter Subject" aria-describedby="subjectHelp" class="form-control m-input m-input--air">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-9">
                                                                                <div class="form-group m-form__group">
                                                                                    <label for="btnText"  class="text-bold">Button Text:</label>
                                                                                    <input id="btnText" name="btnText" placeholder="Enter Text On Button" aria-describedby="subjectHelp" class="form-control m-input m-input--air">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <div class="checkbox-toggle checkbox-choice float-right">
                                                                                    <input :checked="true" id="on_off_for_1"
                                                                                           :value="true"
                                                                                           @click="yourFunction($event)"
                                                                                           class="custom-control-input"
                                                                                           name="turn_on_off_preference"
                                                                                           type="checkbox">
                                                                                    <label for="on_off_for_1" class="checkbox-label" data-off="OFF" data-on="ON">
                                                                                        <span class="toggle-track">
                                                                                            <span class="toggle-switch"></span>
                                                                                        </span>
                                                                                        <span class="toggle-title"></span>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-9">
                                                                                <div class="form-group m-form__group">
                                                                                    <label class="text-bold">Content:</label>
                                                                                    <vue-editor :editor-toolbar="customToolbar"></vue-editor>
                                                                                </div>
                                                                            </div>
                                                                            <div class=" col-md-3 float-right">
                                                                                <div class="form-group m-form__group">
                                                                                    <label class="text-bold">Template Variables:</label>
                                                                                    <admin-dynamic-variables-list @selectVariable="addVariable"></admin-dynamic-variables-list>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <template v-for="(preference, form_index) in all_preferences">
                                                <!-- Start of preference card -->
                                                <div :id="'sourceBooking_'+form_index" class="accordion mb-2">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <a :aria-controls="'collapse_'+form_index"
                                                               :data-target="'#collapse_'+form_index"
                                                               :id="'click_id_'+form_index" aria-expanded="true"
                                                               class="booking-accordion-title collapsed"
                                                               data-toggle="collapse"
                                                               style="float:left; line-height:46px">
								                                <span>
																	<i :class="preference.icon_class"
                                                                       v-bind:style="{ color: preference.form_data.flag_color }"></i> {{ preference.name }}
							                                	</span>
                                                            </a>
                                                            <div class="checkbox-toggle checkbox-choice preferences-settings-page-active-deactive-button"
                                                                 style="float: right; padding: 0.85rem 10px 0.75rem 1rem;">
                                                                <input
                                                                        :checked="preference.status == 1"
                                                                        :data-activity="preference.form_id"
                                                                        :id="'on_off_for_'+preference.form_id"
                                                                        :value="preference.status"
                                                                        @click="onOffPreference($event)"
                                                                        class="custom-control-input"
                                                                        name="turn_on_off_preference"
                                                                        type="checkbox"
                                                                >
                                                                <label :for="'on_off_for_'+preference.form_id"
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
                                                    <div :aria-labelledby="'click_id_'+form_index" :id="'collapse_'+form_index"
                                                         class="collapse">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group m-form__group">
                                                                        <label :for="'flag_text_'+form_index">PMS Flag Text</label>
                                                                        <textarea
                                                                                :id="'flag_text_'+form_index"
                                                                                aria-describedby="flagTextHelp"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="Enter FlagText"
                                                                                v-model="preference.form_data.flag_text"
                                                                        >
							                                            </textarea>
                                                                        <span class="m-form__help">This will show the text of Flag Text.</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group m-form__group">
                                                                        <label :for="'flag_color_'+form_index">PMS Booking Color</label>
                                                                        <input
                                                                                :id="'flag_color_'+form_index"
                                                                                aria-describedby="emailHelp"
                                                                                class="form-control form-control-sm"
                                                                                type="color"
                                                                                v-model="preference.form_data.flag_color"
                                                                        >
                                                                        <span class="m-form__help">Choose the Flag color.</span>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group m-form__group">
                                                                        <label :for="'booking_status_'+form_index">PMS Booking
                                                                            Status</label>
                                                                        <select
                                                                                :id="'booking_status_'+form_index"
                                                                                class="form-control form-control-sm"
                                                                                v-model="preference.form_data.booking_status">

                                                                            <option>Select Status</option>
                                                                            <option value="Unchanged">Unchanged</option>
                                                                            <option value="Cancelled">Cancelled</option>
                                                                            <option value="Confirmed">Confirmed</option>
                                                                            <option value="New">New</option>
                                                                            <option value="Request">Request</option>
                                                                            <option value="Black">Black</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group m-form__group">
                                                                        <label :for="'notes_'+form_index">PMS Notes</label>
                                                                        <textarea
                                                                                :id="'notes_'+form_index"
                                                                                aria-describedby="notesHelp"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="ChargeAutomation Message:"
                                                                                v-model="preference.form_data.notes"
                                                                        >
							                                            </textarea>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group m-form__group">
                                                                        <label :for="'invoice_discription_'+form_index">PMS Payment Description</label>
                                                                        <textarea
                                                                                :id="'invoice_discription_'+form_index"
                                                                                aria-describedby="invoiceDescriptionHelp"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="ChargeAutomation Message:"
                                                                                v-model="preference.form_data.invoice_discription"
                                                                        >
							                                            </textarea>
                                                                        <!--<span class="m-form__help">Invoice Description.</span> -->
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group m-form__group">
                                                                        <label :for="'information_title_'+form_index">PMS Info Code</label>
                                                                        <input
                                                                                :id="'information_title_'+form_index"
                                                                                aria-describedby="notesHelp"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="Payment"
                                                                                type="text"
                                                                                v-model="preference.form_data.information_title"
                                                                        >
                                                                        <!-- <span class="m-form__help">Notes.</span> -->
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group m-form__group">
                                                                        <label :for="'information_detail_'+form_index">PMS Info Code Description</label>
                                                                        <textarea
                                                                                aria-describedby="invoiceDescriptionHelp"
                                                                                class="form-control form-control-sm"
                                                                                id="'information_detail_'+form_index"
                                                                                placeholder="Information Details"
                                                                                v-model="preference.form_data.information_detail"
                                                                        >
						                                            	</textarea>
                                                                        <!--   <span class="m-form__help">Information Detail.</span> -->
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <hr>

                                                            <div class="row">
                                                                <div class="col-md-2"></div>
                                                                <div class="col-md-10">
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <button
                                                                                    @click.prevent="saveCustomPreferences(form_index)"
                                                                                    class="btn btn-sm btn-primary"
                                                                                    type="button"
                                                                            >
                                                                                Save Custom Settings
                                                                            </button>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <button
                                                                                    @click.prevent="revertToDefault(preference.form_id)"
                                                                                    class="btn btn-sm btn-info"
                                                                                    type="button"
                                                                            >
                                                                                Revert to Default Settings
                                                                            </button>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <button
                                                                                    @click="reset(form_index)"
                                                                                    class="btn btn-sm btn-success"
                                                                                    type="button"
                                                                            >
                                                                                Reset Form
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End of preference card -->
                                            </template>
                                        </div>

                                        <!-- Notifications tab starts from here -->
                                        <div aria-labelledby="notifications-tab" class="tab-pane fade" id="notifications"
                                             role="tabpanel">
                                            <div class="row">

                                                <!-- Account Settings -->
                                                <div class="col-md-12" style="font-weight: bold; margin-top: 2rem;">
                                                    ACCOUNT RELATED ACTIVITIES
                                                </div>

                                                <template
                                                        v-for="setting in all_notification_settings.account_notifications">
                                                    <div class="col-md-12" style="padding-left: 3em; margin-top: 1em;">
                                                        <span style="font-weight: bold; font-size: 0.875rem">{{ setting.name }}</span>
                                                        <p class="small">{{ setting.desc }}</p>
                                                    </div>
                                                    <div class="col-md-12" style="padding-left: 3em">
                                                        <input :id="setting.id"
                                                               class="form-control make_it_token_input	"
                                                               ref="make_it_token_input" v-model="setting.to_email"/>
                                                    </div>
                                                </template>
                                                <!-- Account Settings ends -->

                                                <!-- Guest Settings -->
                                                <div class="col-md-12" style="font-weight: bold; margin-top: 2rem;">
                                                    GUEST RELATED ACTIVITIES
                                                </div>

                                                <template
                                                        v-for="setting in all_notification_settings.guest_notification">
                                                    <div class="col-md-12" style="padding-left: 3em;margin-top: 1em;">
                                                        <span style="font-weight: bold; font-size: 0.875rem">{{ setting.name }}</span>
                                                        <p class="small">{{ setting.desc }}</p>
                                                    </div>
                                                    <div class="col-md-12" style="padding-left: 3em">
                                                        <input :id="setting.id"
                                                               class="form-control make_it_token_input	"
                                                               ref="make_it_token_input" v-model="setting.to_email"/>
                                                    </div>
                                                </template>
                                                <!-- Guest Settings ends -->

                                                <!-- Payment Activity Settings -->
                                                <div class="col-md-12" style="font-weight: bold; margin-top: 2rem;">
                                                    PAYMENT RELATED ACTIVITIES
                                                </div>

                                                <template
                                                        v-for="setting in all_notification_settings.payment_activity_notification">
                                                    <div class="col-md-12" style="padding-left: 3em;margin-top: 1em;">
                                                        <span style="font-weight: bold; font-size: 0.875rem">{{ setting.name }}</span>
                                                        <p class="small">{{ setting.desc }}</p>
                                                    </div>
                                                    <div class="col-md-12" style="padding-left: 3em">
                                                        <input :id="setting.id"
                                                               class="form-control make_it_token_input	"
                                                               ref="make_it_token_input" v-model="setting.to_email"/>
                                                    </div>
                                                </template>
                                                <!-- Payment Activity Settings ends -->

                                            </div>
                                        </div>
                                        <!-- Notifications tab ends here -->

                                        <!-- BookingSources Settings Begin-->
                                        <div aria-labelledby="booking-sources-tab" class="tab-pane fade" id="bookingSources"
                                             role="tabpanel">
                                            <booking-channel-settings></booking-channel-settings>
                                        </div>
                                        <!-- BookingSources Settings End-->

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <loader v-show="preLoader"></loader>
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    import AdminDynamicVariablesList from "../../admin/reusables/AdminDynamicVariablesList";
    import {VueEditor} from "vue2-editor";

    export default {
        name: "preference-settings",

        components: {AdminDynamicVariablesList, VueEditor},

        created() {
            this.fetchPreferences();
            this.fetchNotifications();
        },
        data() {
            return {

                customToolbar: [
                    [{ header: [false, 1, 2, 3, 4, 5, 6] }],
                    ["bold", "italic", "underline"],
                    ["blockquote"],
                    [{ list: "ordered" }, { list: "bullet" }, { list: "check" }],
                    [
                        { align: "" },
                        { align: "center" },
                        { align: "right" },
                        { align: "justify" }
                    ],
                    ["clean"]
                ],

                preLoader: false,
                msg: 'Please Wait',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                all_preferences: {},

                all_notification_settings: {
                    account_notifications: {},
                    guest_notification: {},
                    payment_activity_notification: {}
                },

                mailbxvl: {
                    tovl: '',
                    ccvl: '',
                    bccvl: '',
                    activityId: '',
                    error: ''
                },

                preference_on_off: {
                    preferences_form_id: '',
                    status: ''
                },

                boxvl: {
                    name: '',
                    vl: '',
                    activityId: '',
                },
                account_status: 2
            }
        },
        methods: {
            fetchNotifications() {
                this.block = true;
                let self = this;
                axios({
                    url: '/client/v2/fetch-notification-settings',
                    method: 'GET',
                    headers: {
                        'content-type': 'application/json'
                    }
                })
                    .then((resp) => {
                        if (resp.data.status_code == '200') {
                            self.all_notification_settings.account_notifications = resp.data.data.account_notifications;
                            self.all_notification_settings.guest_notification = resp.data.data.guest_notification;
                            self.all_notification_settings.payment_activity_notification = resp.data.data.payment_activity_notification;

                            this.$nextTick(() => {
                                $(this.$refs.make_it_token_input)
                                    .tokenfield()
                                    .on('tokenfield:createtoken', function (e) {
                                        // Über-simplistic e-mail validation
                                        var re = /\S+@\S+\.\S+/;
                                        var valid = re.test(e.attrs.value);
                                        if (!valid) {
                                            toastr.error('Please enter valid email');
                                            return false;
                                        } else {
                                            self.addMails(e.currentTarget.value + ',' + e.attrs.value, e.currentTarget.id)
                                                .then(() => {
                                                    return true;
                                                })
                                                .catch(() => {
                                                    return false;
                                                });

                                        }
                                    })
                                    .on('tokenfield:removedtoken', function (e) {
                                        self.addMails(e.currentTarget.value, e.currentTarget.id);
                                    });
                            });

                        }
                        self.block = false;
                    })
                    .catch((err) => {
                        console.log(err);
                        self.block = false;
                    });
            },
            fetchPreferences() {
                this.block = true;
                let self = this;
                axios({
                    url: '/client/v2/fetch-preferences',
                    method: 'GET',
                    headers: {
                        'content-type': 'application/json'
                    }
                })
                    .then((resp) => {
                        if (resp.data.status_code == '200') {
                            //console.log(resp.data);
                            this.account_status = resp.data.account_status;
                            this.populateForms(resp.data.data);
                        }
                        self.block = false;
                    })
                    .catch((err) => {
                        console.log(err);
                        self.block = false;
                    });
            },
            populateForms(data) {
                this.all_preferences = data;
                //let self = this;
                // $.each(data, function(index, value) {
                // 	self.all_preferences['form'+index].status = value.status;
                // 	self.all_preferences['form'+index].form_data = JSON.parse(value.form_data);
                // });
            },
            saveCustomPreferences(form_index) {
                this.block = true;
                let self = this;
                let form = self.all_preferences[form_index];
                axios({
                    url: '/client/v2/save-custom-preferences',
                    method: 'POST',
                    headers: {
                        'content-type': 'application/json'
                    },
                    data: form
                })
                    .then((resp) => {
                        self.block = false;
                        toastr.success(resp.data.message);
                    })
                    .catch((err) => {
                        self.block = false;
                        console.log(err)
                    });
            },
            revertToDefaultEmail(form_id) {
                this.block = true;
                let self = this;

                axios({
                    url: '/client/v2/revertToDefaultSetting',
                    method: 'POST',
                    headers: {
                        'content-type': 'application/json'
                    },
                    data: {'id': form_id}
                })
                    .then((resp) => {
                        if (resp.data.status_code == '200') {
                            this.revertedFormPopulate(resp.data.data);
                            toastr.success(resp.data.message);
                        } else if (resp.data.status_code == '422') {
                            this.revertedFormPopulate(resp.data.data);
                            toastr.warning(resp.data.message);
                        } else {
                            toastr.error('Somthing Went Critically Wrong!');
                        }
                        self.block = false;
                    })
                    .catch((err) => {
                        console.log(err);
                        self.block = false;
                    });
            },
            revertedFormPopulate(data) {
                this.block = true;
                let self = this;
                if (data.form_id)
                    self.all_preferences[data.form_id].form_data = data.form_data;
                //self.all_preferences['form'+data.form_id].form_data = JSON.parse(data.form_data);

                //stop loader
                self.block = false;
            },
            reset(form_index) {
                this.block = true;

                let self = this;
                if (form_index) {
                    self.all_preferences[form_index].form_data = {
                        flag_text: '',
                        flag_color: '',
                        notes: '',
                        invoice_discription: '',
                        information_title: '',
                        information_detail: '',
                    }
                }

                //stop loader
                self.block = false;
            },

            onOffUserAccount(ev) {

                let self = this;

                swal.fire({
                    title: "Are you sure to change the Charge Automation status?",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes"
                }).then(function (e) {
                    if (e.value == true) {
                        self.block = true;
                        axios({
                            url: '/client/v2/company-status',
                            method: 'POST',
                            data: {status: ev.target.checked}
                        }).then((resp) => {
                            if (resp.data.status_code == 200) {
                                toastr.success(resp.data.message);
                                self.account_status = 1;
                            }
                            if (resp.data.status_code == 422) {
                                toastr.error(resp.data.message);
                                self.account_status = 2;
                                ev.target.checked = !ev.target.checked;
                            }
                            self.block = false;
                            window.location.reload();
                        }).catch((err) => {
                            console.log(err);
                            self.block = false;
                        });
                    } else {
                        ev.target.checked = !ev.target.checked;
                    }
                });

                // if (e.target.value == 1) {
                // 	// th.preference_on_off.status = 0;
                // 	e.target.value = 0
                // } else {
                // 	// th.preference_on_off.status = 1;
                // 	e.target.value = 1
                // }
            },

            onOffPreference(e) {
                this.block = true;
                let th = this;
                th.preference_on_off.preferences_form_id = e.target.dataset.activity;

                if (e.target.value == 1) {
                    th.preference_on_off.status = 0;
                    e.target.value = 0
                } else {
                    th.preference_on_off.status = 1;
                    e.target.value = 1
                }

                axios.post('/client/v2/preference-on-off', th.preference_on_off)

                    .then(function (response) {

                        if (response.data.status == 'success') {
                            toastr.success(response.data.message);
                        } else {
                            toastr.error(response.data.msg);
                            e.target.value = response.data.old_value;
                            e.target.checked = (response.data.old_value == 1 ? true : false);

                        }

                        //stop loader
                        th.block = false;
                    })
                    .catch(function (error) {

                        //stop loader
                        th.block = false;

                        toastr.error('Failed to save settings');
                    });
            },

            //following methods are for Notifications Settings tab
            tickBox(e) {
                this.block = true;
                let th = this;
                th.boxvl.name = e.target.name;
                th.boxvl.vl = e.target.value;
                th.boxvl.activityId = e.target.dataset.activity;

                if (th.boxvl.vl == 1) {
                    th.boxvl.vl = 0;
                    e.target.value = 0
                } else {
                    th.boxvl.vl = 1;
                    e.target.value = 1
                }
                axios.post('/client/v2/boxsettings', th.boxvl)

                    .then(function (response) {

                        if (response.data.status == true) {
                            toastr.success(response.data.msg);
                        } else {
                            toastr.error(response.data.msg);
                            e.target.value = response.data.value;
                            e.target.checked = (response.data.value == 1 ? true : false);

                        }

                        //stop loader
                        th.block = false;
                    })
                    .catch(function (error) {

                        //stop loader
                        th.block = false;

                        toastr.error('Failed to save settings');
                    });
            },

            addMails(emails, activityId) {
                this.block = true;

                let th = this;

                th.mailbxvl.tovl = emails;
                th.mailbxvl.activityId = activityId;

                return axios.post('/client/v2/mailsettings', th.mailbxvl)
                    .then(function (response) {

                        //stop loader
                        th.block = false;

                        if (response.data.status == true) {
                            toastr.success(response.data.msg);
                            return Promise.resolve();
                        } else if (response.data.status == false) {
                            toastr.error(response.data.msg);
                            return Promise.reject();
                        } else {
                            return Promise.reject();
                        }

                    }).catch(function (error) {
                        //stop loader
                        th.block = false;
                        toastr.error('Failed to save Settings!');
                        return Promise.reject();
                    });
            },
        }

    }
</script>