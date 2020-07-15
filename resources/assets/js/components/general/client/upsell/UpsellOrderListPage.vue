<template>
    <div class="page-content" id="properties-listing-page">
        <div class="container">
            <div class="row" v-if="paginationResponse.meta !== undefined">
                <div class="col-md-12">
                    <div class="page-header has-border-bottom mb-0">
                        <h1 class="page-title property-page-title d-inline">
                            Upsell Orders <span class="text-muted" v-if="paginationResponse.meta.total"> - {{paginationResponse.meta.total}}</span>
                        </h1>
                        <button aria-controls="filter-collapse" aria-expanded="false" data-toggle="collapse"
                                class="btn btn-sm btn-theme mb-2 ml-2 filter-btn-margin sm-flex-auto d-inline-block"
                                href="#filter-collapse" role="button">
                            <i class="fas fa-filter"> </i>
                            <span class="d-none d-xs-inline d-sm-inline"> Filter</span>
                            <div class="filter-badge" v-if="filters.filter_count > 0">{{filters.filter_count}}</div>
                        </button>

                        <div class="booking-filter-stack">
                            <div class="d-flex sm-flex-auto">
                                <select v-model="filters.recordsPerPage" @change="getLists" class="custom-select custom-select-sm mb-2 mr-1" id="inlineFormInputName3" ref="recordsPerPage">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="page-body">
                        <div class="content-box">
                            <div class="collapse" id="filter-collapse">
                                <div class="filter-form">
                                    <div class="form-row align-items-end">
                                        <div class="form-group col-md-3 col-sm-5">
                                            <label for="filter-search">Search</label>
                                            <input class="form-control form-control-sm" id="filter-search" type="text" placeholder="Start typing …" @keyup.prevent="searchProperties($event)" v-model="filters.search.searchStr">
                                        </div>
                                        <div class="form-group col-md-1 col-sm-2">
                                            <a class="btn btn-sm btn-block btn-outline-danger float-right" id="reset-btn" href="#" @click.prevent="resetFilters()">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Sorting Options (hidden on desktop)-->
                            <div class="booking-table-filter-mobile d-block d-lg-none">
                                <div class="form-group">
                                    <select class="custom-select custom-select-sm" v-model="filters.sort.sortColumn" @change.prevent="sortOptionsChanged()">
                                        <option selected value="id">Sort by:</option>
                                        <option value="id">ID</option>
                                        <option value="final_amount">Final amount</option>
                                        <option value="status">Status</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Desktop Sorting Options (hidden on mobile) -->
                            <div class="table-header d-none d-lg-block">
                                <div class="row">
                                    <div class="col-2">
                                        <div class="table-box-check d-flex">
                                            <a :class="selectedSortOrder" href="#0" @click.prevent="selectedSortOrderChanged()"> <span>ID</span></a>
                                        </div>
                                    </div>
                                    <div class="col-2"><a  href="#0"> <span>Due Date</span></a></div>
                                    <div class="col-1"> <a href="#0"> <span>Booking#</span></a></div>
                                    <div class="col-2 text-center"> <a href="#0"> <span>Charge Reference</span></a></div>
                                    <div class="col-2"><a href="#0"> <span>Commission Paid</span></a></div>
                                    <div class="col-2"><a href="#0"> <span>Final Amount</span></a></div>
                                    <div class="col-1 pl-2"><a href="#0"> <span>Action</span></a></div>
                                </div>
                            </div>

                            <!-- Property Card-->
                            <div :class="'property-card ' + (upsell_order.payment_status.value ? 'property-connected' : 'property-disconnected')"
                                 v-for="(upsell_order, index) in paginationResponse.data" :key="upsell_order.id">
                                <div class="card-pane">
                                    <div class="for-booking-list-page-only-outer t-b-padding-lg-18">
                                        <div class="row no-gutters for-booking-list-page-only-inner">
                                            <div class="col-1 col-style">
                                                <div class="single-line text-muted">
                                                    {{ upsell_order.id }}
                                                </div>
                                            </div>
                                            <div class="col-3 col-style text-center">
                                                <div class="single-line text-muted">
                                                    {{upsell_order.due_date}}
                                                </div>
                                            </div>
                                            <div class="col-1 col-style">
                                                <div class="single-line text-muted">
                                                    {{upsell_order.pms_booking_id}}
                                                </div>
                                            </div>
                                            <div class="col-2 col-style text-right">
                                                <small>{{upsell_order.charge_ref_no != null ? upsell_order.charge_ref_no : ' -- '}} </small>
                                            </div>
                                            <div class="col-1 col-style pl-4 pl-lg-0 text-right"> {{upsell_order.currency_symbol}}{{upsell_order.commission_fee}}</div>
                                            <div class="col-2 col-style pl-4 pl-lg-0 text-right"> {{upsell_order.currency_symbol}}{{upsell_order.final_amount}}</div>
                                            <div class="col-1 col-style pl-4 pl-lg-0 text-right" style="padding: 0 10px;">
                                                <span v-if="upsell_order.payment_status.value" data-toggle="tooltip" data-placement="top" title="Amount Paid" class="badge badge-success status-badge-align ml-2">
                                                <i class="fas fa-check-circle"></i>
                                                {{upsell_order.payment_status.label}}
                                            </span>
                                                <span v-else data-toggle="tooltip" data-placement="top" title="Amount not Paid" class="badge badge-warning status-badge-align ml-2">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{upsell_order.payment_status.label}}
                                            </span>
                                            </div>
                                            <div class="col-1 col-style">
                                                <div class="d-flex align-items-center justify-content-between" style="padding-left: 20px;">
                                                    <div class="dropdown dropdown-sm">
                                                        <a class="btn btn-xs dropdown-toggle" id="moreMenu" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="moreMenu">
                                                            <a class="dropdown-item" target="_blank" :href="'/client/v2/booking-detail/'+ upsell_order.booking_info_id+'#upsell'"  title="View">View</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!--NO RECORD Begin-->
                            <div class="property-card property-connected" v-if="paginationResponse.meta.total == 0">
                                <div class="card-pane"><div class="row no-gutters"><div class="col-12">No Record found.</div></div></div>
                            </div>
                            <!--NO RECORD END-->

                        </div>
                    </div>
                    <div style="float: right !important;">
                        <pagination :data="paginationResponse" :limit="1" @pagination-change-page="getLists"></pagination>
                    </div>
                </div>
            </div>
        </div>
        <BlockUI :message="loader.msg" v-if="loader.block === true"  :html="loader.html"></BlockUI>
    </div>
