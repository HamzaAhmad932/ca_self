<template>
    <div>
        <!-- User Account List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-users"></i> User Accounts
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">User Account List </span>
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
                            <div class="row filter-row">
                                <div class="form-group col-md-1">
                                    <label>&nbsp;</label>
                                    <select @change="getUserAccounts()" class="custom-select custom-select-sm mb-2 mr-1"
                                            id="inlineFormInputName3" ref="recordsPerPage"
                                            v-model="filters.per_page">
                                        <option selected value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 offset-md-6">
                                    <label for="filter-search">Search</label>
                                    <input @keyup="getUserAccounts()" class="form-control form-control-sm" id="filter-search"
                                           placeholder="Start typing …"
                                           type="text" v-model="filters.search">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <a @click.prevent="resetFilters()"
                                       class="btn btn-sm btn-block btn-danger float-right" href="#" id="reset-btn">Reset</a>
                                </div>
                            </div>
                            <div class="row heading-row">
                                <div class="col-md-1"><strong>More</strong></div>
                                <div class="col-md-1"><strong>ID</strong></div>
                                <div class="col-md-3">
                                    <a :class="selectedSortOrder" @click.prevent="selectedSortOrderChanged()" href="#0">
                                        <strong><span>Company</span></strong>
                                    </a>
                                </div>
                                <div class="col-md-2"><strong>Contact</strong></div>
                                <div class="col-md-2"><strong>Account Status</strong></div>
                                <div class="col-md-2"><strong>Integration Status</strong></div>
                                <div class="col-md-1"><strong>Actions</strong></div>
                            </div>

                            <div class="panel-group">
                                <div class="panel panel-default" v-for="(data, index) in user_accounts">
                                    <div class="panel-heading">
                                        <div class="row" :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <div class="col-md-1">
                                                <a :aria-controls="'id_'+data.user_account.id"
                                                   :href="'#id_'+data.user_account.id"
                                                   :id="data.user_account.id"
                                                   aria-expanded="false"
                                                   class="card-collapse collapsed"
                                                   data-open="true"
                                                   data-toggle="collapse"
                                                   role="button">
                                                    <i :id="data.user_account.id" class="fas fa-chevron-down" data-open="true"
                                                       style="display: block; height: 100%; width: 100%;"></i>
                                                </a>
                                            </div>
                                            <div class="col-md-1">{{data.user_account.id}}</div>
                                            <div class="col-md-3">
                                                <div class="m-card-user m-card-user--sm" title="Click for Detail">
                                                    <div class="m-card-user__pic">
                                                        <div class="m-card-user__no-photo m--bg-fill-warning">
                                                            <span>
                                                                <img :src="['/storage/uploads/companylogos/' +  data.user_account.company_logo ]" @error="imageUrlAlt" class="img-responsive">
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="m-card-user__details">
                                                        <span class="m-card-user__name" title="Click for Detail">
                                                            {{ data.user_account.name }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Email: </strong>{{ data.user_account.email }}<br/>
                                                <strong>Contact Number: </strong>{{ data.user_account.contact_number }}
                                            </div>
                                            <div class="col-md-2">
                                                Email Verified:
                                                <i v-if="data.user_account.account_verified_at" class="fa fa-check" style="color: green"></i>
                                                <i v-else class="fa fa-times" style="color: red"></i>
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="User Account Connected" v-if="data.user_account.status == 1">
                                                    <i class="fas fa-check-circle"></i>
                                                    Connected
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="User Account Disconnected" v-else>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Disconnected
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="PMS Completed" v-if="data.user_account.integration_completed_on">
                                                    <i class="fas fa-check-circle"></i>
                                                    Completed
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="PMS Not Completed" v-else>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Not Completed
                                                </span>
                                            </div>
                                            <div class="col-md-1">
                                                <span class="dropdown">
                                                    <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true"><i class="la la-ellipsis-h"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" :href="'/admin/bookings/' + data.user_account.id"><i class="m-menu__link-icon flaticon-calendar"></i> Bookings </a>
                                                        <a class="dropdown-item" :href="'/admin/users/' + data.user_account.id"><i class="la la-user"></i> Users </a>
                                                        <a class="dropdown-item" :href="'/admin/properties/' + data.user_account.id"><i class="m-menu__link-icon la la-home"></i> Properties </a>
                                                        <a class="dropdown-item" href="javaScript:void(0)" @click="checkVerification(data.user_account.id)"><i class="la la-user"></i> Verification On PMS </a>
                                                        <a v-if="user_role == 'SuperAdmin' && user_type == 4" class="dropdown-item" :href="'/admin/admin-view-client-dashboard/' + data.user_account.id"><i class="la la-dashboard"></i> CA Account Login </a>
                                                    </div>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div :id="'id_'+data.user_account.id" class="panel-collapse collapse">
                                        <div class="panel-body open-div">
                                            <div class="row open-div-row">
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Team Members</strong>
                                                    <br/>{{data.user_account.total_team_members}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Properties</strong><br/>
                                                    <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected">
                                                        <i class="fas fa-check-circle"></i>
                                                        Connected:- {{data.user_account.connected_properties}}
                                                    </span>
                                                        <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Disconnected:- {{data.user_account.disconnected_properties}}
                                                    </span>
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Amounts</strong>
                                                    <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Connected">
                                                        <i class="fas fa-check-circle"></i>
                                                        Successful:- ${{data.successful}}
                                                    </span>
                                                    <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Failed:- ${{ data.failed }}
                                                    </span>
                                                    <span class="badge badge-warning status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="Property Disconnected">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Scheduled:- ${{ data.scheduled }}
                                                    </span>
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Current PMS</strong>
                                                    <br/>{{ data.user_account.current_pms }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Last Booking Sync</strong>
                                                    <br/>{{ data.user_account.last_booking_sync | formatDateTime }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Created Date</strong>
                                                    <br/>{{ data.user_account.created_at | formatDateTime }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default" v-if="paginationResponse.total == 0">
                                    <div class="panel-heading">
                                        <div class="panel-title">No user account found.</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--end: table -->
                    <pagination :data="paginationResponse" :limit="1" @pagination-change-page="getUserAccounts" align="right"></pagination>
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
                user_role: '',
                user_type: '',
                user_accounts: {},
                paginationResponse: {},
                action: '',
                selectedSortOrder: 'selected sort-asc',
                file_name: 'User-Account-List',
                cities: {},
                filter_count: 0,
                filters: {
                    per_page: 10,
                    search: '',
                    sortOrder: 'ASC',
                    sortColumn: 'name',
                },
            }
        },
        components: {
        },
        mounted() {
            this.filters.user_account_id = this.user_account_id;
            this.getUserAccounts(); // Fetch initial results
            // this.getAllPropertiesCities();
            // this.getAllUsers();

        },
        methods: {
            checkVerification(id) {
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
                            url : "/admin/verify-api-key",
                            method : 'POST',
                            data: {id:id},
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
                this.getUserAccounts();
            },

            /**
             * Getting Properties List Using Pagination
             */
            getUserAccounts(page = 1) {
                this.block = true;
                var self = this;
                self.msg = "Loading User Accounts";

                axios.post('/admin/get-user-accounts?page=' + page, {'filters': self.filters})
                    .then(function (response) {
                        let count = 0;
                        if (self.filters.search != '') {
                            count++;
                        }
                        if (self.filters.per_page != '10') {
                            count++;
                        }

                        self.filter_count = count;
                        self.paginationResponse = response.data.meta;
                        self.user_accounts = response.data.data;
                        self.user_role = response.data.user_role[0];
                        self.user_type = response.data.user_type;
                        self.block = false;
                        self.msg = "Please Wait";
                    })
                    .catch(function (error) {
                        toastr.error("Some error while fetching user accounts.");
                    });
            },

            resetFilters() {
                this.filters.per_page = 10;
                this.filters.search = '';
                this.filters.city = 'all';
                this.filters.user_account_id = 0;
                this.filters.sortOrder = 'ASC';
                this.filters.sortColumn = 'name';
                this.getUserAccounts();
            },

            imageUrlAlt(event) {
                event.target.src = "/storage/uploads/companylogos/no_image.png";
            }

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

    .filter-row {
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
