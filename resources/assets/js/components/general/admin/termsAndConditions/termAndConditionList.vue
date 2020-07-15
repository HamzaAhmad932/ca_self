<template>
    <div>

        <!-- User List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-users"></i> Terms & Conditions
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Term & Condition List </span>
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
                                    <select v-model="filters.recordsPerPage" @change="getTermAndConditionList" class="custom-select custom-select-sm mb-2 mr-1" id="inlineFormInputName3" ref="recordsPerPage">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-search">Search</label>
                                    <input class="form-control form-control-sm" id="filter-search" type="text" placeholder="Start typing …" @keyup.prevent="searchProperties($event)" v-model="filters.search.searchStr">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="filter-user-accounts">User Account</label>
                                    <select class="custom-select custom-select-sm" id="filter-user-accounts" v-model="filters.user_account_id" @change.prevent="get_properties">
                                        <option value="all">All User Accounts</option>
                                        <option :value="user_account.id" v-for="user_account in user_accounts" v-if="user_accounts != null && user_accounts.length > 0"> {{ user_account.id +' -- '+user_account.name }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="filter-properties">Properties</label>
                                    <select class="custom-select custom-select-sm" id="filter-properties" v-model="filters.property_info_id" @change.prevent="getTermAndConditionList">
                                        <option value="all">All Properties</option>
                                        <option :value="property.id"  v-for="property in properties"> {{ property.name }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="filter-rentals">Rentals</label>
                                    <select class="custom-select custom-select-sm" id="filter-rentals"  v-model="filters.room_info_id" @change.prevent="getTermAndConditionList">
                                        <option value="all">All Rentals</option>
                                        <option :value="room.id" v-for="room in rooms"> {{ room.name }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <a @click.prevent="resetFilters()"
                                       class="btn btn-sm btn-block btn-danger float-right" href="#" id="reset-btn">Reset</a>
                                </div>
                            </div>
                            <div class="row heading-row">
                                <div class="col-md-2">
                                    <a :class="selectedSortOrder" @click.prevent="selectedSortOrderChanged()" href="#0">
                                        <strong><span>ID</span></strong>
                                    </a>
                                </div>
                                <div class="col-md-3"><strong>Name</strong></div>
                                <div class="col-md-3"><strong>Description</strong></div>
                                <div class="col-md-2"><strong>Status</strong></div>
                                <div class="col-md-2"><strong>Required Status</strong></div>
                            </div>

                            <div class="panel-group">
                                <div class="panel panel-default" v-for="(tac, index) in paginationResponse.data">
                                    <div class="panel-heading">
                                        <div class="row" :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <div class="col-md-2">{{tac.id}}</div>
                                            <div class="col-md-3">{{tac.internal_name}}</div>
                                            <div class="col-md-3">{{tac.text_content}}</div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="tac.status == 1">
                                                    <i class="fas fa-check-circle"></i>
                                                    Active
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-else>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Deactive
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="tac.required == 1">
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
                    <pagination :data="paginationResponse" :limit="1" @pagination-change-page="getTermAndConditionList" align="right"></pagination>
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
                selectedSortOrder : 'selected sort-asc',
                serve_id:0,
                user_accounts: [],
                properties:[],
                rooms:[],
            }
        },
        computed : {
            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                filters : (state)=>{
                    return state.admintac.filters;
                },
                paginationResponse : (state)=>{
                    return state.admintac.paginationResponse;
                },
            }),
            // ...mapActions([
            //     'getTermAndConditionList',
            // ])
        },
        mounted() {
            this.$store.commit('SHOW_LOADER', null, {root : true});
            this.get_user_account();
            this.get_properties();
            this.getTermAndConditionList(); // Fetch List
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
             * Load All Properties
             */
            get_properties() {
                let self = this;
                axios.post('/admin/get-properties-names', {'user_account_id': self.filters.user_account_id})
                    .then(response => {
                        self.properties = response.data;
                    });
                if (self.filters.user_account_id != 'all') {
                    this.getTermAndConditionList();
                }
            },
            /**
             * Load Selected Property Rentals
             */
            get_property_rooms(){
                let self = this;
                // this.filters.room_info_id='all';
                axios.post('/admin/get-properties-room-infos',{'property_info_id':self.filters.property_info_id})
                    .then(response => {
                        self.rooms = response.data;
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
                this.getTermAndConditionList();
            },

            /**
             * Getting Properties List Using Pagination
             */
            getTermAndConditionList(page = 1) {
                let count = 0;
                if(this.filters.user_account_id != 'all')
                    count++;
                if(this.filters.property_info_id != 'all')
                    count++;
                if(this.filters.room_info_id != 'all')
                    count++;
                if(this.filters.search.searchStr != '')
                    count++;
                if(this.filters.recordsPerPage != '10')
                    count++;
                this.filters.filter_count = count;
                this.filters.page = page;
                this.get_property_rooms();
                this.$store.dispatch('getTermAndConditionList');
            },

            /**
             * @param $event
             */
            searchProperties($event) {
                if($event.keyCode === 13){
                    this.getTermAndConditionList();
                }
            },
            sortOptionsChanged(){
                this.filters.sort.sortOrder = 'asc';
                this.getTermAndConditionList();
            },
            resetFilters(){
                this.filters.recordsPerPage = 10;
                this.filters.page = 1;
                this.filters.search.searchStr = '';
                this.filters.property_info_id = 'all';
                this.filters.room_info_id = 'all';
                this.filters.sort.sortOrder = 'Desc';
                this.filters.sort.sortColumn ='id';
                this.filters.filter_count = 0;
                this.getTermAndConditionList();
            },
        },
        filters: {
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
