<template>
    <div>

        <!-- User List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-users"></i> Upsell Orders
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Upsell Order List </span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
        <!-- END: Subheader -->
        <div class="m-content">
            <div class="m-portlet m-portlet--mobile">

                <div class="m-portlet__body" id="b-list">
                    <!--begin: table -->
                    <div class="m-section">

                        <div class="m-section__content">
                            <div class="row filter-fow">
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <select v-model="filters.recordsPerPage" @change="getUpsellOrderList" class="custom-select custom-select-sm mb-2 mr-1" id="inlineFormInputName3" ref="recordsPerPage">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 offset-2">
                                    <label for="filter-search">Search</label>
                                    <input class="form-control form-control-sm" id="filter-search" type="text" placeholder="Start typing …" @keyup="getUpsellOrderList()" v-model="filters.search.searchStr">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-user-accounts">User Account</label>
                                    <select class="custom-select custom-select-sm" id="filter-user-accounts" v-model="filters.user_account_id" @change.prevent="getUpsellOrderList">
                                        <option value="all">All User Accounts</option>
                                        <option :value="user_account.id" v-for="user_account in user_accounts" v-if="user_accounts != null && user_accounts.length > 0"> {{ user_account.id +' -- '+user_account.name }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <a class="btn btn-sm btn-block btn-danger float-right" id="reset-btn" href="#" @click.prevent="resetFilters()">Reset</a>
                                </div>
                            </div>
                            <div class="row heading-row">
                                <div class="col-md-1">
                                    <a :class="selectedSortOrder" href="#0" @click.prevent="selectedSortOrderChanged()"> <span>ID</span></a>
                                </div>
                                <div class="col-md-2"><strong>Due Date</strong></div>
                                <div class="col-md-2"><strong>Booking#</strong></div>
                                <div class="col-md-2"><strong>Charge Reference</strong></div>
                                <div class="col-md-2"><strong>Commission Paid</strong></div>
                                <div class="col-md-2"><strong>Final Amount</strong></div>
                                <div class="col-md-1"><strong>Status</strong></div>
                            </div>

                            <div class="panel-group">
                                <div class="panel panel-default" v-for="(upsell_order, index) in paginationResponse.data">
                                    <div class="panel-heading">
                                        <div class="row" :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <div class="col-md-1">{{ upsell_order.id }}</div>
                                            <div class="col-md-2">{{upsell_order.due_date}}</div>
                                            <div class="col-md-2">{{upsell_order.pms_booking_id}}</div>
                                            <div class="col-md-2">
                                                <small>{{upsell_order.charge_ref_no != null ? upsell_order.charge_ref_no : ' -- '}} </small>
                                            </div>
                                            <div class="col-md-2">{{upsell_order.currency_symbol}}{{upsell_order.commission_fee}}</div>
                                            <div class="col-md-2">{{upsell_order.currency_symbol}}{{upsell_order.final_amount}}</div>
                                            <div class="col-md-1" style="padding: 0 10px;">
                                                <span v-if="upsell_order.payment_status.value" data-toggle="tooltip" data-placement="top" title="Amount Paid" class="badge badge-success status-badge-align ml-2">
                                                <i class="fas fa-check-circle"></i>
                                                    {{upsell_order.payment_status.label}}
                                                </span>
                                                    <span v-else data-toggle="tooltip" data-placement="top" title="Amount not Paid" class="badge badge-warning status-badge-align ml-2">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    {{upsell_order.payment_status.label}}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--NO RECORD Begin-->
                                <!--<div class="property-card property-connected" v-if="paginationResponse.meta.total == 0">
                                    <div class="card-pane"><div class="row no-gutters"><div class="col-12">No Record found.</div></div></div>
                                </div>-->
                                <!--NO RECORD END-->
                            </div>

                        </div>
                    </div>
                    <!--end: table -->
                    <pagination :data="paginationResponse" :limit="1" @pagination-change-page="getUpsellOrderList" align="right"></pagination>
                </div>
            </div>
        </div>
        <!-- End Booking List-->

        <!-- Block UI Loader-->
        <BlockUI :message="loader.msg" v-if="loader.block === true"  :html="loader.html"></BlockUI>
        <!-- Block UI Loader-->

    </div>
</template>

<script scoped>
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';
    import {mapActions, mapState} from "vuex";
    Vue.use(VueToast);
    export default {
        data() {
            return {
                selectedSortOrder : 'selected sort-desc',
                user_accounts: [],
            }
        },
        computed : {
            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                upsell_types : (state)=>{
                    return state.adminUpsells.upsell_types;
                }, //not used yet...
                filters : (state)=>{
                    return state.adminUpsells.filters;
                },
                paginationResponse : (state)=>{
                    return state.adminUpsells.paginationResponse;
                },
            }),
            // ...mapActions([
            //     'getUpsellTypes',
            //     'getList',
            // ])
        },
        mounted() {
            this.$store.commit('SHOW_LOADER', null, {root : true});
            this.filters.search.searchInColumn = ['id','final_amount', 'commission_fee', 'charge_ref_no', 'created_at'];
            this.get_user_account();
            this.getUpsellOrderList(); // Fetch getUpsellOrderList
        },
        methods: {

            /**
             * Load All User Accounts
             */
            get_user_account() {
                var self = this;
                axios.get('/admin/get-all-user-accounts')
                    .then(function (response) {
                        if(response.status == 200){
                            self.user_accounts = response.data.data;
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        console.log(errors);
                    });
            },

            /**
             * Change Sort Order By Name Column ASc or DESc For Desktop
             */
            selectedSortOrderChanged(){
                this.filters.sortColumn = 'id';
                if (this.selectedSortOrder ===  'selected sort-desc'){
                    this.selectedSortOrder = 'selected sort-asc';
                    this.filters.sort.sortOrder = 'Asc';
                } else {
                    this.selectedSortOrder = 'selected sort-desc'
                    this.filters.sort.sortOrder = 'Desc';
                }
                this.getUpsellOrderList();
            },

            /**
             * Getting Properties List Using Pagination
             */
            getUpsellOrderList(page = 1) {
                let count = 0;
                if(this.filters.upsell_type!='all')
                    count++;
                if(this.filters.search.searchStr != '')
                    count++;
                if(this.filters.recordsPerPage!='10')
                    count++;
                this.filters.filter_count = count;
                this.filters.page = page;
                this.$store.dispatch('getAllList', 'upsell-orders');
            },

            sortOptionsChanged(){
                this.filters.sort.sortOrder = 'asc';
                this.getUpsellOrderList();
            },
            resetFilters(){
                this.filters.recordsPerPage = 10;
                this.filters.page = 1;
                this.filters.search.searchStr = '';
                this.filters.upsell_type = 'all';
                this.filters.sort.sortOrder = 'Desc';
                this.filters.sort.sortColumn ='id';
                this.filters.filter_count = 0;
                this.filters.user_account_id = 'all';
                this.getUpsellOrderList();
            },

        },
    }
</script>

<style scoped>
    .m-nav .m-nav__item > .m-nav__link .m-nav__link-icon {
        width: auto;
    }

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

    .heading-row a span {
        display: inline-block;
        position: relative;
        color: #575962;
    }
    .heading-row a span:after {
        display: none;
        content: '';
        width: 0px;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 10px solid #334E68;
        position: absolute;
        left: 40%;
        margin-left: -2px;
        margin-top: 10px;
    }
    .heading-row .selected {
        color: #102A43;
    }
    .heading-row .selected.sort-desc span:after {
        display: block;
    }
    .heading-row .selected.sort-asc span:after {
        display: block;
        transform: rotate(180deg);
    }
    .heading-row .selected .fas {
        margin-left: 0.5rem;
    }

    /*  Property detail  */
    .open-div {
        background-color: #e6e6e6 !important;
    }

    .open-div-row {
        padding: 2rem !important;
        margin-top: 2rem !important;
    }
</style>
