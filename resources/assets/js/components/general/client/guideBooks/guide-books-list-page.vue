<template>
    <div class="page-content" id="properties-listing-page">
        <div class="container">
            <div class="row" v-if="paginationResponse.meta !== undefined">
                <div class="col-md-12">
                    <div class="page-header has-border-bottom mb-0">
                        <h1 class="page-title" style="display: inline;">Guide Books <span class="text-muted" v-if="paginationResponse.meta.total"> - {{paginationResponse.meta.total}}</span>
                        </h1>
                        <button class="btn btn-sm btn-theme mb-2 ml-1 sm-flex-auto" data-toggle="collapse"
                                href="#filter-collapse" role="button" aria-expanded="false"
                                aria-controls="filter-collapse">
                            <i class="fas fa-filter"> </i>
                            <span class="d-none d-xs-inline d-sm-inline"> Filter</span>
                            <div class="filter-badge" v-if="filters.filter_count > 0">{{filters.filter_count}}</div>
                        </button>

                        <div class="booking-filter-stack">
                            <div class="d-flex sm-flex-auto">
                                <select v-model="filters.recordsPerPage" @change="getLists"
                                        class="custom-select custom-select-sm mb-2 mr-1" id="inlineFormInputName3"
                                        ref="recordsPerPage">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <button class="btn btn-sm btn-success mb-2 ml-1 sm-flex-auto" id="guide-books-add"
                                    onclick="window.location.href='guide-books-add'">
                                <i class="fas fa-plus"></i> <b>Add New Guide Book</b>
                            </button>
                            <button class="btn btn-sm btn-info mb-2 ml-1 sm-flex-auto" id="guide-book-types-adds"
                                    onclick="window.location.href='guide-books-types'">
                                <i class="fas fa-list "></i> <b>Guide Books Type</b>
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
                                            <input class="form-control form-control-sm" id="filter-search" type="text"
                                                   placeholder="Start typing …"
                                                   @keyup.prevent="searchProperties($event)"
                                                   v-model="filters.search.searchStr">
                                        </div>
                                        <div class="form-group col-md-2 col-sm-5">
                                            <label for="filter-properties">Type</label>
                                            <select class="form-control form-control-sm" id="type"
                                                    v-model="filters.type_id" @change.prevent="getLists">
                                                <option value="all">All Types</option>
                                                <option v-for="type in types" :value="type.id">{{type.title}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-2 col-sm-5">
                                            <label for="filter-properties">Properties</label>
                                            <select class="custom-select custom-select-sm" id="filter-properties"
                                                    v-model="filters.property_info_id" @change.prevent="getLists">
                                                <option value="all">All Properties</option>
                                                <option :value="property.id" v-for="property in properties"> {{
                                                    property.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2 col-sm-5">
                                            <label for="filter-rentals">Rentals</label>
                                            <select class="custom-select custom-select-sm" id="filter-rentals"
                                                    v-model="filters.room_info_id" @change.prevent="getLists">
                                                <option value="all">All Rentals</option>
                                                <option :value="room.id" v-for="room in rooms"> {{ room.name }}</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-1 col-sm-2">
                                            <a class="btn btn-sm btn-block btn-outline-danger float-right"
                                               id="reset-btn" href="#" @click.prevent="resetFilters()">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Mobile Sorting Options (hidden on desktop)-->
                            <div class="booking-table-filter-mobile d-block d-lg-none">
                                <div class="form-group">
                                    <select class="custom-select custom-select-sm" v-model="filters.sort.sortColumn"
                                            @change.prevent="sortOptionsChanged()">
                                        <option selected value="id">Sort by:</option>
                                        <option value="internal_name">Name</option>
                                        <option value="status">Status</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Desktop Sorting Options (hidden on mobile) -->
                            <div class="table-header d-none d-lg-block">
                                <div class="row">
                                    <div class="col-1 pl-2">
                                        <a href="#0"> <span>Icon</span></a>
                                    </div>
                                    <div class="col-2"><a href="#0"> <span>Name</span></a></div>
                                    <div class="col-3"><a href="#0"> <span>Description</span></a></div>
                                    <div class="col-2 text-center"><a href="#0"> <span>Type</span></a></div>
                                    <div class="col-3 text-center"><a href="#0"> <span>Publish Status</span></a></div>
                                    <div class="col-1 pl-2 text-right"><a href="#0"> <span>Action</span></a></div>
                                </div>
                            </div>


                            <!-- Property Card-->
                            <div :class="'property-card  ' + (tac.status  ? 'property-connected' : 'property-disconnected')"
                                 v-for="(tac, index) in paginationResponse.data" :key="tac.id">
                                <div class="card-pane">
                                    <div class="for-booking-list-page-only-outer t-b-padding-lg-10">
                                        <div class="row no-gutters for-booking-list-page-only-inner">
                                            <div class="col-1 col-style">
                                                <div class="single-line text-left">
                                                    <i :class="tac.icon"></i>
                                                </div>
                                            </div>
                                            <div class="col-2 col-style">
                                                <div class="single-line text-left" v-if="tac.internal_name !=='' && tac.internal_name !== null">
                                                    {{ tac.internal_name.substring(0,10) +(tac.internal_name.length > 10?'...':'')}}
                                                </div>
                                            </div>
                                            <div class="col-3 col-style">
                                                <div class="single-line text-left">
                                                    {{ tac.text_content.substring(0,30) +(tac.text_content.length > 30?'...':'')}}
                                                </div>
                                            </div>
                                            <div class="col-2 col-style">
                                                <div class="single-line text-center">
                                                    <span class="property-name-wrapper-at-propertieslist"> {{ tac.type }}</span>
                                                </div>
                                            </div>
                                            <div class="col-3 col-style pl-4 pl-lg-0 text-center"
                                                 style="padding: 0 10px;">
                                                <div class="checkbox-toggle checkbox-choice">
                                                    <input :id="'checkbox_status-'+tac.id" type="checkbox"
                                                           :name="'checkbox_status-'+tac.id" :checked="tac.status== 1"
                                                           @change="connectOrDisconnect($event, tac.id, index)"/>
                                                    <label class="checkbox-label" :for="'checkbox_status-'+tac.id"
                                                           data-on="ON" data-off="OFF">
                                                    <span class="toggle-track">
                                                        <span class="toggle-switch"></span>
                                                    </span>
                                                        <span class="toggle-title"></span>
                                                    </label>
                                                </div>
                                                <span v-if="tac.status" data-toggle="tooltip" data-placement="top"
                                                      title="Active For Attached Rentals"
                                                      class="badge badge-success status-badge-align ml-2">
                                                <i class="fas fa-check-circle"></i>
                                                Active
                                            </span>
                                                <span v-else data-toggle="tooltip" data-placement="top"
                                                      title="In-active For Attached Rentals"
                                                      class="badge badge-warning status-badge-align ml-2">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                In-active
                                            </span>
                                            </div>
                                            <div class="col-1 col-style">
                                                <div class="float-right d-flex ">

                                                    <div class="dropdown dropdown-sm">
                                                        <a class="btn btn-xs dropdown-toggle" id="moreMenu" href="#"
                                                           role="button" data-toggle="dropdown" aria-haspopup="true"
                                                           aria-expanded="false"><i class="fas fa-ellipsis-h"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right"
                                                             aria-labelledby="moreMenu">
                                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                               data-target="#m_modal_edit" @click="editRecord(tac.id)"
                                                               title="Edit Record">Edit</a>
                                                        </div>
                                                    </div>
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
                                <div class="card-pane">
                                    <div class="row no-gutters">
                                        <div class="col-12">No Record found.</div>
                                    </div>
                                </div>
                            </div>
                            <!--NO RECORD END-->

                        </div>
                    </div>
                    <div style="float: right !important;">
                        <pagination :data="paginationResponse" :limit="1"
                                    @pagination-change-page="getLists"></pagination>
                    </div>
                </div>
            </div>
        </div>

        <!--    Modal Pop-up Booking Source Begin-->
        <div class="modal fade show" id="m_modal_edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Guide Books</h5>
                        <button type="button" id="closeModel" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body edit-form-outer-wrapper">
                        <!--Modal Pop-up Edit  Begin-->
                        <guide-books-add-form :serve_id="serve_id" @updated="getLists(filters.page)"></guide-books-add-form>
                        <!--Modal Pop-up EDIT  End-->
                    </div>
                </div>
            </div>
        </div>
        <!--    Modal Pop-up Booking Source End-->
        <BlockUI :message="loader.msg" v-if="loader.block === true" :html="loader.html"></BlockUI>
    </div>
</template>

<script>
    import {mapActions, mapState} from "vuex";
    import GuideBooksAddForm from "./guide-books-add-form";

    export default {
        components: {GuideBooksAddForm},
        data() {
            return {
                selectedSortOrder: 'selected sort-asc',
                serve_id: 0,
                properties: [],
                rooms: [],
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                types: (state) => {
                    return state.general.guideBook.types
                },
                filters: (state) => {
                    return state.general.guideBook.filters;
                },
                paginationResponse: (state) => {
                    return state.general.guideBook.paginationResponse;
                },
            }),
            ...mapActions([
                'general/getGuideBookList', 'general/getGuideBookTypes'
            ])
        },
        mounted() {
            this.$store.commit('SHOW_LOADER', null, {root: true});
            this.getGuideBookTypes;
            this.getLists(); // Fetch List
            this.get_properties();
        },
        methods: {
            /**
             * Load All Properties
             */
            get_properties() {
                let self = this;
                axios.post('/client/v2/get-properties-names')
                    .then(response => {
                        this.properties = response.data;
                    });
            },
            /**
             * Load Selected Property Rentals
             */
            get_property_rooms() {
                let self = this;
                // this.filters.room_info_id='all';
                axios.post('/client/v2/get-properties-room-infos', {'property_info_id': this.filters.property_info_id})
                    .then(response => {
                        this.rooms = response.data;
                    });
            },
            /**
             * Change Sort Order By Name Column ASc or DESc For Desktop
             */
            selectedSortOrderChanged() {
                this.filters.sortColumn = 'internal_name';
                if (this.selectedSortOrder === 'selected sort-desc') {
                    this.selectedSortOrder = 'selected sort-asc';
                    this.filters.sortOrder = 'Asc';
                } else {
                    this.selectedSortOrder = 'selected sort-desc';
                    this.filters.sortOrder = 'Desc';
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
                if (this.filters.type_id != 'all')
                    count++;
                if (this.filters.property_info_id != 'all')
                    count++;
                if (this.filters.room_info_id != 'all')
                    count++;
                if (this.filters.search.searchStr != '')
                    count++;
                if (this.filters.recordsPerPage != '10')
                    count++;
                this.modalClosed('m_modal_edit');
                this.filters.filter_count = count;
                this.filters.page = page;
                this.get_property_rooms();
                this.$store.dispatch('general/getGuideBookList');
            },

            modalClosed(modalId) {
                document.getElementById("closeModel").click();
                setTimeout(function () {
                    this.serve_id=0;
                },500);
            },

            /**
             * @param $event
             */
            searchProperties($event) {
                if ($event.keyCode === 13) {
                    this.getLists();
                }
            },
            sortOptionsChanged() {
                this.filters.sort.sortOrder = 'asc';
                this.getLists();
            },
            resetFilters() {
                this.filters.recordsPerPage = 10;
                this.filters.page = 1;
                this.filters.search.searchStr = '';
                this.filters.type_id = 'all'
                this.filters.property_info_id = 'all';
                this.filters.room_info_id = 'all';
                this.filters.sort.sortOrder = 'Desc';
                this.filters.sort.sortColumn = 'id';
                this.filters.filter_count = 0;
                this.getLists();
            },

            connectOrDisconnect(event, id, index) {
                let self = this;
                let status = event.target.checked;
                let msg = (status ?
                    'Do You Really Want To Activate?' :
                    'Do You Really Want To In Active?');
                swal.fire({
                    title: msg,
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes",
                    cancelButtonText: 'No'
                }).then(async function (e) {
                    let payload = {'id': id, 'updateWhat': 'status', 'updateWith': status};
                    if (e.value === true) {
                        if (await self.$store.dispatch('general/updateGuideBookStatus', payload)) {
                            self.$store.commit('general/UPDATE_GUIDE_BOOK_STATUS', {
                                index: index,
                                'updateWhat': 'status',
                                'updateWith': status
                            });
                        } else {
                            status = (status ? false : true);
                            event.target.checked = status;
                            self.$store.commit('general/UPDATE_GUIDE_BOOK_STATUS', {
                                index: index,
                                'updateWhat': 'required',
                                'updateWith': status
                            });
                        }
                    } else {
                        status = (status ? false : true);
                        event.target.checked = status;
                        self.$store.commit('general/UPDATE_GUIDE_BOOK_STATUS', {
                            index: index,
                            'updateWhat': 'required',
                            'updateWith': status
                        });
                    }
                });
            },
        },
        watch: {},
    }
</script>

<style scoped>

</style>
