<template>
    <div class="page-content" id="properties-listing-page">
        <div class="container">
            <div class="row" v-if="paginationResponse.meta !== undefined">
                <div class="col-md-12">
                    <div class="page-header has-border-bottom mb-0">
                        <h1 class="page-title" style="display: inline;">Upsells <span class="text-muted" v-if="paginationResponse.meta.total"> - {{paginationResponse.meta.total}}</span></h1>
                        <button class="btn btn-sm btn-theme mb-2 ml-1 sm-flex-auto" data-toggle="collapse" href="#filter-collapse" role="button" aria-expanded="false" aria-controls="filter-collapse">
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
                            <div v-if="disable_upsell == 1" >
                                <button disabled="true"  class="btn btn-sm btn-success mb-2 ml-1 sm-flex-auto" id="add-upsell-dis">
                                    <i class="fas fa-plus"></i> Add Upsell
                                </button>
                                <button disabled="true" class="btn btn-sm btn-info mb-2 ml-1 sm-flex-auto" id="upsell-types-adds-dis" >
                                    <i class="fas fa-list "></i> <b>Upsell Type</b>
                                </button>
                            </div>
                            <div v-else >
                                <button class="btn btn-sm btn-success mb-2 ml-1 sm-flex-auto" id="add-upsell" onclick="window.location.href='upsell-add'">
                                    <i class="fas fa-plus"></i> Add Upsell
                                </button>
                                <button class="btn btn-sm btn-info mb-2 ml-1 sm-flex-auto" id="upsell-types-adds"
                                        onclick="window.location.href='upsell-types'">
                                    <i class="fas fa-list "></i> <b>Upsell Types</b>
                                </button>
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
                                        <div class="form-group col-md-2 col-sm-5">
                                            <label for="filter-Types">Upsell Types</label>
                                            <select class="custom-select custom-select-sm" id="filter-Types" v-model="filters.upsell_type" @change.prevent="getLists">
                                                <option value="all">All</option>
                                                <option :value="upsell_type.id"  v-for="upsell_type in upsell_types"> {{ upsell_type.title }}</option>
                                            </select>
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
                                        <option value="internal_name">Internal Name</option>
                                        <option value="status">Status</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Desktop Sorting Options (hidden on mobile) -->
                            <div class="table-header d-none d-lg-block">
                                <div class="row">
                                    <div class="col-2"><a :class="selectedSortOrder" href="#0" @click.prevent="selectedSortOrderChanged()"> <span>Internal Name</span></a></div>
                                    <div class="col-3 text-center"> <a href="#0"> <span>Period</span></a></div>
                                    <div class="col-2 text-center"><a href="#0"> <span>Type</span></a></div>
                                    <div class="col-3 text-center"><a href="#0"> <span>Connection Status</span></a></div>
                                    <div class="col-1 pl-2 text-right"><a href="#0"> <span>Action</span></a></div>
                                </div>
                            </div>


                            <!-- Property Card-->
                            <div :class="'property-card  ' + (upsell.status.value  ? 'property-connected' : 'property-disconnected')"  v-for="(upsell, index) in paginationResponse.data" :key="upsell.id">
                                <div class="card-pane">
                                    <div class="for-booking-list-page-only-outer t-b-padding-lg-10">
                                        <div class="row no-gutters for-booking-list-page-only-inner">
                                            <div class="col-3 col-style">
                                                <div class="single-line">
                                                    <span class="property-name-wrapper-at-propertieslist"> {{ upsell.internal_name.substring(0, 30) }}{{upsell.internal_name.length > 30 ? '...':''}}</span>
                                                </div>
                                            </div>
                                            <div class="col-2 col-style">
                                                <div class="single-line text-muted"> {{upsell.per.label}} - {{upsell.period.label}}</div>
                                            </div>
                                            <div class="col-2 col-style pl-4 pl-lg-0 text-center">{{upsell.type}}</div>
                                            <div class="col-3 col-style pl-4 pl-lg-0 text-right" style="padding: 0 10px;">
                                                <div class="checkbox-toggle checkbox-choice">
                                                    <input :id="'checkbox-'+upsell.id" type="checkbox"  :name="'checkbox-'+upsell.id" :checked="upsell.status.value == 1" @change="connectOrDisconnect($event, upsell.id, index)"/>
                                                    <label class="checkbox-label" :for="'checkbox-'+upsell.id" data-on="ON" data-off="OFF">
                                                    <span class="toggle-track">
                                                        <span class="toggle-switch"></span>
                                                    </span>
                                                        <span class="toggle-title"></span>
                                                    </label>
                                                </div>
                                                <span v-if="upsell.status.value" data-toggle="tooltip" data-placement="top" title="Property Connected" class="badge badge-success status-badge-align ml-2">
                                                <i class="fas fa-check-circle"></i>
                                                Active
                                            </span>
                                                <span v-else data-toggle="tooltip" data-placement="top" title="Property Disconnected" class="badge badge-warning status-badge-align ml-2">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                In-active
                                            </span>
                                            </div>
                                            <div class="col-1 col-style">
                                                <div class="d-flex float-right">

                                                    <div class="dropdown dropdown-sm">
                                                        <a class="btn btn-xs dropdown-toggle" id="moreMenu" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="moreMenu">
                                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#m_modal_edit" @click="editRecord(upsell.id)" title="Edit Record">Edit</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a class="card-collapse collapsed"  data-toggle="collapse" :href="'#property-collapse'+upsell.id" role="button" aria-expanded="false" aria-controls="'property-collapse'+upsell.id"><i class="fas fa-chevron-up"></i></a>
                                <div class="property-card-body collapse" :id="'property-collapse'+upsell.id">

                                    <div class="property-card-details">
                                        <div class="card-section">
                                            <div class="card-section-title">Attached Rentals</div>
                                            <div class="card-inset-table">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <tr>
