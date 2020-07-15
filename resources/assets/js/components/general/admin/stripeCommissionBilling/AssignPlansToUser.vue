<template>
    <div class="m-grid__item m-grid__item--fluid m-wrapper">
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-analytics"></i> Stripe Billing
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Assign Plans</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="m-content">
            <div class="m-portlet m-portlet--mobile">
                <!--<div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text">
                                Accounts List<small>(Subscribe User For Billing)</small>
                            </h3>
                        </div>
                    </div>
                </div>-->
                <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row filter-fow">
                                <div class="form-group col-md-4">
                                    <label for="account_status">Account Status: </label>
                                    <select @change="getUserAccounts()" class="form-control"
                                            v-model="account_status" id="account_status">
                                        <option value="all">All</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="integration_status">Integration Status: </label>
                                    <select @change="getUserAccounts()" class="form-control"
                                            v-model="integration_status" id="integration_status">
                                        <option value="all">All</option>
                                        <option value="completed">Completed</option>
                                        <option value="incomplete">Incomplete</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>&nbsp;</label>
                                    <div class="m-input-icon m-input-icon--left">
                                        <input @keyup="getUserAccounts()" class="form-control m-input m-input--solid"
                                               id="generalSearch" placeholder="Search..."
                                               type="text" v-model="filters.search.searchStr">
                                        <span class="m-input-icon__icon m-input-icon__icon--left">
                                            <span><i class="la la-search"></i></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row heading-row">
                                <div class="col-md-1"><strong>More</strong></div>
                                <div class="col-md-1">
                                    <a @click="sort('id_sort', 'id')" href="#0" style="color: #00d3eb">
                                        <i :class="id_sort === 'desc' ? 'fa fa-fw fa- fa-sort fa-sort-down' : id_sort === 'asc' ? 'fa fa-fw fa-sort fa-sort-up' : 'fa fa-fw fa-sort'"></i>
                                    </a>
                                    <strong>ID</strong>
                                </div>
                                <div class="col-md-3">
                                    <a @click="sort('name_sort', 'name')" href="#0" style="color: #00d3eb">
                                        <i :class="name_sort === 'desc' ? 'fa fa-fw fa-sort fa-sort-down' : name_sort === 'asc' ? 'fa fa-fw fa-sort fa-sort-up' : 'fa fa-fw fa-sort'"></i>
                                    </a>
                                    <strong>Company</strong>
                                </div>
                                <div class="col-md-2"><strong>Integration Status</strong></div>
                                <div class="col-md-2"><strong>Account Status</strong></div>
                                <div class="col-md-2">
                                    <a @click="sort('plan_sort', 'plan_attached_status')" href="#0"
                                       style="color: #00d3eb">
                                        <i :class="plan_sort === 'desc' ? 'fa fa-fw fa-sort fa-sort-down' : plan_sort === 'asc' ? 'fa fa-fw fa-sort fa-sort-up' : 'fa fa-fw fa-sort'"></i>
                                    </a>
                                    <strong>Plan Attached</strong>
                                </div>
                                <div class="col-md-1"><strong>Action</strong></div>
                            </div>
                            <div class="panel-group">
                                <div class="panel panel-default" v-for="(user_account, index) in userAccounts.data">
                                    <div class="panel-heading">
                                        <div class="row" :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <div class="col-md-1">
                                                <a :aria-controls="'id_'+user_account.id"
                                                   :href="'#id_'+user_account.id"
                                                   :id="user_account.id"
                                                   aria-expanded="false"
                                                   class="card-collapse collapsed"
                                                   data-open="true"
                                                   data-toggle="collapse"
                                                   role="button">
                                                    <i :id="user_account.id" class="fas fa-chevron-down" data-open="true"
                                                       style="display: block; height: 100%; width: 100%;"></i>
                                                </a>
                                            </div>
                                            <div class="col-md-1">{{user_account.id}}</div>
                                            <div class="col-md-3">
                                                <div class="m-card-user m-card-user--sm" title="Click for Detail">
                                                    <div class="m-card-user__pic">
                                                        <div class="m-card-user__no-photo m--bg-fill-warning">
                                                            <span>
                                                                <img :src="['/storage/uploads/companylogos/' +  user_account.company_logo ]" @error="imageUrlAlt" class="img-responsive">
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="m-card-user__details">
                                                        <span class="m-card-user__name" title="Click for Detail">
                                                            {{ user_account.name }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="user_account.integration_completed_on">
                                                    <i class="fas fa-check-circle"></i>
                                                    Completed
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-else>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Not Completed
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="user_account.status == 1">
                                                    <i class="fas fa-check-circle"></i>
                                                    Connected
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-if="user_account.status == 2">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Disconnected
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-if="user_account.status == 3">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Suspended
                                                </span>
                                                <span class="badge badge-warning status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-if="user_account.status == 4">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Pending
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="user_account.plan_attached_status == 1">
                                                    <i class="fas fa-check-circle"></i>
                                                    Attached
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-if="user_account.plan_attached_status == 0">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Not attached
                                                </span>
                                            </div>
                                            <div class="col-md-1">
                                                <span class="dropdown">
                                                    <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" :class="user_account.integration_completed_on == null ? 'disabled' : ''" data-toggle="dropdown" aria-expanded="true"><i class="la la-ellipsis-h"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="javascript:void(0)" @click="createCustomerWithOutCard(user_account.id)" v-if="user_account.stripe_customer_id == null"><i class="m-menu__link-icon flaticon-avatar"></i> Create Stripe Customer </a>
<!--                                                        <a class="dropdown-item" :href="'/admin/create-customer/' + user_account.id + '/' + user_account.name"><i class="la la-user"></i> Billing Details Setup </a>-->
                                                        <a class="dropdown-item" href="javascript:void(0)" @click="listSubscriptions(user_account.id, user_account.name)" data-target="#m_modal_6" data-toggle="modal"><i class="m-menu__link-icon la flaticon-settings-1"></i> Subscription Setup </a>
                                                    </div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div :id="'id_'+user_account.id" class="panel-collapse collapse">
                                        <div class="panel-body open-div">
                                            <div class="row open-div-row">
                                                <div class="col-md-4">
                                                    <strong class="m--margin-bottom-20">Email</strong><br/>
                                                    {{user_account.email != null ? user_account.email : '--'}}
                                                </div>
                                                <div class="col-md-4">
                                                    <strong class="m--margin-bottom-20">Contact Number</strong><br/>
                                                    {{user_account.contact_number != null ? user_account.contact_number : '--'}}
                                                </div>
                                                <div class="col-md-4">
                                                    <strong class="m--margin-bottom-20">Stripe Customer ID</strong><br/>
                                                    <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-if="user_account.stripe_customer_id == null">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Customer ID not created
                                                    </span>
                                                    {{user_account.stripe_customer_id}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default" v-if="userAccounts.total == 0">
                                    <div class="panel-heading">
                                        <div class="panel-title">No user account found.</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--end: table -->
                    <pagination :data="userAccounts" @pagination-change-page="getUserAccounts" align="right"></pagination>
                </div>
            </div>

            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="m_modal_6" role="dialog"
                 tabindex="-1">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"> Subscription List</h5>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="col-12">
                                <button @click="getAllBillingPlans()" class="btn btn-sm btn-success pull-right"
                                        data-target="#m_modal_9" data-toggle="modal">Add Subscription +
                                </button>
                                <br><br>
                            </div>
                            <div class="m_datatable m-datatable m-datatable--default m-datatable--loaded">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-header">
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Subscription ID</th>
                                        <th>Period Start</th>
                                        <th>Period END</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(subscription,key) in subscriptions">
                                        <td> {{key+1}}</td>
                                        <td> {{subscription.id}} </td>
                                        <td> {{ subscription.current_period_start | moment("dddd, MMMM Do YYYY") }}</td>
                                        <td> {{ subscription.current_period_end | moment("dddd, MMMM Do YYYY") }}</td>
                                        <td> {{subscription.status}}</td>
                                        <td class="m-datatable__cell" data-field="Actions">
                                   <span style="overflow: visible; position: relative; width: 110px;">
                                      <div class="dropdown">
                                         <a aria-expanded="false"
                                            class="btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill"
                                            data-toggle="dropdown" href="#"><i
                                                 class="la la-ellipsis-h"></i></a>
                                         <div class="dropdown-menu dropdown-menu-right" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-186px, 33px, 0px);"
                                              x-placement="bottom-end">
                                             <a @click="getSubscriptionDetails(subscription.id)" class="dropdown-item" data-dismiss="modal"
                                                data-target="#m_modal_7" data-toggle="modal"
                                                href="#"><i
                                                     class="la la-edit"></i> Edit Subscriptions</a>
                                             <a @click="cancelSubscription(subscription.id)" class="dropdown-item"
                                                href="#"><i class="la la-trash"></i> Cancel Subscriptions</a>
                                         </div>
                                      </div>
                                   </span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <h4 style="text-align: center;" v-if="subscriptions.length == 0">
                                    <b>No Subscription Found!</b>
                                </h4>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal" type="button">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Details Begin-->
            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="m_modal_7" role="dialog"
                 tabindex="-1">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Subscription </h5>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="col-12">
                                <button @click="getAllBillingPlans()" class="btn btn-sm btn-success pull-right"
                                        data-target="#m_modal_8" data-toggle="modal">Add Plan +
                                </button>
                                <br><br>
                            </div>

                            <table class="table table-bordered">
                                <tr>
                                    <th>Sr#</th>
                                    <th>Pricing Plan</th>
                                    <th>QTY</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                                <tr v-for="(subscriptionDetail, key) in subscriptionDetails">
                                    <td>{{key+1}}</td>
                                    <td>{{subscriptionDetail.plan.nickname}}</td>
                                    <td><input min="1" style="width: 50px" type="number"
                                               v-if="subscriptionDetail.plan.usage_type == 'licensed'"
                                               v-model="subscriptionDetail.quantity">
                                        <span v-if="subscriptionDetail.plan.usage_type == 'metered'">Varies with usage</span>
                                    </td>
                                    <td><span v-if="subscriptionDetail.plan.usage_type == 'licensed'">
                                    {{Number((subscriptionDetail.plan.amount > 0 ? (subscriptionDetail.plan.amount * subscriptionDetail.quantity) : (subscriptionDetail.plan.amount_decimal * subscriptionDetail.quantity))).toFixed(2)}}</span>
                                        <span v-if="subscriptionDetail.plan.usage_type == 'metered'">Varies with usage</span>
                                    </td>
                                    <td><span style="overflow: visible; position: relative; width: 110px;">
                                  <div class="dropdown">
                                     <a aria-expanded="false"
                                        class="btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill"
                                        data-toggle="dropdown" href="#"><i
                                             class="la la-ellipsis-h"></i></a>
                                     <div class="dropdown-menu dropdown-menu-right" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-186px, 33px, 0px);"
                                          x-placement="bottom-end">
                                         <a @click="deAttachPlan(subscriptionDetail.plan.id, subscriptionDetail.subscription, subscriptionDetail.id)" class="dropdown-item" data-toggle="modal" href="#"
                                            title="De-attach Plan"><i
                                                 class="la la-leaf"></i> Remove Plan</a>
                                     </div>
                                  </div></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal" type="button">Close</button>
                            <button @click="saveSubscription()" class="btn btn-accent"><i class="la la-refresh"></i>
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Subscription Details End -->

            <!-- ADD Plan To Subscription Begin-->
            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="m_modal_8" role="dialog"
                 tabindex="-1">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Attach New Plan </h5>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12" v-for="plan in plans" v-if="plan.alreadySubscribed == false">
                                    <div class="m-radio-inline">
                                        <label class="m-checkbox m-checkbox--solid m-checkbox--success">
                                            <input type="checkbox" v-model="plan.checked">
                                            <b>{{plan.nickname}}</b> <br><small>({{plan.usage_type}} -
                                            {{plan.id}})</small>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal" ref="m_modal_8_dismiss"
                                    type="button">Close
                            </button>
                            <button @click="addPlanToSubscription()" class="btn btn-accent"><i class="la la-adn"></i>
                                ADD
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ADD Plan To Subscription ENd-->

            <!-- ADD New Subscription Begin-->
            <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="m_modal_9" role="dialog"
                 tabindex="-1">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">New Subscription </h5>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="row">
                                <div class="col-12">
                                        <label class="label" for="trial_days_to_avail"><strong>Trial Days </strong>
                                            <small> (Trial days from previous subscription or Default 14)</small>
                                        </label>
                                </div>
                                <div class="col-12" style="margin-bottom:20px">
                                    <input type="text" class="form-control-sm"  v-model="trial_days_to_avail" id="trial_days_to_avail">
                                </div>
                            </div>

                            <div class="row">
                            <div class="col-12" v-for="plan in plans">
                                    <div class="m-radio-inline">
                                        <label class="m-checkbox m-checkbox--solid m-checkbox--success">
                                            <input type="checkbox" v-model="plan.checked">
                                            <b>{{plan.nickname}}</b> <br><small>({{plan.usage_type}} -
                                            {{plan.id}})</small>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal" ref="m_modal_9_dismiss"
                                    type="button">Close
                            </button>
                            <button @click="addSubscription()" class="btn btn-accent"><i class="la la-adn"></i> ADD
                                Subscription
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ADD New Subscription End-->
            <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
        </div>
    </div>