</template>

<script>
    import {mapState, mapActions} from "vuex";
    export default {
        name: "UpsellOrderListPage",
        data() {
            return {
                selectedSortOrder : 'selected sort-desc',

            }
        },
        computed : {
            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                upsell_types : (state)=>{
                    return state.general.upsell.upsell_types;
                }, //not used yet...
                filters : (state)=>{
                    return state.general.upsell.filters;
                },
                paginationResponse : (state)=>{
                    return state.general.upsell.paginationResponse;
                },
              }),
            //...mapActions([//'getUpsellTypes','getList',])
        },
        mounted() {
            this.$store.commit('SHOW_LOADER', null, {root : true});
            this.filters.search.searchInColumn = ['id','final_amount', 'commission_fee', 'charge_ref_no', 'created_at'];
            this.getLists(); // Fetch getUpsellOrderList
        },
        methods: {
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
                this.getLists();
            },

            editRecord(id) {
                //this.serve_id = id;
            },

            /**
             * Getting Properties List Using Pagination
             */
            getLists(page = 1) {
                let count = 0;
                if(this.filters.upsell_type!='all')
                    count++;
                if(this.filters.search.searchStr != '')
                    count++;
                if(this.filters.recordsPerPage!='10')
                    count++;
                this.filters.filter_count = count;
                this.filters.page = page;
                this.$store.dispatch('general/getList', 'upsell-orders');
            },


            /**
             * @param $event
             */
            searchProperties($event) {
                if($event.keyCode === 13){
                    this.getLists();
                }
            },
            sortOptionsChanged(){
                this.filters.sort.sortOrder = 'asc';
                this.getLists();
            },
            resetFilters(){
                this.filters.recordsPerPage = 10;
                this.filters.page = 1;
                this.filters.search.searchStr = '';
                this.filters.upsell_type = 'all';
                this.filters.sort.sortOrder = 'Desc';
                this.filters.sort.sortColumn ='id';
                this.filters.filter_count = 0;
                this.getLists();
            },
        },
        watch: {

        },
    }
</script>
