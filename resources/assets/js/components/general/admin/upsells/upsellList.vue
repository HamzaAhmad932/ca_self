<template>
    <div>

        <!-- User List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-users"></i> Upsells
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Upsell List </span>
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
                                <div class="form-group col-md-1">
                                    <label>&nbsp;</label>
                                    <select v-model="filters.recordsPerPage" @change="getAllUpsellList" class="custom-select custom-select-sm mb-2 mr-1" id="inlineFormInputName3" ref="recordsPerPage">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-search">Search</label>
                                    <input class="form-control form-control-sm" id="filter-search" type="text" placeholder="Start typing …" @keyup="getAllUpsellList()" v-model="filters.search.searchStr">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-Types">Upsell Types</label>
                                    <select class="custom-select custom-select-sm" id="filter-Types" v-model="filters.upsell_type" @change.prevent="getAllUpsellList">
                                        <option value="all">All</option>
                                        <option :value="upsell_type.id"  v-for="upsell_type in upsell_types"> {{ upsell_type.title }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-user-accounts">User Account</label>
                                    <select class="custom-select custom-select-sm" id="filter-user-accounts" v-model="filters.user_account_id" @change.prevent="getAllUpsellType">
                                        <option value="all">All User Accounts</option>
                                        <option :value="user_account.id" v-for="user_account in user_accounts" v-if="user_accounts != null && user_accounts.length > 0"> {{ user_account.id +' -- '+user_account.name }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <a @click.prevent="resetFilters()"
                                       class="btn btn-sm btn-block btn-danger float-right" href="#" id="reset-btn">Reset</a>
                                </div>
                            </div>
                            <div class="row heading-row">
                                <div class="col-md-1"><strong>More</strong></div>
                                <div class="col-md-2"><strong>ID</strong></div>
                                <div class="col-md-3">
                                    <a :class="selectedSortOrder" @click.prevent="selectedSortOrderChanged()" href="#0">
                                        <strong><span>Internal Name</span></strong>
                                    </a>
                                </div>
                                <div class="col-md-2"><strong>Period</strong></div>
                                <div class="col-md-2"><strong>Type</strong></div>
                                <div class="col-md-2"><strong>Connection Status</strong></div>
                            </div>

                            <div class="panel-group">
                                <div class="panel panel-default" v-for="(upsell, index) in paginationResponse.data">
                                    <div class="panel-heading">
                                        <div class="row" :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <div class="col-md-1">
                                                <a :aria-controls="'id_'+upsell.id"
                                                   :href="'#id_'+upsell.id"
                                                   :id="upsell.id"
                                                   aria-expanded="false"
                                                   class="card-collapse collapsed"
                                                   data-open="true"
                                                   data-toggle="collapse"
                                                   role="button">
                                                    <i :id="upsell.id" class="fas fa-chevron-down" data-open="true"
                                                       style="display: block; height: 100%; width: 100%;"></i>
                                                </a>
                                            </div>
                                            <div class="col-md-2">{{upsell.id}}</div>
                                            <div class="col-md-3">
                                                <div class="single-line">
                                                    <span class="property-name-wrapper-at-propertieslist"> {{ upsell.internal_name.substring(0, 30) }}{{upsell.internal_name.length > 30 ? '...':''}}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="single-line text-muted"> {{upsell.per.label}} - {{upsell.period.label}}</div>
                                            </div>
                                            <div class="col-md-2">{{upsell.type}}</div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="upsell.status.value == 1">
                                                    <i class="fas fa-check-circle"></i>
                                                    Active
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-else>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Deactive
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div :id="'id_'+upsell.id" class="panel-collapse collapse">
                                        <div class="panel-body open-div">
                                            <div class="row open-div-row">
                                                <div class="col-md-12">
                                                    <strong>Attached Rentals</strong>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Property ID</th>
                                                                    <th>Property Name</th>
                                                                    <th>Rentals</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr v-for="(property, index) in upsell.attached_rentals">
                                                                    <!--                                                            <td>{{index+1}}</td>-->
                                                                    <td>#{{property.pms_property_id}}</td>
                                                                    <td>{{property.property_name}}</td>
                                                                    <td>
                                                                        <div class="table-responsive">
                                                                            <table class="table">
                                                                                <tr v-for="(room, index2) in property.rooms">
                                                                                    <td>{{index2 > 0 ? '#'+index2 +' -- ': ''}} {{room}}</td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>

                                                                    </td>
                                                                </tr>
                                                                <tr v-if="upsell.attached_rentals_count == 0"><td>No rental attached</td></tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--<div class="panel panel-default" v-if="paginationResponse.meta.total == 0">
                                    <div class="panel-heading text-center">
                                        <div class="panel-title">No Record found.</div>
                                    </div>
                                </div>-->
                            </div>

                        </div>
                    </div>
                    <!--end: table -->
                    <pagination :data="paginationResponse" :limit="1" @pagination-change-page="getAllUpsellList" align="right"></pagination>
                </div>
            </div>
        </div>
        <!-- End Booking List-->

        <!-- Block UI Loader-->
        <BlockUI :message="loader.msg" v-if="loader.block === true" :html="loader.html"></BlockUI>
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
                selectedSortOrder : 'selected sort-asc',
                serve_id:0,
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
                },
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
            this.$store.dispatch('getAllUpsellTypes',{
                for_filters:false,
                serve_id:0
            });
            // this.(this.$store.commit,); // Fetch getUpsellTypes
            this.get_user_account();
            this.getAllUpsellList(); // Fetch getUpsellList
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

            getAllUpsellType() {
                this.$store.dispatch('getAllUpsellTypes',{
                    for_filters:false,
                    serve_id:0
                });
                this.getAllUpsellList();
            },

            /**
             * Change Sort Order By Name Column ASc or DESc For Desktop
             */
            selectedSortOrderChanged(){
                this.filters.sortColumn = 'internal_name';
                if (this.selectedSortOrder ===  'selected sort-desc'){
                    this.selectedSortOrder = 'selected sort-asc';
                    this.filters.sort.sortOrder = 'Asc';
                } else {
                    this.selectedSortOrder = 'selected sort-desc'
                    this.filters.sort.sortOrder = 'Desc';
                }
                this.getAllUpsellList();
            },

            /**
             * Getting Properties List Using Pagination
             */
            getAllUpsellList(page = 1) {
                let count = 0;
                if(this.filters.upsell_type!='all')
                    count++;
                if(this.filters.search.searchStr != '')
                    count++;
                if(this.filters.recordsPerPage!='10')
                    count++;
                this.filters.filter_count = count;
                this.filters.page = page;
                this.$store.dispatch('getAllList');
            },

            sortOptionsChanged(){
                this.filters.sort.sortOrder = 'asc';
                this.getAllUpsellList();
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
                this.getAllUpsellList();
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
