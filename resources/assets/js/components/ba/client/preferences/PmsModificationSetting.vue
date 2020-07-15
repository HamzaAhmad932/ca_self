<template>
    <div>
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
                <div :aria-labelledby="'click_id_'+form_index"
                     :id="'collapse_'+form_index"
                     class="collapse">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group m-form__group">
                                    <label :for="'flag_text_'+form_index">PMS Flag
                                        Text</label>
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
                                    <label :for="'flag_color_'+form_index">PMS
                                        Booking Color</label>
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
                                    <label :for="'booking_status_'+form_index">PMS
                                        Booking
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
                                    <label :for="'notes_'+form_index">PMS
                                        Notes</label>
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
                                    <label :for="'invoice_discription_'+form_index">PMS
                                        Payment Description</label>
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
                                    <label :for="'information_title_'+form_index">PMS
                                        Info Code</label>
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
                                    <label :for="'information_detail_'+form_index">PMS
                                        Info Code Description</label>
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
        <BlockUI :html="'<i class=`fa fa-spinner fa-spin fa-3x fa-fw`></i>'" :message="'Please Wait'" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    export default {
        name: "BaPmsModificationSetting",
        data() {
            return {
                block: false,
                all_preferences: {},
                preference_on_off: {
                    preferences_form_id: '',
                    status: ''
                },
            }
        },
        methods: {

            fetchPreferences() {
                this.block = true;
                let self = this;
                axios({
                    url: '/client/v2/ba/fetch-preferences',
                    method: 'GET',
                    headers: {
                        'content-type': 'application/json'
                    }
                }).then((resp) => {
                    if (resp.data.status_code == '200') {
                        //console.log(resp.data);
                        this.account_status = resp.data.account_status;
                        this.populateForms(resp.data.data);
                    }
                    self.block = false;
                }).catch((err) => {
                    console.log(err);
                    self.block = false;
                });
            },

            populateForms(data) {
                this.all_preferences = data;
            },

            saveCustomPreferences(form_index) {
                this.block = true;
                let self = this;
                let form = self.all_preferences[form_index];
                axios({
                    url: '/client/v2/ba/save-custom-preferences',
                    method: 'POST',
                    headers: {
                        'content-type': 'application/json'
                    },
                    data: form
                }).then((resp) => {
                    self.block = false;
                    toastr.success(resp.data.message);
                }).catch((err) => {
                    self.block = false;
                    console.log(err)
                });
            },

            revertToDefault(form_id) {
                this.block = true;
                let self = this;

                axios({
                    url: '/client/v2/revertToDefaultSetting',
                    method: 'POST',
                    headers: {
                        'content-type': 'application/json'
                    },
                    data: {'id': form_id}
                }).then((resp) => {
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
                }).catch((err) => {
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
                    }).catch(function (error) {
                    th.block = false; //stop loader
                    toastr.error('Failed to save settings');
                });
            },

        },
        mounted() {
            this.fetchPreferences();
        }
    }
</script>

<style scoped>

</style>