<!--                                                            <th>Sr.</th>-->
                                                            <th>Property ID</th>
                                                            <th>Property Name</th>
                                                            <th style="text-align: left">Rentals</th>
                                                        </tr>
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
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Property Card-->
                            </div>
                            <!-- Property END-->


                            <!--NO RECORD Degin-->
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

        <!--    Modal Pop-up Booking Source Begin-->
        <div class="modal fade show" id="m_modal_edit" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog modal-lg" role="document" >
                <div class="modal-content" >
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Upsell</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body edit-form-outer-wrapper">
                        <!--Modal Pop-up Edit Upsell Begin-->
                        <add-upsell :serve_id="serve_id" @updated=""></add-upsell>
                        <!--Modal Pop-up EDIT Upsell End-->
                    </div>
                    <!--<div class="modal-footer mt-4"></div>-->
                </div>
            </div>
        </div>
        <!--    Modal Pop-up Booking Source End-->
        <BlockUI :message="loader.msg" v-if="loader.block === true"  :html="loader.html"></BlockUI>
        
        <button style="display: none;" type="button" data-toggle="modal" data-target="#upsell-disabled-for-stripe" id="hidden-alert-button">upsell-disabled-for-stripe</button>
            
            <div aria-labelledby="upsell-disabled-for-stripe" id="upsell-disabled-for-stripe" aria-hidden="true" class="modal fade" role="dialog" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"><strong>⚠️ Upsell Not Supported</strong></h4>
                            <button aria-label="Close" class="close" data-dismiss="modal" type="button"> <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                        </div>
                        
                        <div class="modal-body">
                            
                            <p>Your Stripe Account does not support Stripe Express, a mandatory feature for the Upsell to work.
                                <br><br>
                                Click <a href="https://stripe.com/global" target="_blank">here</a> to view list of currently supported countries.
                            </p>
                            <!-- <p>
                                In case it's not true please feel free to contact support.
                            </p> -->
                            
                        </div>
                    </div>
                </div>
            </div>
        
    </div>
</template>

<script>
    import {mapState, mapActions} from "vuex";
    export default {
        props: {disable_upsell:{default: 0}},
        name: "UpsellListPage",
        data() {
            return {
                selectedSortOrder : 'selected sort-asc',
                serve_id:0,
            }
        },
        computed : {
            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                upsell_types : (state)=>{
                    return state.general.upsell.upsell_types;
                },
                filters : (state)=>{
                    return state.general.upsell.filters;
                },
                paginationResponse : (state)=>{
                    return state.general.upsell.paginationResponse;
                },
              }),
            ...mapActions([
                'general/getUpsellTypes',
                'general/getList',
            ])
        },
        mounted() {
            
            if(this.disable_upsell == 1) {    
                $('#hidden-alert-button').click();
            }
            
            this.$store.commit('SHOW_LOADER', null, {root : true});
            this.$store.dispatch('general/getUpsellTypes',{
                for_filters:false,
                serve_id:0
            });
            // this.(this.$store.commit,); // Fetch getUpsellTypes
            this.getLists(); // Fetch getUpsellList
            

        },
        methods: {
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
                this.getLists();
            },

            editRecord(id) {
                this.serve_id = id;
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
                this.$store.dispatch('general/getList');
            },

            modalClosed(modalId) {
                // if (document.querySelector('#'+modalId).className  == "modal fade") {
                //     this.$store.commit('SHOW_LOADER', null, {root : true});
                //     this.$store.dispatch('getList');
                //     this.serve_id = 0;
                // }
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

            connectOrDisconnect(event, id, index) {
                let self = this;
                let status = event.target.checked;
                let msg = status  ?
                    'ChargeAutomation will start processing against this Upsell\'s rentals' :
                    'Are you sure you want to disable this Upsell for all rentals?';
                swal.fire({
                    title: msg,
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, "+ (status ? 'enable' : 'disable') +"!",
                    cancelButtonText:'No, cancel'
                }).then(function (e) {
                    let payload = {id:id, status : status, index: index};
                    if (e.value === true) {
                        payload.status = status ? 1 : 0;
                        self.$store.dispatch('general/connectOrDisconnect', payload);
                    } else {
                        payload.status = status ? 0 : 1;
                        event.target.checked = (payload.status == 1);
                        self.$store.commit('general/UPSELL_LIST_STATUS_UPDATE', payload);
                    }
                });
            },
        },
        watch: {
            
        },
    }
</script>
