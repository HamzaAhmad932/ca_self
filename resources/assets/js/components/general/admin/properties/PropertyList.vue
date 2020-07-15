<template>
    <div>
        <!-- Property List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">Properties</h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Property List </span>
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
                                    <select @change="getProperties()" class="custom-select custom-select-sm mb-2 mr-1"
                                            id="inlineFormInputName3" ref="recordsPerPage"
                                            v-model="filters.per_page">
                                        <option selected value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-search">Search</label>
                                    <input @keyup.prevent="searchProperties($event)" class="form-control form-control-sm" id="filter-search"
                                           placeholder="Start typing …"
                                           type="text" v-model="filters.search">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-user-account">All User Account</label>
                                    <select @change.prevent="updateUserAccountId($event)" class="custom-select custom-select-sm"
                                            id="filter-user-account" v-model="filters.user_account_id">
                                        <option value="0" selected>All</option>
                                        <option :value="user_account.id" v-for="user_account in user_accounts" v-if="user_accounts != null && user_accounts.length > 0"> {{ user_account.id +' -- '+user_account.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="filter-cities">All Cities</label>
                                    <select @change.prevent="getProperties()" class="custom-select custom-select-sm"
                                            id="filter-cities" v-model="filters.city">
                                        <option value="all">All</option>
                                        <option :value="city" v-for="city in cities"
                                                v-if="city != null && city.length > 0"> {{ city }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <a @click.prevent="resetFilters()"
                                       class="btn btn-sm btn-block btn-danger float-right" href="#" id="reset-btn">Reset</a>
                                </div>
                            </div>
                            <div class="row heading-row">
                                <div class="col-md-1"><strong>ID</strong></div>
                                <div class="col-md-1"><strong>User</strong></div>
                                <div class="col-md-2">
                                    <a :class="selectedSortOrder" @click.prevent="selectedSortOrderChanged()" href="#0">
                                        <strong><span>Name</span></strong>
                                    </a>
                                </div>
                                <div class="col-md-2"><strong>Property Timezone</strong></div>
                                <div class="col-md-1"><strong>Payment setting</strong></div>
                                <div class="col-md-1"><strong>Currency</strong></div>
                                <div class="col-md-2"><strong>Connected</strong></div>
                                <div class="col-md-2"><strong>Verify on PMS</strong></div>
                            </div>

                            <div class="panel-group">
                                <div class="panel panel-default" v-for="(propertyInfo, index) in propertyInfos">
                                    <div class="panel-heading">
                                        <div class="row" :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <div class="col-md-1"><a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View">{{propertyInfo.id}}</a></div>
                                            <div class="col-md-1">
                                                <a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View"><strong>User ID: </strong>{{propertyInfo.user_id}}</a><br />
                                                <a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View"><strong>User Account ID: </strong>{{propertyInfo.user_account_id}}</a>
                                            </div>
                                            <div class="col-md-2">
                                                <a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View">
                                                    <div class="m-card-user m-card-user--sm" title="Click for Detail">
                                                        <div class="m-card-user__pic">
                                                            <div class="m-card-user__no-photo m--bg-fill-warning">
                                                                <span>
                                                                    <a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View"><img :src="['/storage/uploads/property_logos/' +  propertyInfo.logo ]" @error="imageUrlAlt" class="img-responsive"></a>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="m-card-user__details">
                                                            <span class="m-card-user__name" title="Click for Detail">
                                                                <a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View">{{ propertyInfo.name }}</a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-md-2"><a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View">{{propertyInfo.time_zone}}</a></div>
                                            <div class="col-md-1"><a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View">{{propertyInfo.use_pg_settings | propertyPaymentSetting}}</a></div>
                                            <div class="col-md-1"><a :href="'/admin/property/'+propertyInfo.id" target="_blanck" title="View">{{propertyInfo.currency_code}}</a></div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected" v-if="propertyInfo.status == 1">
                                                    <i class="fas fa-check-circle"></i>
                                                    Connected
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected" v-else>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Disconnected
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <a :class="propertyInfo.status == 1 ? ' ' : 'disabled'" class="btn btn-sm btn-primary"  href="javaScript:void(0)" @click="verificationOnPMS(propertyInfo.id)"> Verify The Property </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default" v-if="paginationResponse.total == 0">
                                    <div class="panel-heading">
                                        <div class="panel-title">No property found.</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--end: table -->
                    <pagination :data="paginationResponse" :limit="1" @pagination-change-page="getProperties" align="right"></pagination>
                </div>
            </div>
        </div>
        <!-- End Booking List-->

        <!-- Block UI Loader-->
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
        <!-- Block UI Loader-->

    </div>
</template>

<script scoped>
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';
    Vue.use(VueToast);
    export default {
        props: ['user_account_id'],
        data() {
            return {
                msg: 'Please Wait...',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                propertyInfos: {},
                paginationResponse: {},
                action: '',
                selectedSortOrder: 'selected sort-asc',
                file_name: 'Properties-List',
                cities: {},
                user_accounts: {},
                filter_count: 0,
                filters: {
                    per_page: 10,
                    search: '',
                    city: 'all',
                    sortOrder: 'Asc',
                    sortColumn: 'name',
                    user_account_id: 0,
                },
            }
        },
        components: {
        },
        mounted() {
            this.filters.user_account_id = this.user_account_id;
            this.getProperties(); // Fetch initial results
            this.getAllPropertiesCities();
            this.getAllUsers();

        },
        methods: {
            updateUserAccountId(event) {
                this.filters.user_account_id = event.target.value;
                this.getAllPropertiesCities();
                this.getProperties();
            },
            verificationOnPMS(property_id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to be verify this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.value) {
                        swal({
                            title: 'Please Wait..!',
                            text: 'Is working..',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            onOpen: () => {
                                swal.showLoading()
                            }
                        });

                        axios({
                            url : '/admin/verification-detail',
                            method : 'POST',
                            data: {property_id:property_id},
                        }).then(resp => {
                            swal.hideLoading();
                            if (resp.data.status_code == 200) {
                                Swal.fire(
                                    'Verified!',
                                    resp.data.message,
                                    'success'
                                )
                            } else if (resp.data.status_code == 404) {
                                Swal.fire(
                                    'Error!',
                                    resp.data.message,
                                    'error'
                                )
                            }

                        });


                    } else if (
                        /* Read more about handling dismissals below */
                        result.dismiss === Swal.DismissReason.cancel
                    ) {
                        Swal.fire(
                            'Cancelled',
                            'Your imaginary file is safe :)',
                            'error'
                        )
                    }
                })
            },


            /**
             * Toaster View on any action
             * @param msg
             * @param status
             */
            toasterView(msg, status = false) {
                let type = (status ? 'success' : 'error');
                Vue.$toast.open({message: msg, duration: 3000, type: type, position: 'top-right',});
            },

            /**
             * Change Sort Order By Name Column ASc or DESc For Desktop
             */
            selectedSortOrderChanged() {
                this.filters.sortColumn = 'name';
                if (this.selectedSortOrder === 'selected sort-desc') {
                    this.selectedSortOrder = 'selected sort-asc';
                    this.filters.sortOrder = 'Asc';
                } else {
                    this.selectedSortOrder = 'selected sort-desc';
                    this.filters.sortOrder = 'Desc';
                }
                this.getProperties();
            },

            /**
             * Getting Properties List Using Pagination
             */
            getProperties(page = 1) {
                this.block = true;
                var self = this;
                self.msg = "Loading Properties";
                axios.post('/admin/get-properties?page=' + page, {'filters': self.filters})
                    .then(function (response) {
                        let count = 0;
                        if (self.filters.city != 'all') {
                            count++;
                        }
                        if (self.filters.search != '') {
                            count++;
                        }
                        if (self.filters.per_page != '10') {
                            count++;
                        }
                        self.filter_count = count;

                        self.paginationResponse = response.data.data;
                        self.propertyInfos = self.paginationResponse.data;
                        self.block = false;
                        self.msg = "Please Wait";
                    })
                    .catch(function (error) {
                        toastr.error("Some error while fetching properties.");
                    });
            },

            /**
             * Getting Properties Cities List
             */
            getAllPropertiesCities() {
                var self = this;
                // self.user_account_id =
                axios.post('/admin/get-all-properties-cities', {'user_account_id': self.filters.user_account_id})
                    .then(response => {
                        self.cities = response.data;
                        self.getProperties();
                    });
            },
            getAllUsers(){
                var self = this;
                axios.get('/admin/get-all-user-accounts', self.user_account_id)
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
             *
             * @param $event
             */
            searchProperties($event) {
                if ($event.keyCode === 13) {
                    this.getProperties();
                }
            },

            resetFilters() {
                this.filters.per_page = 10;
                this.filters.search = '';
                this.filters.city = 'all';
                this.filters.user_account_id = 0;
                this.filters.sortOrder = 'ASC';
                this.filters.sortColumn = 'name';
                this.getProperties();
            },

            imageUrlAlt(event) {
                event.target.src = "/storage/uploads/property_logos/no_image.png";
            }

        },
        /*Vue Methods End*/
        filters: {
            propertyPaymentSetting: function (value) {
                if (value == 0) {
                    return 'Global Settings Active';
                } else {
                    return 'Custome Settings Active';
                }
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
        padding: 1rem 0rem 1rem 0rem;
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
</style>
