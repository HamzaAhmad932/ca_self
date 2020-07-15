<template v-if="$can('accountSetup')">
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <account-enable-disable-button></account-enable-disable-button>
                        <div class="page-body pmsintegration_page_content">
                            <div class="content-box">
                                <div class="setup-box account-setup-content">
                                    <!-- STEPS NAV-BAR BEGIN-->
                                    <pms_setup_steps_navbar :step="5"></pms_setup_steps_navbar>
                                    <!-- STEPS NAV-BAR END-->
                                    <div class="setup-body">
                                        <h4 class="setup-page-title">Enable properties to sync new bookings</h4>
                                        <div class="row justify-space-between">
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <input @keyup="searchProperties($event)" class="form-control form-control-sm"
                                                           placeholder="Search..."
                                                           type="text" v-model="filters.search.searchStr">
                                                </div>
                                            </div>
                                            <div class="col-md-8 col-lg-6 ml-auto">
                                                <div class="connection-btn-stack">
                                                    <!--                              <a class="btn btn-sm btn-secondary px-3" href="#0"  data-toggle="modal" data-target="#generateKeyModal"  @click="generateApiKey()">Generate Key</a>-->
                                                    <div class="btn-group">
                                                        <a @click="getProperties(1, true)"
                                                           class="btn btn-sm btn-secondary px-3 font-10" href="javaScript:void(0)"
                                                           title="Import property changes from PMS">
                                                            <i class="fa fa-sync" title="Re-sync Now"></i>
                                                            Re-sync Now
                                                        </a>
                                                        <a @click="connectOrDisconnectAllProperties('connect')"
                                                           class="btn btn-sm btn-secondary px-3 font-10"
                                                           href="javaScript:void(0)">
                                                            <i class="fas fa-plug" title="Connect All"></i>
                                                            Connect All
                                                        </a>
                                                        <a @click="connectOrDisconnectAllProperties('disconnect')"
                                                           class="btn btn-sm btn-secondary px-3 font-10"
                                                           href="javaScript:void(0)">
                                                            <i class="fas fa-unlink" title="Disconnect All"></i>
                                                            Disconnect All
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-inset-table">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <tr>
                                                        <th>PMS Property ID</th>
                                                        <th>Name</th>
                                                        <th class="text-center">Currency</th>
                                                        <th>PMS Connection</th>
                                                        <th>Status</th>
                                                    </tr>
                                                    <tr v-for="(propertyInfo, index) in propertyInfos.data">
                                                        <td>{{propertyInfo.pms_property_id}}</td>
                                                        <td><strong>{{propertyInfo.name}}</strong>
                                                            <div class="small text-muted">{{propertyInfo.address}}</div>
                                                        </td>
                                                        <td class="text-center">{{propertyInfo.currency_code}}</td>
                                                        <td>
                                                            <div class="checkbox-toggle checkbox-choice">
                                                                <input :checked="propertyInfo.status == 1"
                                                                       :id="'checkbox-'+propertyInfo.id" @change="connectOrDisconnectProperty((propertyInfo.status == 1 ? 'disconnect' : 'connect'), index, propertyInfo.id)"
                                                                       name="'checkbox-'+propertyInfo.id"
                                                                       type="checkbox"
                                                                       v-bind:ref="'checkbox-'+propertyInfo.id"/>
                                                                <label :for="'checkbox-'+propertyInfo.id"
                                                                       class="checkbox-label" data-off="OFF"
                                                                       data-on="ON">
                                            <span class="toggle-track">
                                                <span class="toggle-switch"></span>
                                            </span>
                                                                    <span class="toggle-title"></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="badge badge-success"
                                                                 v-if="(propertyInfo.status == 1)">
                                                                <i class="fas fa-check-circle"></i>
                                                                Connected
                                                            </div>
                                                            <div class="badge badge-danger" v-else>
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                Disconnected
                                                            </div>
                                                        </td>

                                                    </tr>
                                                    <tr>
                                                        <td class="text-right" colspan="5">
                                                            <div style="float:right ;padding-top: 20px; !important">
                                                                <pagination :data="propertyInfos"
                                                                            @pagination-change-page="getProperties"></pagination>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </div>

                                        </div>

                                    </div>
                                    <div class="setup-footer d-flex justify-content-center">
                                        <!--<a class="btn btn-light align-self-start setup-back text-muted" href="/client/v2/pms-setup-step-1"> <i class="fas fa-arrow-left"></i><span> Back </span></a>-->
                                        <a class="btn btn-light align-self-start setup-back text-muted width-not-apply"
                                           href="/client/v2/pms-setup-step-4"> <i class="fas fa-arrow-left"></i><span> Back </span></a>
                                        <a class="btn btn-success px-md-4" href="/client/v2/dashboard">Save and Finish
                                            <i class="fas fa-arrow-right"></i></a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--begin::Modal-->
        <div aria-hidden="true" aria-labelledby="API-Key" class="modal fade show" id="generateKeyModal" role="dialog"
             style="padding-top: 15%;" tabindex="-1">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Property Key</h5>
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button> -->
                    </div>
                    <div class="modal-body">
                        <p>
                            You can use this Key as a Property Key on PMS !
                        </p>
                        <small id="generated_key">Please Wait Generating Api Key...</small>
                        <input id="generated_key_hidden_input" type="hidden" value="">

                    </div>
                    <div class="modal-footer">
                        <button @click.stop.prevent="copyApiKeyToClipBoard()" class="btn btn-success" id="copyKey" type="button"
                                v-if="isCopyToClipBoardReady">Copy to Clipboard
                        </button>
                        <button class="btn btn-secondary" data-dismiss="modal" type="button">Close</button>

                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal-->


        <loader v-show="preLoader"></loader>
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    import Pms_setup_steps_navbar from "../../../general/client/pms_integration/pms_setup_steps_navbar";

    export default {
        name: "ba-pms-setup-step5",
        components: {Pms_setup_steps_navbar},

        data() {
            return {
                preLoader: false,
                msg: 'Please Wait',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                isCopyToClipBoardReady: false,
                filters: {
                    recordsPerPage: 10,
                    page: 1,
                    columns: ['id', 'user_account_id', 'pms_property_id', 'name', 'available_on_pms', 'address', 'currency_code', 'property_key', 'status'], //main table columns
                    relations: [],
                    sort: {
                        sortOrder: "ASC",
                        sortColumn: "name",
                    },
                    constraints: [
                        ['available_on_pms', '=', 1],
                    ],
                    search: {
                        searchInColumn: ['id', 'pms_property_id', 'name', 'address', 'currency_code'],
                        searchStr: ""
                    },
                }, //Datatable filters Object End
                propertyInfos: {},
            }
        },
        methods: {

            /**`
             * Toaster View on any action
             * @param msg
             * @param status
             */
            toasterView(msg, status = false) {
                if (status)
                    toastr.success(msg);
                else
                    toastr.error(msg);
            },

            /**
             * Getting Properties List Using Pagination
             */
            getProperties(page = 1, sync = false) {
                this.block = true;
                var _this = this;
                _this.msg = 'Loading Properties',
                    _this.filters.page = page;
                axios.post('/client/v2/ba/get-properties-list-for-master-settings', {
                    'filters': _this.filters,
                    'sync': sync,
                    'page': page
                })
                    .then(function (response) {
                        _this.block = false;
                        _this.msg = 'Please Wait';

                        if (sync === true) {
                            if(response.data.status) {
                                toastr.success("Properties successfully synced.");
                            } else {
                                toastr.error(response.data.message);
                                return;
                            }
                        }

                        _this.propertyInfos = response.data.data;
                    })
                    .catch(function (error) {
                        console.log(error);
                        _this.block = false;
                        if (sync === true) {
                            toastr.error("Some error while syncing properties. Please Re-sync now.");
                        } else {
                            toastr.error("Some error while fetching properties.");
                        }
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
             * @param _status
             * @param propertyInfoIndex
             * @param propertyInfoId
             */
            connectOrDisconnectProperty(_status, propertyInfoIndex, propertyInfoId) {
                this.msg = "Please Wait " + _status + "ing Property to PMS";
                let _this = this;
                _this.block = true;
                status = _status == 'connect' ? 1 : 0;
                let checkbox = this.$refs['checkbox-' + propertyInfoId][0];

                axios.post('/client/v2/ba/pms-connect-disconnect-property', {
                    'propertyInfoId': _this.propertyInfos.data[propertyInfoIndex].id,
                    'status': status
                })
                    .then(function (response) {
                        if (response.data.status) {
                            if (_this.propertyInfos.data[propertyInfoIndex].id == propertyInfoId) {
                                _this.propertyInfos.data[propertyInfoIndex].status = status;
                                _this.propertyInfos.data[propertyInfoIndex].property_key = 'fake_key_to_update_status';
                            }

                            //update intercom data
                            (status==1) ? updateIntercomData('property_connected'):updateIntercomData('property_disconnected');

                            _this.toasterView(response.data.message, true);
                        } else {
                            if (_this.propertyInfos.data[propertyInfoIndex].id == propertyInfoId)
                                _this.propertyInfos.data[propertyInfoIndex].status = status == 0 ? 1 : 0;
                            _this.toasterView(response.data.message, false);
                            checkbox.checked = status == 0 ? 1 : 0;
                        }
                        _this.block = false;
                        _this.msg = "Please Wait";

                    }).catch(function (error) {

                    console.log(error);
                    _this.block = false;
                    _this.msg = "Please Wait";
                    _this.propertyInfos.data[propertyInfoIndex].status = status == 0 ? 1 : 0;
                    _this.toasterView('Failed to ' + _status + ' Property #' + _this.propertyInfos.data[propertyInfoIndex].pms_property_id, false);
                    checkbox.checked = status == 0 ? 1 : 0;
                });

            },


            /**
             * Connect | Disconnect Properties InBulk => All Properties
             */
            connectOrDisconnectAllProperties(_status) {
                let _this = this;
                this.msg = "Please Wait " + _status + "ing Properties to PMS";
                _this.block = true;
                status = _status == 'connect' ? 1 : 0;
                axios.post('/client/v2/ba/bulk-connect-disconnect-properties-xml', {'status': status})
                    .then(function (response) {
                        _this.block = false;
                        _this.msg = "Please Wait ";
                        _this.preLoader = true;
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

                                if(_status=='connect')
                                    updateIntercomData('properties_connected', {'no_of_properties':successCount});
                                else
                                    updateIntercomData('properties_disconnected', {'no_of_properties':successCount});
                            }
                        } else {
                            _this.toasterView(response.data.message, false);
                        }
                        _this.preLoader = false;
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                    _this.preLoader = false;
                    _this.msg = "Please Wait ";
                    _this.toasterView('Failed to ' + _status + ' Properties', false);
                });
            },

            /**
             * Generate new Api Key to use on PMS
             */
            generateApiKey() {
                let _this = this;
                _this.isCopyToClipBoardReady = false;
                document.querySelector("#generated_key").textContent = 'Please Wait Generating Api Key...';
                axios.post('/client/v2/generate-api-key')
                    .then(function (response) {
                        if (response.data.status) {
                            document.querySelector("#generated_key").textContent = response.data.data;
                            document.querySelector("#generated_key_hidden_input").value = response.data.data;
                            _this.isCopyToClipBoardReady = true;
                        } else {
                            document.querySelector("#generated_key").textContent = 'Fail to Generate Api Key';
                        }
                    }).catch(function (error) {
                });
            },

            /**
             * Copy Generated Api Key to Clip Board
             */
            copyApiKeyToClipBoard() {
                let _this = this;
                let keyToCopy = document.querySelector('#generated_key_hidden_input');
                keyToCopy.setAttribute('type', 'text');
                keyToCopy.select();
                try {
                    var successful = document.execCommand('copy');
                    var msg = successful ? 'successful' : 'unsuccessful';
                    keyToCopy.setAttribute('type', 'hidden');
                    _this.toasterView('Api Key Copied to Clipboard ' + msg, successful);
                } catch (err) {
                    _this.toasterView('Oops, unable to copy', false);
                }
                /* unselect the range */
                keyToCopy.setAttribute('type', 'hidden');
                window.getSelection().removeAllRanges()
            },
        }, //Methods End
        mounted() {
            this.getProperties();
        }
    }
</script>

<style scoped>

</style>