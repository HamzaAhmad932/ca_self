<template>
    <div>

        <!-- User List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-users"></i> Upsell Types
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Upsell Type List </span>
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
                                    <select v-model="filters.recordsPerPage" @change="getUpsellTypeLists"
                                            class="custom-select custom-select-sm mb-2 mr-1" id="inlineFormInputName3"
                                            ref="recordsPerPage">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-search">Search</label>
                                    <input class="form-control form-control-sm" id="filter-search" type="text"
                                           placeholder="Start typing …"
                                           @keyup.prevent="searchProperties($event)"
                                           v-model="filters.search.searchStr">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <a class="btn btn-sm btn-block btn-dark"
                                       id="search-btn" href="#" @click.prevent="searchProperties(null,true)"><i class="fas fa-search"></i></a>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-user-accounts">User Account</label>
                                    <select class="custom-select custom-select-sm" id="filter-user-accounts" v-model="filters.user_account_id" @change.prevent="getUpsellTypeLists">
                                        <option value="all">All User Accounts</option>
                                        <option :value="user_account.id" v-for="user_account in user_accounts" v-if="user_accounts != null && user_accounts.length > 0"> {{ user_account.id +' -- '+user_account.name }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <a class="btn btn-sm btn-block btn-danger float-right"
                                       id="reset-btn" href="#" @click.prevent="resetFilters()">Reset</a>
                                </div>
                            </div>
                            <div class="row heading-row">
                                <div class="col-md-3">
                                    <a :class="selectedSortOrder" @click.prevent="selectedSortOrderChanged()" href="#0">
                                        <strong><span>ID</span></strong>
                                    </a>
                                </div>
                                <div class="col-md-3"><strong>Title</strong></div>
                                <div class="col-md-3"><strong>Priority</strong></div>
                                <div class="col-md-3"><strong>Upsells Attached</strong></div>
                            </div>

                            <div class="panel-group">
                                <div class="panel panel-default" v-for="(upsell_type, index) in paginationResponse.data">
                                    <div class="panel-heading">
                                        <div class="row" :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <div class="col-md-3">{{upsell_type.id}}</div>
                                            <div class="col-md-3">
                                                <div class="single-line text-left" v-if="upsell_type.title !=='' && upsell_type.title !== null">
                                                    {{ upsell_type.title.substring(0,40) +(upsell_type.title > 40?'...':'')}}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="single-line text-center">
                                                    <span class="property-name-wrapper-at-propertieslist"> {{ upsell_type.priority_name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <span class="property-name-wrapper-at-propertieslist"> {{ upsell_type.attached_records }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--NO RECORD Degin-->
                                <!--<div class="property-card property-connected" v-if="paginationResponse.meta.total == 0">
                                    <div class="card-pane">
                                        <div class="row no-gutters">
                                            <div class="col-12">No Record found.</div>
                                        </div>
                                    </div>
                                </div>-->
                                <!--NO RECORD END-->
                            </div>

                        </div>
                    </div>
                    <!--end: table -->
                    <pagination :data="paginationResponse" :limit="1" @pagination-change-page="getUpsellTypeLists" align="right"></pagination>
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
                selectedSortOrder: 'selected sort-asc',
                serve_id: 0,
                properties: [],
                rooms: [],
                user_accounts: [],
            }
        },
        computed : {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                filters: (state) => {
                    return state.adminUpsellType.filters;
                },
                paginationResponse: (state) => {
                    return state.adminUpsellType.paginationResponse;
                },
            }),
            // ...mapActions([
            //     'getUpsellTypes',
            //     'getList',
            // ])
        },
        mounted() {
            this.$store.commit('SHOW_LOADER', null, {root: true});
            this.getGuideBookTypes;
            this.get_user_account();
            this.getUpsellTypeLists(); // Fetch List
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
            selectedSortOrderChanged() {
                this.filters.sortColumn = 'internal_name';
                if (this.selectedSortOrder === 'selected sort-desc') {
                    this.selectedSortOrder = 'selected sort-asc';
                    this.filters.sort.sortOrder = 'Asc';
                } else {
                    this.selectedSortOrder = 'selected sort-desc';
                    this.filters.sort.sortOrder = 'Desc';
                }
                this.getUpsellTypeLists();
            },

            /**
             * Getting Properties List Using Pagination
             */
            getUpsellTypeLists(page = 1) {
                let count = 0;
                if (this.filters.search.searchStr != '')
                    count++;
                if (this.filters.recordsPerPage != '10')
                    count++;
                this.filters.filter_count = count;
                this.filters.page = page;
                this.$store.dispatch('getUpsellsTypesLists');
            },

            /**
             * @param $event
             */
            searchProperties($event,from_btn=false) {
                if (this.filters.search.searchStr !== "" && (!from_btn && $event.keyCode !== 13)) {
                    return
                }
                this.getUpsellTypeLists();
            },
            sortOptionsChanged() {
                this.filters.sort.sortOrder = 'asc';
                this.getUpsellTypeLists();
            },
            resetFilters() {
                this.filters.recordsPerPage = 10;
                this.filters.page = 1;
                this.filters.search.searchStr = '';
                this.filters.sort.sortOrder = 'Desc';
                this.filters.sort.sortColumn = 'id';
                this.filters.filter_count = 0;
                this.filters.user_account_id = 'all';
                this.getUpsellTypeLists();
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
