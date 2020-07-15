<template>
    <div>

        <!--    Modal Pop-up Booking Source Begin-->
        <div @focusout="modalClosed('m_modal_edit')" aria-labelledby="exampleModalLabel" class="modal fade show" id="m_modal_edit"
             role="dialog" tabindex="-1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!--Modal Pop-up Booking Source Settings Setup Begin-->
                        <client-booking-source-settings :propertyInfoId='propertyInfoId'
                                                        :propertyInfoObjectIndex='propertyInfoObjectIndex'
                                                        :bookingSourceFormId='bookingSourceFormId'
                                                        :action='action'
                                                        @saved="reloadPropertyDetails"></client-booking-source-settings>
                        <!--Modal Pop-up Booking Source  Settings Setup End-->
                    </div>
                </div>
            </div>
        </div>
        <!--    Modal Pop-up Booking Source End-->

        <div class="card-section">
            <div class="card-section-title">Payment Rules Settings
                <ul :id="'channel-settings'+propertyInfo.id"
                    class="nav nav-pills custom-pills">
                    <li class="nav-item">
                        <input type="radio" value="0"
                               v-model="propertyInfo.use_bs_settings"
                               @click.prevent="useGlobalOrLocalBookingSourceSettings($event)"
                               :accessKey="index"
                               :data-id="propertyInfo.id"
                               data-status="global"
                               :name="['bs-settings_' + index]"
                        />
                        <label> Use Global Settings</label>
                    </li>
                    <li class="nav-item">
                        <input type="radio" value="1"
                               v-model="propertyInfo.use_bs_settings"
                               @click.prevent="useGlobalOrLocalBookingSourceSettings($event)"
                               :accessKey="index"
                               :data-id="propertyInfo.id"
                               data-status="local"
                               :name="['bs-settings_' + index]"
                        >
                        <label> Custom Settings</label>
                    </li>
                </ul>
            </div>
            <div :id="'channel-settings-pane'+propertyInfo.id" class="tab-content">
                <div :class="'tab-pane fade show '  + (propertyInfo.use_bs_settings == 0 ? 'active' : '')"
                     aria-labelledby="global-tab" id="'global-settings-pane'+propertyInfo.id"
                     role="tabpanel">
                    <div class="card-inset-table">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="pt-2 text-center">
                                        This property imports Global Booking Channel Settings.
                                        You can manage them on
                                        <a href="/client/v2/pms-setup-step-2">Settings Page</a>
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div :class="'tab-pane fade show '  + (propertyInfo.use_bs_settings != 0 ? 'active' : '')"
                     aria-labelledby="custom-tab" id="'custom-settings-pane'+propertyInfo.id"
                     role="tabpanel">
                    <div class="card-inset-table">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th colspan="2">Channel</th>
                                    <th>Card Authorization</th>
                                    <th>Payment Schedule</th>
                                    <th>Security Deposit</th>
                                    <th>Cancellation Policy</th>
                                </tr>
                                <tr v-for="(bookingSource, indexBS) in bookingSourceSettings" v-if="bookingSourceSettings.length>0">
                                    <td class="py-2 pr-0">
                                        <div class="display-initials-wrapper">
                                            <img :src="bookingSource.logo"
                                                 v-if="bookingSource.logo.length > 2"/>
                                            <span class="initial_icon"
                                                  v-if="bookingSource.logo.length < 3">{{ bookingSource.logo }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2 pl-0">
                                        <strong>{{bookingSource.name}}</strong><br/>
                                        <a @click.prevent="sendBookingSourceAndPropertyInfoIdToModal(propertyInfo.id, index, bookingSource.bookingSourceFormId, 'view')" class="small" data-target="#m_modal_edit"
                                           data-toggle="modal"
                                           href="#0">View</a>
                                    </td>
                                    <td class="py-2">
                                        {{(bookingSource.settings.booking_deposit.status ?
                                        'Enabled' : 'Disabled') }}
                                        <div class="small text-muted">
                                            {{bookingSource.settings.booking_deposit.details}}
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        {{(bookingSource.settings.booking_payment.status ?
                                        'Enabled' : 'Disabled') }}
                                        <div class="small text-muted">
                                            {{bookingSource.settings.booking_payment.details}}
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        {{(bookingSource.settings.security_deposit.status ?
                                        'Enabled' : 'Disabled') }}
                                        <div class="small text-muted">
                                            {{bookingSource.settings.security_deposit.details}}
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        {{(bookingSource.settings.return_rules.status ?
                                        'Enabled' : 'Disabled') }}
                                        <div class="small text-muted">
                                            {{bookingSource.settings.return_rules.details}}
                                        </div>
                                    </td>
                                    <td class="text-right py-2">
                                        <div class="dropdown dropdown-sm dropdown-compact float-right">
                                            <a aria-expanded="false"
                                               aria-haspopup="true" class="btn btn-xs dropdown-toggle" data-toggle="dropdown"
                                               href="#" id="dropdown-1"
                                               role="button"></a>
                                            <div aria-labelledby="dropdown-1"
                                                 class="dropdown-menu dropdown-menu-right"><a
                                                    @click.prevent="sendBookingSourceAndPropertyInfoIdToModal(propertyInfo.id, index, bookingSource.bookingSourceFormId, 'edit')" class="dropdown-item"
                                                    data-target="#m_modal_edit"
                                                    data-toggle="modal"
                                                    href="#">Edit</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center pt-3" colspan="7">
                                        <a @click="sendBookingSourceAndPropertyInfoIdToModal(propertyInfo.id, index, 0, 'edit')"
                                            class="btn btn-success btn-xs" data-target="#m_modal_edit"
                                            data-toggle="modal"
                                            href="#">Set Payment Rules</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    import ClientBookingSourceSettings from "../../../general/client/bookingSourceSetings/ClientBookingSourceSettings";
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';
    import {bus} from "../../../../app";
    Vue.use(VueToast);

    export default {

        name: 'PaymentSettingDetails',
        props: ['propertyInfo', 'index', 'bookingSourceSettings'],

        data() {
            return {
                msg: 'Please Wait...',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
                propertyInfoId: '',
                propertyInfoObjectIndex: '',
                bookingSourceFormId: '',
                action: '',
            }
        },

        components: {
            ClientBookingSourceSettings,
        },

        mounted() {
            bus.$on('getPropertyBookingSourcesWithDetail', this.getPropertyBookingSourcesWithDetail)
        },

        methods: {
            toasterView(msg, status = false) {
                let type = (status ? 'success' : 'error');
                Vue.$toast.open({message: msg, duration: 3000, type: type, position: 'top-right',});
            },

            getPropertyBookingSourcesWithDetail(propertyInfoId, index) {
                var _this = this;
                _this.deletePropertyInfoBookingSourceSettingsObject(index);
                if (_this.propertyInfo.use_bs_settings === 1) {
                    _this.block = true;
                    axios.post('/client/v2/get-property-bs-details', {'propertyInfoId': propertyInfoId})
                        .then(function (response) {
                            if (_this.propertyInfo.bookingSourceSettings === undefined) {
                                _this.$set(_this.propertyInfo, 'bookingSourceSettings', response.data.data);
                            } else {
                                _this.propertyInfo.bookingSourceSettings = response.data.data;
                            }
                            _this.block = false;
                        }).catch(function (error) {
                        console.log(error);
                        _this.block = false;
                        _this.toasterView('Failed to Get Booking Source Settings for Property #' + _this.propertyInfo.pms_property_id, false);
                    });
                }
            },

            deletePropertyInfoBookingSourceSettingsObject(propertyInfoIndex) {
                if (this.propertyInfo.bookingSourceSettings !== undefined)
                    delete this.propertyInfo.bookingSourceSettings;
            },

            useGlobalOrLocalBookingSourceSettings(event) {
                var _this = this;

                let propertyId = event.target.dataset.id;
                let useSettings = event.target.dataset.status;
                let propertyInfoIndex = event.target.accessKey;

                let msg = 'Do You Really Want to Use '+(useSettings==='local'?'Custom':'Global')+' Payment Rules Settings';
                swal.fire({
                    title: msg,
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, " +(useSettings==='local'?'Custom':'Global')+ " Settings!",
                    cancelButtonText: 'No, cancel'
                }).then(function (e) {
                    if (e.value === true) {
                        _this.block = true;
                        _this.deletePropertyInfoBookingSourceSettingsObject(propertyInfoIndex);
                        axios.post('/client/v2/use-property-bs-settings/' + propertyId, {'useSettings': useSettings})
                            .then(function (response) {

                                if (response.data.status) {
                                    _this.propertyInfo.use_bs_settings = (useSettings === 'local' ? 1 : 0);
                                    _this.getPropertyBookingSourcesWithDetail(propertyId, propertyInfoIndex);

                                } else {
                                    document.querySelector("#" + (useSettings === 'local' ? 'custom' : 'global') + "-tab" + propertyId).className = 'nav-link ';
                                    document.querySelector("#" + (useSettings === 'local' ? 'global' : 'custom') + "-tab" + propertyId).className = 'nav-link active';
                                }
                                _this.toasterView(response.data.message, response.data.status);
                                _this.block = false;
                            })
                            .catch(function (error) {
                                _this.toasterView('Booking Source Settings Updating fail for Property #' + _this.propertyInfos[propertyInfoIndex].pms_property_id, false);
                                document.querySelector("#" + (useSettings === 'local' ? 'custom' : 'global') + "-tab" + propertyId).className = 'nav-link ';
                                document.querySelector("#" + (useSettings === 'local' ? 'global' : 'custom') + "-tab" + propertyId).className = 'nav-link active disabled';
                                _this.block = false;
                                console.log(error);
                        });
                    }
                });
            },

            sendBookingSourceAndPropertyInfoIdToModal(propertyInfoId, propertyInfoObjectIndex, booking_source_form_id, action) {

                var _this = this;

                _this.propertyInfoId = propertyInfoId;
                _this.propertyInfoObjectIndex = propertyInfoObjectIndex;
                _this.bookingSourceFormId = booking_source_form_id;
                _this.action = action;
            },

            reloadPropertyDetails(propertyInfoIndex, settingsToReload) {
                this.getPropertyBookingSourcesWithDetail(this.propertyInfo.id, propertyInfoIndex); //Load BS Settings
            },

            modalClosed(modalId) {
                if (document.querySelector('#' + modalId).className == "modal fade") {
                    this.propertyInfoId = '';
                    this.propertyInfoObjectIndex = '';
                    this.bookingSourceFormId = 0;
                    this.action = '';
                }
            },
        },
    }
</script>