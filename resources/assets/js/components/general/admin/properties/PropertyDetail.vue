<template>
    <div>
        <!-- Property Detail-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">Property Detail</h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Property Detail</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- END: Subheader -->
        <div class="m-content">
            <div class="m-portlet m-portlet--mobile">

                <!-- Property Detail -->
                <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row">
                                <div class="col-md-1"><strong>More </strong></div>
                                <div class="col-md-2"><strong>Property Info ID</strong></div>
                                <div class="col-md-1"><strong>PMS ID</strong></div>
                                <div class="col-md-2"><strong>User Account ID</strong></div>
                                <div class="col-md-2"><strong>Name</strong></div>
                                <div class="col-md-2"><strong>Payment setting</strong></div>
                                <div class="col-md-2"><strong>Connected</strong></div>
                            </div>
                            <div class="panel-group" v-if="Object.keys(property_info).length > 0">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="row"
                                             style="border-top: 1px solid #ebedf2; margin-top: 2rem; padding-top: 2rem;">
                                            <div class="col-md-1">
                                                <a :aria-controls="'id_'+property_info.id"
                                                   :href="'#id_'+property_info.id"
                                                   :id="property_info.id"
                                                   aria-expanded="false"
                                                   class="card-collapse collapsed"
                                                   data-open="true"
                                                   data-toggle="collapse"
                                                   role="button">
                                                    <i :id="property_info.id" class="fas fa-chevron-down"
                                                       data-open="true"
                                                       style="display: block; height: 100%; width: 100%;"></i>
                                                </a>
                                            </div>
                                            <div class="col-md-2">{{property_info.id}}</div>
                                            <div class="col-md-1">{{property_info.pms_property_id}}</div>
                                            <div class="col-md-2">{{property_info.user_account_id}}</div>
                                            <div class="col-md-2">
                                                <div class="m-card-user m-card-user--sm" title="Click for Detail">
                                                    <div class="m-card-user__pic">
                                                        <div class="m-card-user__no-photo m--bg-fill-warning">
                                                                <span>
                                                                    <img :src="['/storage/uploads/property_logos/' +  property_info.logo ]" @error="imageUrlAlt" class="img-responsive">
                                                                </span>
                                                        </div>
                                                    </div>
                                                    <div class="m-card-user__details">
                                                            <span class="m-card-user__name" title="Click for Detail">
                                                                {{ property_info.name }}
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">{{property_info.use_pg_settings | propertyPaymentSetting}}</div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="property_info.status == 1">
                                                    <i class="fas fa-check-circle"></i>
                                                    Connected
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-else>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Disconnected
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div :id="'id_'+property_info.id" class="panel-collapse collapse">
                                        <div class="panel-body open-div">
                                            <div class="row open-div-row">
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">User ID</strong>
                                                    <br/>{{property_info.user_id}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">PMS ID</strong>
                                                    <br/>{{property_info.pms_id}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">PMS Name</strong>
                                                    <br/>{{property_info.pms_form.name}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Property Key</strong>
                                                    <br/>{{property_info.property_key}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Currency Code</strong>
                                                    <br/>{{property_info.currency_code}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Time Zone</strong>
                                                    <br/>{{property_info.time_zone}}
                                                </div>
                                            </div>
                                            <div class="row open-div-row">
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Address</strong>
                                                    <br/>{{ property_info.address }}
                                                    <br/><strong>City:</strong> {{ property_info.city }}
                                                    <br/><strong>Country:</strong> {{ property_info.country }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">User Payment Gateway Name</strong>
                                                    <br/>{{ property_info.user_payment_gateway_name }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Booking Source Setting</strong>
                                                    <br/>
                                                    <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="property_info.use_bs_settings == 1">
                                                        <i class="fas fa-check-circle"></i>
                                                        On
                                                    </span>
                                                    <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-else>
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Off
                                                    </span>
                                                </div>
                                                <div class="col-md-2 col-overflow">
                                                    <strong class="m--margin-bottom-20">Property Email</strong>
                                                    <br/>{{ property_info.property_email }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Last Sync</strong>
                                                    <br/>{{ property_info.last_sync |formatDateTime }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Available On PMS</strong>
                                                    <br/>
                                                    <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="property_info.available_on_pms == 1">
                                                        <i class="fas fa-check-circle"></i>
                                                        Available or Valid<br /> Property
                                                    </span>
                                                    <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-else>
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Not valid or deleted<br /> on PMS
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row open-div-row">
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Created Date</strong>
                                                    <br/>{{ property_info.created_at | formatDateTime }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Updated Date</strong>
                                                    <br/>{{ property_info.updated_at | formatDateTime }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
                </div>
                <!-- END: Property Detail -->

                <!-- Property Audit Detail -->
                <div class="m-portlet__head" style="border-bottom: none;">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h4>Property Audit Detail</h4>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped- table-bordered table-hover table-checkable">
                                        <thead>
                                            <tr>
                                                <th>Sr #</th>
                                                <th>Event</th>
                                                <th>Auditable Type</th>
                                                <th>Auditable ID</th>
                                                <th>Old Values</th>
                                                <th>New Values</th>
                                                <th>Created Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(audit, index) in property_info.property_info_audits">
                                                <td>{{index + 1}}</td>
                                                <td>{{audit.event}}</td>
                                                <td>{{audit.auditable_type}}</td>
                                                <td>{{audit.auditable_id}}</td>
                                                <td>{{audit.old_values}}</td>
                                                <td>{{audit.new_values}}</td>
                                                <td>{{audit.created_at}}</td>
                                            </tr>
                                            <tr v-if="Object.keys(property_info).length > 0 && property_info.property_info_audits.length === 0">
                                                <td class="text-center" colspan="20">
                                                    Record not found
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
                </div>
                <!-- END: Property Audit Detail -->

            </div>
        </div>
        <!-- End Booking List-->

        <!-- Block UI Loader-->
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
        <!-- Block UI Loader-->

    </div>
</template>

<script scoped>
    import {mapState} from 'vuex';

    export default {
        props: ['property_info_id'],

        data() {
            return {
                msg: 'Please Wait...',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                property_info: {},
            }
        },
        methods: {
            getPropertyDetail() {
                this.block = true;
                var self = this;
                self.msg = "Loading Property Detail";
                axios({
                    url: '/admin/property_detail/' + self.property_info_id,
                    method: 'GET'
                }).then((resp) => {
                    console.error();
                    self.property_info = resp.data.data;
                    this.block = false;
                }).catch((err) => {
                    console.log(err);
                    this.block = false;
                });
            },

            imageUrlAlt(event) {
                event.target.src = "/storage/uploads/property_logos/no_image.png";
            }
        },
        filters: {
            propertyPaymentSetting: function (value) {
                if (value == 0) {
                    return 'Global Settings Active';
                } else {
                    return 'Custome Settings Active';
                }
            },
            formatDateTime(value) {
                var months = {
                    'jan': '01', 'feb': '02', 'mar': '03', 'apr': '04',
                    'may': '05', 'jun': '06', 'jul': '07', 'aug': '08',
                    'sep': '09', 'oct': '10', 'nov': '11', 'dec': '12'
                };
                var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                let date = new Date(value);
                let day = date.getDate();
                let monthIndex = date.getMonth();
                let year = date.getFullYear();
                let hours = date.getHours();
                let minutes = date.getMinutes();
                let seconds = date.getSeconds();

                return monthNames[monthIndex] + ' ' + day + ', ' + year + ', ' + hours + ':' + minutes + ':' + seconds;
            }

        },
        mounted() {
            this.getPropertyDetail()
        },

    }

</script>

<style scoped>
    /*!*Div open*!*/
    .open-div {
        background-color: #e6e6e6 !important;
    }

    .open-div-row {
        padding: 2rem !important;
        margin-top: 2rem !important;
    }

    .col-overflow {
        overflow: auto !important;
    }

</style>
