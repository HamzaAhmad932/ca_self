<template>
    <div class="page-content" id="properties-listing-page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header has-border-bottom mb-0">
                        <h1 class="page-title property-page-title d-inline">
                            <span>Properties<span class="text-muted"> - {{paginationResponse.total}}</span></span>

                            <button aria-controls="filter-collapse" aria-expanded="false" data-toggle="collapse"
                                    class="btn btn-sm btn-theme m-0 property-filter-btn filter-btn-sm-width sm-flex-auto d-inline-block d-md-none"
                                    href="#filter-collapse" role="button">
                                <i class="fas fa-filter"> </i>
                                <span class="d-none d-xs-inline d-sm-inline"> Filter</span>
                                <div class="filter-badge" v-if="filter_count > 0">{{filter_count}}</div>
                            </button>

                            <button class="btn btn-sm btn-success sync-booking-btn-sm-width md-0 d-inline-block d-md-none" id="sync_properties_btn0" @click.prevent="getProperties(1, true)">
                                <i class="fa fa-sync"></i> Re-Sync Properties
                            </button>
                        </h1>

                        <button aria-controls="filter-collapse" aria-expanded="false" data-toggle="collapse" role="button"
                                class="btn btn-sm btn-theme ml-2 filter-btn-margin sm-flex-auto d-none d-md-inline-block"
                                href="#filter-collapse" style="margin-top: -6px">
                            <i class="fas fa-filter"> </i>
                            <span class="d-none d-xs-inline d-sm-inline"> Filter</span>
                            <div class="filter-badge" v-if="filter_count > 0">{{filter_count}}</div>
                        </button>

                        <div class="booking-filter-stack">
                            <div class="d-flex sm-flex-auto">
                                <select @change="getProperties()" class="custom-select custom-select-sm mb-2 mr-1"
                                        id="inlineFormInputName3" ref="recordsPerPage"
                                        v-model="filters.per_page">
                                    <option selected value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <div class="d-flex sm-flex-auto">
                                <select @change="exportProperties()" class="custom-select custom-select-sm mb-2 mr-1"
                                        id="filter-export" style="min-width: 10rem;" v-model="exportType">
                                    <option value=""> Select To Export</option>
                                    <option value="csv">CSV</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>

                            <button class="btn btn-sm btn-success mb-2 ml-1 sm-flex-auto d-none d-md-block" id="sync_properties_btn" @click.prevent="getProperties(1, true)">
                                <i class="fa fa-sync"></i> Re-Sync Properties
                            </button>
                        </div>
                    </div>

                    <div class="page-body">
                        <div class="content-box">
                            <div class="collapse" id="filter-collapse">
                                <div class="filter-form">
                                    <div class="form-row align-items-end">
                                        <div class="form-group col-md-3 col-sm-5">
                                            <label for="filter-search">Search</label>
                                            <input @keyup.prevent="searchProperties($event)" class="form-control form-control-sm" id="filter-search"
                                                   placeholder="Start typing …"
                                                   type="text" v-model="filters.search">
                                        </div>
                                        <div class="form-group col-md-2 col-sm-5">
                                            <label for="filter-cities">All Cities</label>
                                            <select @change.prevent="getProperties()" class="custom-select custom-select-sm"
                                                    id="filter-cities" v-model="filters.city">
                                                <option value="all">All</option>
                                                <option :value="city" v-for="city in cities"
                                                        v-if="city != null && city.length > 0"> {{ city }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-1 col-sm-2">
                                            <a @click.prevent="resetFilters()"
                                               class="btn btn-sm btn-block btn-outline-danger float-right" href="#" id="reset-btn">Reset</a>
                                        </div>
                                        <div class="form-group col-md-4 ml-auto d-inline-flex">
                                            <select class="custom-select custom-select-sm mb-2 mr-1"
                                                    id="inlineFormInputName2" style="min-width:10rem;"
                                                    v-model="bulkActionVal">
                                                <option value="">Bulk Action</option>
                                                <option value="connect">Connect Properties</option>
                                                <option value="disconnect">Disconnect Properties</option>
                                            </select>
                                            <button @click="bulkAction()" class="btn btn-sm btn-outline-secondary mb-2"
                                                    type="submit">Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Sorting Options (hidden on desktop)-->
                            <div class="booking-table-filter-mobile d-block d-lg-none">
                                <div class="form-group">
                                    <select @change.prevent="getProperties()" class="custom-select custom-select-sm"
                                            v-model="filters.sortColumn">
                                        <option selected value="">Sort by:</option>
                                        <option value="id">PMS ID</option>
                                        <option value="name">Name</option>
                                        <option value="address">Address</option>
                                        <option value="currency_code">Currency</option>
                                        <option value="status">PMS Status</option>
                                        <option value="status">Connection Status</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Desktop Sorting Options (hidden on mobile) -->
                            <div class="table-header d-none d-lg-block">
                                <div class="row">
                                    <div class="col-2">
                                        <div class="table-box-check d-flex">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" id="sorter-check"
                                                       type="checkbox"
                                                       ref="checkBoxForAllProperties"
                                                       v-model="checkBoxForAllProperties"
                                                       @click="checkUncheckAllcheckBoxs">
                                                <label class="custom-control-label" for="sorter-check"></label>
                                            </div>
                                            <a href="#0"> <span>PMS Property ID</span></a>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <a :class="selectedSortOrder" @click.prevent="selectedSortOrderChanged()" href="#0">
                                            <span>Name</span>
                                        </a>
                                    </div>
                                    <div class="col-3"><a href="#0"> <span>Address</span></a></div>
                                    <div class="col-1"><a href="#0"> <span>Currency</span></a></div>
                                    <div class="col-3"><a href="#0"> <span>PMS Connection Status</span></a></div>
                                    <div class="col-1 pl-2"><a href="#0"> <span>Action</span></a></div>
                                </div>
                            </div>

                            <!-- Property Card-->
                            <div :class="'property-card ' + (propertyInfo.status == 1 ? 'property-connected' : 'property-disconnected')"
                                 :key="propertyInfo.id" v-for="(propertyInfo, index) in propertyInfos">
                                <div class="card-pane">
                                    <div class="for-booking-list-page-only-outer t-b-padding-lg-10">
                                        <div class="row no-gutters for-booking-list-page-only-inner">
                                            <div class="col-2 col-style">
                                                <div class="table-box-check d-flex">
                                                    <div class="custom-control custom-checkbox">
                                                        <input :data-propertyInfoId="propertyInfo.id"
                                                               :data-propertyInfoIndex="index"
                                                               :id="'check-01-'+propertyInfo.id"
                                                               class="custom-control-input"
                                                               name="checkedPropertyInList"
                                                               type="checkbox"
                                                               :value="propertyInfo.id"
                                                               v-model="checkedPropertyIds">
                                                        <label :for="'check-01-'+propertyInfo.id"
                                                               class="custom-control-label"></label>
                                                    </div>
                                                    {{ propertyInfo.pms_property_id }}
                                                </div>
                                            </div>
                                            <div class="col-2 col-style">
                                                <div class="single-line">
                                                    <span class="property-img-wrapper-at-propertieslist">
                                                        <img :src="['/storage/uploads/property_logos/' +  propertyInfo.logo ]" @error="imageUrlAlt" class="img-responsive">
                                                    </span>
                                                    <span class="property-name-wrapper-at-propertieslist">
                                                        {{ propertyInfo.name.substring(0, 13) }}
                                                        {{propertyInfo.name.length > 13 ? '...':''}}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-3 col-style">
                                                <div class="single-line text-muted">
                                                    {{ propertyInfo.address }} -- {{ propertyInfo.city }}
                                                </div>
                                            </div>
                                            <div class="col-1 col-style pl-4 pl-lg-0 text-center">
                                                {{ propertyInfo.currency_code }}
                                            </div>
                                            <div class="col-3 col-style" style="padding: 0 10px;">
                                                <div class="checkbox-toggle checkbox-choice">
                                                    <input :checked="propertyInfo.status == 1" :id="'checkbox-'+propertyInfo.id"
                                                           :name="'checkbox-'+propertyInfo.id"
                                                           @change="connectOrDisconnectProperty($event, (propertyInfo.status == 1 ? 'disconnect' : 'connect'), index, propertyInfo.id)"
                                                           type="checkbox"/>
                                                    <label :for="'checkbox-'+propertyInfo.id" class="checkbox-label"
                                                           data-off="OFF" data-on="ON">
                                                        <span class="toggle-track">
                                                            <span class="toggle-switch"></span>
                                                        </span>
                                                        <span class="toggle-title"></span>
                                                    </label>
                                                </div>
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top"
                                                      data-toggle="tooltip" title="Property Connected"
                                                      v-if="propertyInfo.status == 1">
                                                    <i class="fas fa-check-circle"></i> Connected
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip"
                                                      title="Property Disconnected"
                                                      v-else>
                                                    <i class="fas fa-exclamation-triangle"></i> Disconnected
                                                </span>
                                            </div>
                                            <div class="col-1 col-style">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="dropdown dropdown-sm">
                                                        <a aria-expanded="false" aria-haspopup="true" class="btn btn-xs dropdown-toggle"
                                                           data-toggle="dropdown" href="#" id="moreMenu"
                                                           role="button">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </a>
                                                        <div aria-labelledby="moreMenu"
                                                             class="dropdown-menu dropdown-menu-right">
                                                            <a @click="editAndSyncPropertyModal(propertyInfo.id)" class="dropdown-item" data-target="#m_modal_edit3"
                                                               data-toggle="modal"
                                                               href="#"
                                                               title="Update Property Info and Sync from PMS">
                                                                Edit & Sync Property
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a :href="'#property-collapse'+propertyInfo.id"
                                   @click.prevent="getBookingDetails($event.target, propertyInfo.id, index)"
                                   aria-controls="'property-collapse'+propertyInfo.id" aria-expanded="false" class="card-collapse collapsed"
                                   data-toggle="collapse" role="button">
                                    <i class="fas fa-chevron-up"></i>
                                </a>
                                <div :id="'property-collapse'+propertyInfo.id" class="property-card-body collapse">
                                    <div class="property-card-details">
                                        <div class="row">
                                            <div class="col col-sm-6">
                                                <div class="form-group">
                                                    <label class="form-label-sm">Timezone</label>
                                                    <input :value="propertyInfo.time_zone" class="form-control form-control-sm" readonly="readonly"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <PaymentSettingDetails :propertyInfo="propertyInfo" :index="index" :bookingSourceSettings="propertyInfos[index].bookingSourceSettings"></PaymentSettingDetails>


                                    <!--Payment Gateway Settings General Component-->
                                    <general-property-gateway-settings :propertyInfos="propertyInfos"
                                                                       :propertyInfoId="propertyInfo.id"
                                                                       :propertyInfo="propertyInfo"
                                                                       :index="index"></general-property-gateway-settings>
                                    <!--Modal Pop-up Payment Gateways Settings Setup End-->

                                </div>
                                <!-- Property Card-->
                            </div>

                            <div class="property-card property-connected" v-if="paginationResponse.total == 0">
                                <div class="card-pane">
                                    <div class="row no-gutters">
                                        <div class="col-12">
                                            No property found.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="float: right !important;">
                        <pagination :data="paginationResponse" :limit="1"
                                    @pagination-change-page="getProperties"></pagination>
                    </div>
                </div>
            </div>
        </div>

        <!--Modal Pop-up Property Info Update  Begin-->
        <div aria-hidden="true" aria-labelledby="updatePropertyInfo" class="modal fade" id="m_modal_edit3" role="dialog"
             tabindex="-1">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <property-info-update-modal
                            :propertyInfoId="propertyInfoIdForSyncModal"></property-info-update-modal>
                </div>
            </div>
        </div>
        <!--Modal Pop-up Property Info Update  End-->

        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
        <download-csv :data="propertiesToExport" :name="file_name + ' (City - ' + filters.city+ ').csv'"
                      v-if="exportType === 'csv'">
            <span ref="csvDownload" class="d-none">Downloading CSV</span>
        </download-csv>
    </div>
</template>

<script>
    import {bus} from '../../../../app';
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';
    import JsonCSV from 'vue-json-csv';
    import pdfMake from "pdfmake/build/pdfmake";
    import pdfFonts from "pdfmake/build/vfs_fonts";
    import PropertyInfoUpdateModal from "./PropertyInfoUpdateModal";
    import PaymentSettingDetails from "./PaymentSettingDetails";

    Vue.use(VueToast);
    Vue.component('downloadCsv', JsonCSV);
    pdfMake.vfs = pdfFonts.pdfMake.vfs;
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    export default {
        data() {
            return {
                msg: 'Please Wait...',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                exportType: '',
                propertyInfos: {},
                paginationResponse: {},
                propertyInfosBookingSourceSettings: [],
                propertyInfoIdForSyncModal: '',
                propertyInfoId: '',
                propertyInfoObjectIndex: '',
                bookingSourceFormId: '',
                action: '',
                selectedPaymentGatewayFormID: 0,
                actionPaymentGateway: '',
                selectedSortOrder: 'selected sort-asc',
                checkedPropertyIds: [],
                checkBoxForAllProperties: false,
                cities: {},
                bulkActionVal: '',
                file_name: 'Properties-List',
                filter_count: 0,
                filters: {
                    per_page: 10,
                    search: '',
                    city: 'all',
                    sortOrder: 'Asc',
                    sortColumn: 'name',
                },
                propertiesToExport: [],
            }
        },
        components: {
            PropertyInfoUpdateModal,
            PaymentSettingDetails,
        },
        mounted() {
            this.getProperties();
            this.getAllPropertiesCities();

        },
        methods: {

            /**
             * Pop Up Modal for updating property info
             * @param propertyInfoId
             */
            editAndSyncPropertyModal(propertyInfoId) {
                this.propertyInfoIdForSyncModal = propertyInfoId;
            },

            checkUncheckAllcheckBoxs(){
                this.checkedPropertyIds = [];
                if (!this.checkBoxForAllProperties) {
                    for (let i in this.propertyInfos) {
                        this.checkedPropertyIds.push(this.propertyInfos[i].id);
                    }
                }
            },


            /**
             * check or uncheck all checkbox functionality
             */
            /*checkBoxForAllPropertiesChanged() {
                this.checkUncheckAllcheckBoxs();

                let _this = this;
                $.each(document.getElementsByName('checkedPropertyInList'), function (key, event) {
                    event.checked = _this.checkBoxForAllProperties;
                })

            },*/

            async exportProperties() {
                if ((this.exportType === 'csv') || (this.exportType === 'pdf')) {
                    let _this = this;
                    let today = new Date();
                    _this.file_name = 'Properties-List-' + today.getDate() + '-' + months[today.getMonth()] + '-' + today.getFullYear();
                    _this.block = true;
                    _this.msg = 'Exporting Properties';
                    if(_this.checkedPropertyIds.length>0){
                        var propertyInfoIds = [];
                        if(this.$refs.checkBoxForAllProperties.checked === false){
                            var propertyInfoIds = _this.checkedPropertyIds;
                        }
                        await axios.post('/client/v2/export-properties-list', {
                            'city': _this.filters.city,
                            'propertyInfoIds': propertyInfoIds})
                            .then(response => {
                                _this.propertiesToExport = response.data.data;
                            });

                        _this.block = false;
                        if (_this.exportType === 'csv') {
                            _this.clickDownloadCsv();
                        } else if (_this.exportType === 'pdf') {
                            _this.generatePDF();
                        }
                        _this.msg = 'Please Wait';
                    }
                    else{
                        this.exportType = '';
                        _this.toasterView('Not  any property selected to export', false);
                        _this.block = false;
                    }
                }
            },


            clickDownloadCsv() {
                this.$refs.csvDownload.click();
                this.exportType = '';
            },

            /**
             * Toaster View on any action
             * @param msg
             * @param status
             */
            toasterView(msg, status = false) {
                let type = (status ? 'success' : 'error');
                Vue.$toast.open({message: msg, duration: 3000, type: type, position: 'top-right',});
            },

            chkAction(e) {
                return e === true ? false : true;
            },

            /**
             * connect or Disconnect Properties in Bulk
             */
            bulkAction() {
                let _this = this;
                let propertyInfoIds = [];
                if ((_this.bulkActionVal === 'connect') || (_this.bulkActionVal === 'disconnect')) {
                    let msg = _this.bulkActionVal == 'connect' ?
                        'ChargeAutomation will start processing all bookings against this property' :
                        'Are you sure you want to disconnect this property from Charge Automation?' + '\n \n' +
                        'Disconnecting will mean payments for new bookings at this property will not be processed through Charge Automation.' + '\n \n' +
                        'Existing reservations at this property, with scheduled payments already captured in Charge Automation, will be processed as normal.';
                    swal.fire({
                        title: msg,
                        type: "question",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, " + _this.bulkActionVal + "!",
                        cancelButtonText: 'No, cancel'
                    }).then(function (e) {
                        if (e.value === true) {
                            let count = 0;
                            $.each(document.getElementsByName('checkedPropertyInList'), function (key, event) {
                                if (event.checked) {
                                    propertyInfoIds.push(event.dataset.propertyinfoid);
                                }
                            });
                            if (propertyInfoIds.length === 0)
                                _this.toasterView('Not  any Property Selected to ' + _this.bulkActionVal, false);
                            else
                                _this.connectOrDisconnectAllProperties(_this.bulkActionVal, propertyInfoIds);
                        }
                    });
                }
            },

            /**
             * Connect | Disconnect Properties InBulk => All Properties
             */
            connectOrDisconnectAllProperties(_status, propertyInfoIds) {
                let _this = this;
                _this.msg = "Please Wait " + _status + "ing Properties to PMS";
                _this.block = true;
                status = _status == 'connect' ? 1 : 0;
                axios.post('/client/v2/ba/bulk-connect-disconnect-properties', {
                    'status': status,
                    'propertyInfoIds': propertyInfoIds
                })
                    .then(function (response) {
                        _this.block = false;
                        _this.msg = "Please Wait ";
                        if (response.data.status) {
                            let successCount = response.data.data.success.length;
                            let failedCount = response.data.data.failed.length;
                            let propertiesWithOutKeyCount = response.data.data.propertiesWithOutKey.length;
                            if (propertiesWithOutKeyCount > 0)
                                _this.toasterView('Property Key not Valid for Property # ' + response.data.data.propertiesWithOutKey.toString(), false);
                            $.each(response.data.data.failed, function (keyResponse, errorMsg) {
                                _this.toasterView(errorMsg, false);
                            });
                            if (successCount > 0) {
                                _this.getProperties();
                                _this.toasterView(successCount + (successCount == 1 ? ' Out of ' + (successCount + failedCount + propertiesWithOutKeyCount) + ' Property ' : ' Properties ') + _status + 'ed ', true);

                                //update Intercom data
                                if(_status=='connect')
                                    updateIntercomData('properties_connected', {'no_of_properties':successCount});
                                else
                                    updateIntercomData('properties_disconnected', {'no_of_properties':successCount});
                            }
                        } else {
                            _this.toasterView(response.data.message, false);
                        }
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                    _this.msg = "Please Wait ";
                    _this.toasterView('Failed to ' + _status + ' Properties', false);
                });
            },


            /**
             * Change Sort Order By Name Column ASc or DESc For Desktop
             */
            selectedSortOrderChanged() {
                this.filters.sortColumn = 'name';
                if (this.selectedSortOrder === 'selected sort-desc') {
                    this.selectedSortOrder = 'selected sort-asc';
                    this.filters.sortOrder = 'Asc';
                } else {
                    this.selectedSortOrder = 'selected sort-desc';
                    this.filters.sortOrder = 'Desc';
                }
                this.getProperties();
            },

            /**
             * Getting Properties List Using Pagination
             */
            getProperties(page = 1, sync = false) {
                this.block = true;
                var _this = this;
                _this.msg = "Loading Properties";
                axios.post('/client/v2/ba/get-properties?page=' + page, {'filters': _this.filters, 'sync': sync})
                    .then(function (response) {
                        let count = 0;
                        if (_this.filters.city != 'all') {
                            count++;
                        }
                        if (_this.filters.search != '') {
                            count++;
                        }
                        if (_this.filters.per_page != '10') {
                            count++;
                        }
                        _this.filter_count = count;
                        _this.block = false;
                        _this.msg = "Please Wait";

                        if (sync === true) {
                            if(response.data.status) {
                                _this.paginationResponse = response.data.data;
                                _this.propertyInfos = _this.paginationResponse.data;
                                toastr.success("Properties successfully synced.");
                            } else {
                                toastr.error(response.data.message);
                            }
                        } else {
                            _this.paginationResponse = response.data.data;
                            _this.propertyInfos = _this.paginationResponse.data;
                        }


                    })
                    .catch(function (error) {
                        if (sync === true) {
                            toastr.error("Some error while syncing properties.");
                        } else {
                            toastr.error("Some error while fetching properties.");
                        }
                    });
            },

            /**
             * Getting Properties Cities List
             */
            getAllPropertiesCities() {
                var _this = this;
                axios.get('/client/v2/get-all-properties-cities')
                    .then(response => {
                        _this.cities = response.data;
                    });
            },

            getBookingDetails(event, propertyInfoId, index) {
                if (event.className !== 'card-collapse') {
                    bus.$emit('getPropertyBookingSourcesWithDetail', {propertyInfoId, index});
                    bus.$emit('getPreviousPaymentGatewaySettings', index);
                }
            },

            /**
             * @param event
             * @param _status
             * @param propertyInfoIndex
             * @param propertyInfoId
             * @param bulk
             */
            connectOrDisconnectProperty(event, _status, propertyInfoIndex, propertyInfoId, bulk = false) {
                let _this = this;
                if (bulk) {
                    _this.connectDisconnectPropertyAxios(event, _status, propertyInfoIndex, propertyInfoId, true);
                } else {
                    let msg = _status == 'connect' ?
                        'ChargeAutomation will start processing all bookings against this property' :
                        'Are you sure you want to disconnect this property from Charge Automation?' + '\n \n' +
                        'Disconnecting will mean payments for new bookings at this property will not be processed through Charge Automation.' + '\n \n' +
                        'Existing reservations at this property, with scheduled payments already captured in Charge Automation, will be processed as normal.';
                    swal.fire({
                        title: msg,
                        type: "question",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, " + _status + "!",
                        cancelButtonText: 'No, cancel'
                    }).then(function (e) {
                        if (e.value === true) {
                            _this.connectDisconnectPropertyAxios(event, _status, propertyInfoIndex, propertyInfoId, false);
                        } else {
                            event.target.checked = _this.chkAction(event.target.checked);
                        }
                    });
                }
            },

            /**
             * @param event
             * @param _status
             * @param propertyInfoIndex
             * @param propertyInfoId
             * @param fromBulkAction
             */

            connectDisconnectPropertyAxios(event, _status, propertyInfoIndex, propertyInfoId, fromBulkAction = false) {
                let _this = this;
                _this.block = true;
                let status = _status == 'connect' ? 1 : 0;
                axios.post('/client/v2/ba/connect-disconnect-property', {
                    'propertyInfoId': _this.propertyInfos[propertyInfoIndex].id,
                    'status': status
                })
                    .then(function (response) {
                        // console.log(response);
                        if (response.data.status) {
                            if (_this.propertyInfos[propertyInfoIndex].id == propertyInfoId)
                                _this.propertyInfos[propertyInfoIndex].status = status;

                            //update intercom data
                            (status==1) ? updateIntercomData('property_connected'):updateIntercomData('property_disconnected');

                            _this.toasterView(response.data.message, true);
                        } else {
                            _this.propertyInfos[propertyInfoIndex].status = status == 0 ? 1 : 0;
                            if (!fromBulkAction)
                                event.target.checked = _this.chkAction(event.target.checked);
                            _this.toasterView(response.data.message, false);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                    if (!fromBulkAction)
                        event.target.checked = _this.chkAction(event.target.checked);
                    _this.toasterView('Failed to ' + _status + ' Property #' + _this.propertyInfos[propertyInfoIndex].pms_property_id, false);
                });
            },

            /**
             *
             * @param $event
             */
            searchProperties($event) {
                if ($event.keyCode === 13) {
                    this.getProperties();
                }
            },
            /**
             * Export Properties as PDF
             */
            generatePDF() {
                let bodyHeaders = [
                    {text: 'Pms id', style: 'tableHeader'},
                    {text: 'Name', style: 'tableHeader'},
                    {text: 'Currency', style: 'tableHeader'},
                    {text: 'Property Key', style: 'tableHeader'},
                    {text: 'TimeZone', style: 'tableHeader'},
                    {text: 'Payment Gateway', style: 'tableHeader'},
                    {text: 'Payment Rules', style: 'tableHeader'},
                    {text: 'Address', style: 'tableHeader'},
                    {text: 'Country', style: 'tableHeader'},
                    {text: 'City', style: 'tableHeader'},
                    {text: 'Status', style: 'tableHeader'},
                ];

                let bodyRows = [];
                bodyRows.push(bodyHeaders);
                $.each(this.propertiesToExport, function (key, value) {
                    bodyRows.push([
                        {text: value['Pms id'], style: 'tdStyle'},
                        {text: value['Name'], style: 'tdStyle'},
                        {text: value['Currency'], style: 'tdStyle'},
                        {text: value['Property Key'], style: 'tdStyle'},
                        {text: value['Timezone'], style: 'tdStyle'},
                        {text: value['Payment Gateway'], style: 'tdStyle'},
                        {text: value['Payment Rules'], style: 'tdStyle'},
                        {text: value['Address'], style: 'tdStyle'},
                        {text: value['Country'], style: 'tdStyle'},
                        {text: value['city'], style: 'tdStyle'},
                        {text: value['Connection Status'], style: 'tdStyle'},
                    ]);
                });
                var documentDefinition = {

                    header: {
                        text: 'Properties Details', style: 'header'
                    },
                    content:
                        [
                            {
                                table:
                                    {
                                        headerRows: 1,
                                        width: ['*', '*', '*', '*', '*', '*', '*', '*', '*', '*', '*'],
                                        body: bodyRows,
                                    },
                            },
                        ],
                    footer: {
                        text: 'PDF Generated By Charge Automation on ' + new Date(Date.now()).toDateString(),
                        style: 'footerCaption'
                    },
                    styles:
                        {
                            header: {
                                fontSize: 18,
                                bold: true,
                                margin: [0, 10, 0, 30],
                                alignment: 'center',
                            },
                            tableHeader: {
                                fontSize: 10,
                                fillColor: '#4CAF50',
                                color: 'white',
                                alignment: 'center',
                            },
                            footerCaption: {
                                fontSize: 11,
                                bottom: 0,
                                margin: [10, 0, 0, 0],
                                alignment: 'center'
                            },
                            tdStyle: {
                                fontSize: 8,
                            }
                        },
                    pageOrientation: 'landscape',
                };
                pdfMake.createPdf(documentDefinition).download(this.file_name + ' (City - ' + this.filters.city + ').pdf');
                this.exportType = '';
            },
            resetFilters() {
                this.filters.per_page = 10;
                this.filters.search = '';
                this.filters.city = 'all';
                this.filters.sortOrder = 'ASC';
                this.filters.sortColumn = 'name';
                this.getProperties();
            },
            imageUrlAlt(event) {
                event.target.src = "/storage/uploads/property_logos/no_image.png";
            }

        },
        /*Vue Methods End*/
        // computed(){
        // },
        /*watch: {
            checkBoxForAllProperties: function () {
                this.checkBoxForAllPropertiesChanged();
            }
        },*/
    }
</script>
