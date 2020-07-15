<template>
    <div class="card-section">
        <div class="card-section-title">Payment Gateway Settings
            <ul class="nav nav-pills custom-pills" id="gateway-settings">
                <li class="nav-item">
                    <input type="radio" :name="'pg-settings_'+index"  value="0"  v-model="propertyInfo.use_pg_settings" @click.prevent="useGlobalOrLocalPaymentGatewaySettings(index, 'global')" /> <label> Use Global Settings </label>
                </li>
                <li class="nav-item">
                    <input type="radio" :name="'pg-settings_'+index"  value="1"  v-model="propertyInfo.use_pg_settings" @click.prevent="useGlobalOrLocalPaymentGatewaySettings(index, 'local')"> <label> Custom Settings </label>
                </li>
            </ul>
        </div>
        <div class="form-group row mt-2 mb-0">
            <label class="col-3 col-sm-9 mb-0 text-right" style="margin-top:3px">
                <small>Currency</small></label>
            <div class="col col-sm-3">
                <input :value="propertyInfo.currency_code" class="form-control form-control-sm"
                       readonly="readonly"/>
            </div>
        </div>
        <div :id="'gateway-settings-pane'+propertyInfo.id" class="tab-content">
            <div :aria-labelledby="'global-tab2'+propertyInfo.id"
                 :class="(propertyInfo.use_pg_settings == 0 ? 'tab-pane fade show active' : 'tab-pane fade')" :id="'global-settings-pane2'+propertyInfo.id"
                 role="tabpanel">
                <div class="card-inset-table">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th class="pt-2 text-center">This property imports
                                    Global Payment Gateway Settings. You can manage them
                                    on <a href="/client/v2/pms-setup-step-3">Settings
                                        Page</a></th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div :aria-labelledby="'custom-tab2'+propertyInfo.id"
                 :class="(propertyInfo.use_pg_settings != 0 ? 'tab-pane fade show active' : 'tab-pane fade')" :id="'custom-settings-pane2'+propertyInfo.id"
                 role="tabpanel">
                <div class="card-inset-table">
                    <div class="table-responsive">
                        <table class="table">
                            <tr v-if="propertyInfo.paymentGatewaySettings">
                                <td class="py-2 pr-0"
                                    style="vertical-align:middle; !important">
                                    <div class="booking-list-item-src pr-0 mr-1"
                                         style="margin-left:30%">
                                        <img :src="propertyInfo.paymentGatewaySettings.logo"
                                             alt=""/>
                                    </div>
                                    <strong class="h6" style="margin-left:30%">{{propertyInfo.paymentGatewaySettings.name}}</strong>
                                </td>
                                <td class="py-2" colspan="2">
                                    <div class="form-group mb-0"
                                         v-for="(credential, credentialIndex) in propertyInfo.paymentGatewaySettings.credentials">
                                        <label :title="credential.desc"
                                               class="form-label-sm"
                                               v-if="credential.type != 'button'">{{credential.label}}</label>
                                        <input :name="credential.name"
                                               :type="credential.safe ?  credential.type : 'password'"
                                               :value="credential.value"
                                               class="form-control form-control-sm"
                                               readonly v-if="credential.type != 'button'"/>
                                    </div>
                                </td>

                                <td class="py-2 text-right"
                                    style="vertical-align:top !important">
                                    <div class="dropdown dropdown-sm dropdown-compact float-right">
                                        <a aria-expanded="false"
                                           aria-haspopup="true" class="btn btn-xs dropdown-toggle" data-toggle="dropdown"
                                           href="#" id="dropdown-3"
                                           role="button"></a>
                                        <div aria-labelledby="dropdown-3"
                                             class="dropdown-menu dropdown-menu-right">
                                            <a @click="sendPropertyInfoIdAndActionToPaymentGatewayModal(propertyInfo.id, index, propertyInfo.paymentGatewaySettings.paymentGatewayFormId,'update')" class="dropdown-item"
                                               data-target="#m_modal_edit2"
                                               data-toggle="modal"
                                               href="#">Edit
                                                {{propertyInfo.paymentGatewaySettings.name}}
                                                Keys</a>
                                            <a @click="sendPropertyInfoIdAndActionToPaymentGatewayModal(propertyInfo.id, index, propertyInfo.paymentGatewaySettings.paymentGatewayFormId,'change')" class="dropdown-item"
                                               data-target="#m_modal_edit2"
                                               data-toggle="modal"
                                               href="#">Add
                                                New
                                                {{propertyInfo.paymentGatewaySettings.name}}
                                                Account</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center pt-3" colspan="4"><a
                                        @click="sendPropertyInfoIdAndActionToPaymentGatewayModal(propertyInfo.id, index, 0,'add')" class="btn btn-success btn-xs"
                                        data-target="#m_modal_edit2" data-toggle="modal"
                                        href="#">Add
                                    Payment Gateway</a></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!--    Modal Pop-up Payment Gateways Settings  Begin-->
        <div @focusout="modalClosed('m_modal_edit2')" aria-labelledby="exampleModalLabel" class="modal fade show" id="m_modal_edit2"
             role="dialog" tabindex="-1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <h4 class="setup-page-title">Payment Gateway Settings</h4>
                        </h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!--Modal Pop-up Payment Gateways Settings Setup Begin-->
                        <client-payment-gateway-settings :action="actionPaymentGateway"
                                                         :propertyInfoId="propertyInfoId"
                                                         :propertyInfoObjectIndex="propertyInfoObjectIndex"
                                                         :selectedPaymentGatewayFormID="selectedPaymentGatewayFormID"
                                                         @saved="reloadPropertyDetails"></client-payment-gateway-settings>
                        <!--Modal Pop-up Payment Gateways Settings Setup End-->
                    </div>
                </div>
            </div>
        </div>
        <!--    Modal Pop-up Payment Gateways Settings  End-->

        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>

    </div>