</template>

<script>
    Vue.use(require('vue-moment'));
    export default {
        name: "AssignPlansToUser",
        data() {
            return {
                msg: 'Please Wait',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                plans: {},
                userAccounts: {},
                subscriptions: {},
                trial_days_to_avail:15,
                planToAddId: '',
                selectedUserAccount: {
                    id: 0,
                    name: '',
                    selectedSubscriptionId: '',
                },
                subscriptionDetails: {},
                filters: {
                    recordsPerPage: 10,
                    page: 1,
                    columns: ['*'], //main table columns
                    relations: [],
                    sort: {
                        sortOrder: "Desc",
                        sortColumn: "id",
                    },
                    constraints: [
                        ["account_type", "=", 1],
                        //['integration_completed_on', '!=', null],
                    ],
                    search: {
                        searchInColumn: ["id", "name", 'stripe_customer_id', 'email', "contact_number"],
                        searchStr: ""
                    },
                }, //Datatable filters Object End
                id_sort: 'desc',
                name_sort: 'none',
                plan_sort: 'none',
                customer_created_sort: 'none',
                integration_status: 'all',
                account_status: 'all',
            }
        },
        methods: {
            getUserAccounts(page = 1) {
                this.block = true;
                var _this = this;
                _this.filters.page = page;
                axios.post('/admin/get-all-user-accounts?page=' + page, {
                    'filters': _this.filters, 'account_constraint': _this.account_status,
                    'integration_constraint': _this.integration_status
                }).then(response => {
                    _this.userAccounts = response.data.data;
                    _this.block = false;
                });
            },

            /**
             *
             * @param $event
             */
            searchUserAccounts($event) {
                if ($event.keyCode === 13) {
                    this.getUpsellList();
                }
            },
            getAllBillingPlans() {
                let _this = this;
                _this.plans = {};
                _this.block = true;
                axios.post('/admin/get-all-billing-plans-with-user-subscribed-plans/', {
                    'userAccountId': _this.selectedUserAccount.id,
                    'subscriptionId': _this.selectedUserAccount.selectedSubscriptionId
                })
                    .then(function (response) {
                        if (response.data.status) {
                            _this.plans = response.data.data;
                        } else {
                            toastr.error(response.data.message);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    _this.block = false;
                    console.log(error);
                });
            },
            getSubscriptionDetails(subscriptionId) {
                let _this = this;
                _this.block = true;
                _this.selectedUserAccount.selectedSubscriptionId = subscriptionId;
                _this.subscriptionDetails = {};
                axios.post('/admin/get-subscription-details/', {
                    'userAccountId': _this.selectedUserAccount.id,
                    'subscriptionId': subscriptionId
                })
                    .then(function (response) {
                        if (response.data.status) {
                            _this.subscriptionDetails = response.data.data;
                        } else {
                            toastr.error(response.data.message);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                });
            },
            listSubscriptions(userAccountId, name) {
                let _this = this;
                _this.subscriptions = {};
                _this.trial_days_to_avail = 15;
                _this.selectedUserAccount.id = userAccountId;
                _this.selectedUserAccount.selectedSubscriptionId = '';
                _this.selectedUserAccount.name = name;
                _this.block = true;
                axios.post('/admin/list-user-subscriptions/', {'userAccountId': _this.selectedUserAccount.id})
                    .then(function (response) {
                        if (response.data.status) {
                            console.log(response.data.data);
                            console.log(response.data.data.subscriptions);
                            _this.subscriptions = response.data.data.subscriptions;
                            _this.trial_days_to_avail = response.data.data.trial_days_to_avail;
                        } else {
                            toastr.error(response.data.message);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                });
            },
            deAttachPlan(planID, subscriptionId, subscriptionItemId) {
                let _this = this;
                swal({
                    title: 'Are You Sure', text: "Do you Really Want to De-Attach this Plan",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, De-Attach!"
                }).then(function (e) {
                    if (e.value == true) {
                        _this.block = true;
                        axios.post('/admin/de-attach-user-subscription-plan/', {
                            'userAccountId': _this.selectedUserAccount.id,
                            'planId': planID,
                            'subscriptionId': subscriptionId,
                            'subscriptionItemId': subscriptionItemId
                        })
                            .then(function (response) {
                                if (response.data.status) {
                                    _this.subscriptionDetails = response.data.data;
                                    toastr.success(response.data.message);
                                    _this.getUserAccounts(_this.userAccounts.current_page);

                                } else {
                                    toastr.error(response.data.message);
                                }
                                _this.block = false;
                            }).catch(function (error) {
                            console.log(error);
                            _this.block = false;
                        });
                    }
                });
            },
            addPlanToSubscription() {
                let _this = this;
                _this.block = true;
                axios.post('/admin/attach-plan-to-subscription/', {
                    'userAccountId': _this.selectedUserAccount.id,
                    'plans': _this.plans,
                    'subscriptionId': _this.selectedUserAccount.selectedSubscriptionId
                })
                    .then(function (response) {
                        if (response.data.status) {
                            _this.subscriptionDetails = response.data.data;
                            _this.$refs.m_modal_8_dismiss.click();
                            toastr.success(response.data.message);
                        } else {
                            toastr.error(response.data.message);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                });
            },
            saveSubscription() {
                let _this = this;
                _this.block = true;
                axios.post('/admin/save-subscription/', {
                    'userAccountId': _this.selectedUserAccount.id,
                    'subscriptionItems': _this.subscriptionDetails,
                    'subscriptionId': _this.selectedUserAccount.selectedSubscriptionId
                })
                    .then(function (response) {
                        if (response.data.status) {
                            _this.subscriptionDetails = response.data.data;
                            toastr.success(response.data.message);
                            _this.getUserAccounts(_this.userAccounts.current_page);

                        } else {
                            toastr.error(response.data.message);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                });
            },
            addSubscription() {
                let _this = this;
                _this.block = true;
                axios.post('/admin/add-subscription/'+this.trial_days_to_avail, {
                    'userAccountId': _this.selectedUserAccount.id,
                    'plans': _this.plans
                })
                    .then(function (response) {
                        if (response.data.status) {
                            _this.subscriptions = response.data.data;
                            _this.$refs.m_modal_9_dismiss.click();
                            toastr.success(response.data.message);
                            _this.getUserAccounts(_this.userAccounts.current_page);

                        } else {
                            toastr.error(response.data.message);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                });
            },
            cancelSubscription(subscriptionId) {
                let _this = this;
                swal({
                    title: 'Are You Sure !', text: "Do You Really Want to Remove Subscription",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, Remove!"
                }).then(function (e) {
                    if (e.value == true) {
                        _this.block = true;
                        axios.post('/admin/cancel-subscription/', {
                            'userAccountId': _this.selectedUserAccount.id,
                            'subscriptionId': subscriptionId,
                        })
                            .then(function (response) {
                                if (response.data.status) {
                                    _this.subscriptions = response.data.data;
                                    toastr.success(response.data.message);
                                    _this.getUserAccounts(_this.userAccounts.current_page);

                                } else {
                                    toastr.error(response.data.message);
                                }
                                _this.block = false;
                            }).catch(function (error) {
                            console.log(error);
                            _this.block = false;
                        });
                    }
                });
            },
            createCustomerWithOutCard(user_account_id) {
                let _this = this;
                _this.block = true;
                axios.post('/admin/create-customer-with-out-card/', {'userAccountId': user_account_id})
                    .then(function (response) {
                        if (response.data.status) {
                            toastr.success(response.data.message);
                            _this.getUserAccounts(_this.userAccounts.current_page);
                        } else {
                            toastr.error(response.data.message);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    console.log(error);
                    _this.block = false;
                });
            },

            sort(sort_option, sort_by_column) {
                let previous_value = this[sort_option];
                this[sort_option] = previous_value !== 'asc' ? 'asc' : 'desc';
                this.filters.sort.sortColumn = sort_by_column;
                this.filters.sort.sortOrder = this[sort_option];
                if (sort_option != 'id_sort')
                    this['id_sort'] = 'none';
                if (sort_option != 'name_sort')
                    this['name_sort'] = 'none';
                if (sort_option != 'plan_sort')
                    this['plan_sort'] = 'none';
                if (sort_option != 'customer_created_sort')
                    this['customer_created_sort'] = 'none';

                this.getUserAccounts();
            },
            imageUrlAlt(event) {
                event.target.src = "/storage/uploads/companylogos/no_image.png";
            }
        },
        watch: {},
        mounted() {
            this.getUserAccounts();
        }
    };
</script>

<style scoped>
    thead > tr > th {
        height: 55px;
        vertical-align: middle;
    }

    /*  User Account More Option  */
    .open-div {
        background-color: #e6e6e6 !important;
    }

    .open-div-row {
        padding: 2rem !important;
        margin-top: 2rem !important;
    }
    /*  Headings and filters  */
    .filter-fow {
        background-color: rgb(188, 204, 220);
        box-sizing: border-box;
        padding: 1rem 0rem 1rem 0rem;
    }

    .heading-row {
        background-color: rgb(240, 244, 248);
        padding: 2rem 0rem 2rem 0rem;
    }

    .first-data-row {
        border-top: 1px solid #ebedf2;
        padding-top: 2rem;
    }

    .data-row {
        border-top: 1px solid #ebedf2;
        margin-top: 2rem;
        padding-top: 2rem;
    }
</style>