<template>
    <div class="page-content" id="properties-listing-page">
        <div class="container">
            <div class="row" v-if="paginationResponse.meta !== undefined">
                <div class="col-md-12">
                    <div class="row"><div class="col-6"><a href="/client/v2/guide-books" class="text-muted d-block text-md mb-1"><i class="fas fa-arrow-circle-left"></i>&nbsp;<span class="hidden-xs">Back to </span> Guide Books list</a></div> </div>
                    <div class="page-header has-border-bottom mb-0">

                        <h1 class="page-title" style="display: inline;">Guide Book Types <span class="text-muted" v-if="paginationResponse.meta.total"> - {{paginationResponse.meta.total}}</span>
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
                                    onclick="window.location.href='guide-book-types-add'">
                                <i class="fas fa-plus"></i> <b>Add New Type</b>
                            </button>
<!--                            <button class="btn btn-sm btn-info mb-2 ml-1 sm-flex-auto" id="guide-book-types-adds"-->
<!--                                    onclick="window.location.href=''">-->
<!--                                <i class="fas fa-list "></i> <b>Guide Books List</b>-->
<!--                            </button>-->

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
                                        <div class="form-group col-md-1 col-sm-2">
                                            <a class="btn btn-sm btn-block btn-outline-dark"
                                               id="search-btn" href="#" @click.prevent="searchProperties(null,true)"><i class="fas fa-search"></i></a>
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
                                        <option value="title">title</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Desktop Sorting Options (hidden on mobile) -->
                            <div class="table-header d-none d-lg-block">
                                <div class="row">
                                    <div class="col-1 pl-2">
                                        <a href="#0"> <span>Icon</span></a>
                                    </div>
                                    <div class="col-4"><a href="#0"> <span>Title</span></a></div>
                                    <div class="col-2 text-center"><a href="#0"> <span>Priority</span></a></div>
                                    <div class="col-3 text-center"><a href="#0"> <span>Guide Books Attached</span></a></div>
                                    <div class="col-2 pl-2 text-right"><a href="#0"> <span>Action</span></a></div>
                                </div>
                            </div>


                            <!-- Property Card-->
                            <div class="property-card property-connected"
                                 v-for="(tac, index) in paginationResponse.data" :key="tac.id">
                                <div class="card-pane">
                                    <div class="for-booking-list-page-only-outer t-b-padding-lg-10">
                                        <div class="row no-gutters for-booking-list-page-only-inner">
                                            <div class="col-1 col-style">
                                                <div class="single-line text-left">
                                                    <i :class="tac.icon"></i>
                                                </div>
                                            </div>
                                            <div class="col-4 col-style">
                                                <div class="single-line text-left" v-if="tac.title !=='' && tac.title !== null">
                                                    {{ tac.title.substring(0,40) +(tac.title > 40?'...':'')}}
                                                </div>
                                            </div>
                                            <div class="col-2 col-style">
                                                <div class="single-line text-center">
                                                    <span class="property-name-wrapper-at-propertieslist"> {{ tac.priority_name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-3 col-style">
                                                <div class="single-line text-center">
                                                    <span class="property-name-wrapper-at-propertieslist"> {{ tac.attached_records }}</span>
                                                </div>
                                            </div>
                                            <div class="col-2 col-style">
                                                <div class="float-right d-flex ">
                                                    <div class="dropdown dropdown-sm">
                                                        <a class="btn btn-xs dropdown-toggle" id="moreMenu" href="#"
                                                           role="button" data-toggle="dropdown" aria-haspopup="true"
                                                           aria-expanded="false"><i class="fas fa-ellipsis-h"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right"
                                                             aria-labelledby="moreMenu">
                                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                               data-target="#m_modal_edit" @click="editRecord(tac.id)"
                                                               title="Edit">Edit</a>
                                                            <a class="dropdown-item" href="javascript:void(0)"  @click="deleteRecord(index)"
                                                               title="Delete">Delete</a>
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
                        <h5 class="modal-title">Edit Guide Book Type</h5>
                        <button type="button" id="closeModel" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body edit-form-outer-wrapper">
                        <!--Modal Pop-up Edit  Begin-->
                        <guide-books-types-add-form :serve_id="serve_id" @updated="getLists(filters.page)"></guide-books-types-add-form>
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
    import GuideBooksTypesAddForm from "./guide-books-types-add-form";

    export default {
        components: {GuideBooksTypesAddForm},
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
                filters: (state) => {
                    return state.general.guideBookTypes.filters;
                },
                paginationResponse: (state) => {
                    return state.general.guideBookTypes.paginationResponse;
                },
            }),
            ...mapActions([
                'general/getGuideBookTypesList'
            ])
        },
        mounted() {
            this.$store.commit('SHOW_LOADER', null, {root: true});
            this.getGuideBookTypes;
            this.getLists(); // Fetch List
        },
        methods: {

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
            deleteRecord:function(index){
                let self = this;
                let type = this.paginationResponse.data[index];
                let msg = "Do you really want to delete\n'"
                          +type.title+"' ?"
                          +(type.attached_records > 0 ?"\nAll "+type.attached_records+" attached Guide Books will also be deleted.":"");
                swal.fire({
                    title: msg,
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes",
                    cancelButtonText: 'No'
                }).then(async function (e) {
                    if (e.value === true) {
                        self.$store.dispatch('general/deleteGuideBookType',type.id);
                        self.getLists();
                    }
                });

            },

            /**
             * Getting Properties List Using Pagination
             */
            getLists(page = 1) {
                let count = 0;
                if (this.filters.search.searchStr != '')
                    count++;
                if (this.filters.recordsPerPage != '10')
                    count++;
                this.modalClosed('m_modal_edit');
                this.filters.filter_count = count;
                this.filters.page = page;
                this.$store.dispatch('general/getGuideBookTypesList');
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
            searchProperties($event,from_btn=false) {
                if (this.filters.search.searchStr !== "" && (!from_btn && $event.keyCode !== 13)) {
                    return
                }
                this.getLists();
            },
            sortOptionsChanged() {
                this.filters.sort.sortOrder = 'asc';
                this.getLists();
            },
            resetFilters() {
                this.filters.recordsPerPage = 10;
                this.filters.page = 1;
                this.filters.search.searchStr = '';
                this.filters.sort.sortOrder = 'Desc';
                this.filters.sort.sortColumn = 'id';
                this.filters.filter_count = 0;
                this.getLists();
            },
        },
        watch: {},
    }
</script>

<style scoped>

</style>