</template>

<script>
    import {bus} from '../../../../app';
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';

    Vue.use(VueToast);

    export default {
        props: ['propertyInfos', 'propertyInfo', 'index'],
        data() {
            return {
                msg: 'Please Wait...',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                selectedPaymentGatewayFormID: 0,
                propertyInfoObjectIndex: '',
                actionPaymentGateway: '',
                propertyInfoId: ''
            }
        },
        components: {

        },
        mounted() {
            bus.$on('getPreviousPaymentGatewaySettings', this.getPreviousPaymentGatewaySettings)
        },
        methods: {

            /**
             * Toaster View on any action
             * @param msg
             * @param status
             */
            toasterView(msg, status = false) {
                let type = (status ? 'success' : 'error');
                Vue.$toast.open({message: msg, duration: 3000, type: type, position: 'top-right',});
            },

            /**
             *Chane User Payment Gateway  for specific property useSettings 1 => Custom or Local | 0 => Global
             * @param propertyInfoIndex
             * @param useSettings
             */
            useGlobalOrLocalPaymentGatewaySettings(propertyInfoIndex, useSettings) {
                var _this = this;

                let msg = 'Do You Really Want to Use '+(useSettings==='local'?'Custom':'Global')+' Payment Gateway Settings';
                swal.fire({
                    title: msg,
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, " +(useSettings==='local'?'Custom':'Global')+ " Settings!",
                    cancelButtonText: 'No, cancel'
                }).then(function (e) {
                    if (e.value === true) {
                        _this.block = true;
                        axios.post('/client/v2/use-property-pg-settings/' + _this.propertyInfos[propertyInfoIndex].id, {'useSettings': useSettings})
                            .then(function (response) {
                                _this.toasterView(response.data.message, response.data.status);
                                if (response.data.status) {
                                    _this.propertyInfos[propertyInfoIndex].use_pg_settings = (useSettings === 'local' ? 1 : 0);
                                    if (useSettings === 'local') {
                                        _this.getPreviousPaymentGatewaySettings(propertyInfoIndex);
                                    }
                                } else {
                                    document.querySelector("#" + (useSettings === 'local' ? 'custom' : 'global') + "-settings-pane2" + _this.propertyInfos[propertyInfoIndex].id).className = 'nav-link ';
                                    document.querySelector("#" + (useSettings === 'local' ? 'global' : 'custom') + "-settings-pane2" + _this.propertyInfos[propertyInfoIndex].id).className = 'nav-link active';
                                }
                                _this.block = false;
                            }).catch(function (error) {
                            console.log(error);
                            _this.toasterView('Payment Gateway Settings Updating fail for Property #' + _this.propertyInfos[propertyInfoIndex].pms_property_id, false);
                            document.querySelector("#" + (useSettings === 'local' ? 'custom' : 'global') + "-settings-pane2" + _this.propertyInfos[propertyInfoIndex].id).className = 'nav-link ';
                            document.querySelector("#" + (useSettings === 'local' ? 'global' : 'custom') + "-settings-pane2" + _this.propertyInfos[propertyInfoIndex].id).className = 'nav-link active';
                            _this.block = false;
                        });
                    }else{
                        // useSettings = useSettings === 'local'?'global':'local';
                        // _this.propertyInfos[propertyInfoIndex].use_pg_settings = (useSettings === 'local' ? 1 : 0);
                        // document.querySelector("#" + (useSettings === 'local' ? 'custom' : 'global') + "-settings-pane2" + _this.propertyInfos[propertyInfoIndex].id).className = 'nav-link ';
                        // document.querySelector("#" + (useSettings === 'local' ? 'global' : 'custom') + "-settings-pane2" + _this.propertyInfos[propertyInfoIndex].id).className = 'nav-link active';
                    }
                });
            },

            deletePropertyInfoPaymentGatewaySettingsObject(propertyInfoIndex) {
                if (this.propertyInfos[propertyInfoIndex].paymentGatewaySettings !== undefined)
                    delete this.propertyInfos[propertyInfoIndex].paymentGatewaySettings;
            },

            modalClosed(modalId) {
                if (document.querySelector('#' + modalId).className == "modal fade") {
                    this.propertyInfoId = '';
                    this.propertyInfoObjectIndex = '';
                    this.selectedPaymentGatewayFormID = '';
                    this.actionPaymentGateway = '';
                }
            },

            getPreviousPaymentGatewaySettings(propertyInfoIndex) {
                if (this.propertyInfos[propertyInfoIndex].use_pg_settings == 1) {
                    let _this = this;
                    _this.block = true;
                    _this.deletePropertyInfoPaymentGatewaySettingsObject(propertyInfoIndex);
                    axios.post('/client/v2/get-property-local-payment-gateway/', {'propertyInfoId': _this.propertyInfos[propertyInfoIndex].id})
                        .then(function (response) {
                            if (response.data.status) {
                                if (_this.propertyInfos[propertyInfoIndex].paymentGatewaySettings === undefined) {
                                    _this.$set(_this.propertyInfos[propertyInfoIndex], 'paymentGatewaySettings', response.data.data);
                                } else {
                                    _this.propertyInfos[propertyInfoIndex].paymentGatewaySettings = response.data.data;
                                }
                            } else if (response.data.status_code !== 1000) {
                                _this.toasterView('Fail to load Payment Gateway for Property #' + _this.propertyInfos[propertyInfoIndex].pms_property_id, false);
                            }
                            _this.block = false;
                        }).catch(function (error) {
                        console.log(error);
                        _this.block = false;
                        _this.toasterView('Fail to load Payment Gateway for Property #' + _this.propertyInfos[propertyInfoIndex].pms_property_id, false);
                    });
                }

            },

            /**
             * @param propertyInfoIndex
             * @param settingsToReload  => bookingSource | paymentGateway
             */
            reloadPropertyDetails(propertyInfoIndex, settingsToReload) {
                this.getPreviousPaymentGatewaySettings(propertyInfoIndex); //Load PaymentGateway Settings
                // if (settingsToReload === 'bookingSource') {
                //     this.getPropertyBookingSourcesWithDetail(this.propertyInfos[propertyInfoIndex].id, propertyInfoIndex); //Load BS Settings
                // } else if (settingsToReload === 'paymentGateway') {
                //     this.getPreviousPaymentGatewaySettings(propertyInfoIndex); //Load PaymentGateway Settings
                // }
            },

            /**
             *
             * @param propertyInfoId
             * @param propertyInfoIndex
             * @param selectedPaymentGatewayFormID
             * @param actionPaymentGateway
             */
            sendPropertyInfoIdAndActionToPaymentGatewayModal(propertyInfoId, propertyInfoIndex, selectedPaymentGatewayFormID, actionPaymentGateway) {
                this.propertyInfoId = propertyInfoId;
                this.propertyInfoObjectIndex = propertyInfoIndex;
                this.selectedPaymentGatewayFormID = selectedPaymentGatewayFormID;
                this.actionPaymentGateway = actionPaymentGateway;
            }

        }
    }
</script>


<style scoped>

</style>