<template>
    <div>
        <!-- User List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-users"></i> Users
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">User List </span>
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
                                    <select @change="getUsers()" class="custom-select custom-select-sm mb-2 mr-1"
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
                                    <input @keyup="getUsers" class="form-control form-control-sm" id="filter-search"
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
                                <div class="col-md-2"><strong>ID</strong></div>
                                <div class="col-md-2"><strong>User Account ID</strong></div>
                                <div class="col-md-2">
                                    <a :class="selectedSortOrder" @click.prevent="selectedSortOrderChanged()" href="#0">
                                        <strong><span>Name</span></strong>
                                    </a>
                                </div>
                                <div class="col-md-2"><strong>Contact</strong></div>
                                <div class="col-md-2"><strong>Status</strong></div>
                                <div class="col-md-2"><strong>Created Date</strong></div>
                            </div>

                            <div class="panel-group">
                                <div class="panel panel-default" v-for="(user, index) in users">
                                    <div class="panel-heading">
                                        <div class="row" :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <div class="col-md-2">{{user.id}}</div>
                                            <div class="col-md-2">{{user.user_account_id}}</div>
                                            <div class="col-md-2">
                                                <div class="m-card-user m-card-user--sm" title="Click for Detail">
                                                    <div class="m-card-user__pic">
                                                        <div class="m-card-user__no-photo m--bg-fill-warning">
                                                            <span>
                                                                <img :src="['/storage/uploads/companylogos/' +  user.user_image ]" @error="imageUrlAlt" class="img-responsive">
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="m-card-user__details">
                                                        <span class="m-card-user__name" title="Click for Detail">
                                                            {{ user.name }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>Email: </strong>{{ user.email }}<br/>
                                                <strong>Phone: </strong>{{ user.phone }}
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge badge-success status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="User Connected" v-if="user.status == 1">
                                                    <i class="fas fa-check-circle"></i>
                                                    Active
                                                </span>
                                                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip" title="User Disconnected" v-else>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Deactive
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                {{user.created_at | formatDateTime}}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default" v-if="paginationResponse.total == 0">
                                    <div class="panel-heading">
                                        <div class="panel-title">No user found.</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--end: table -->
                    <pagination :data="paginationResponse" :limit="1" @pagination-change-page="getUsers" align="right"></pagination>
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
                users: {},
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
            this.getUsers(); // Fetch initial results

        },
        methods: {
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
                this.getUsers();
            },

            /**
             * Getting Properties List Using Pagination
             */
            getUsers(page = 1) {
                this.block = true;
                var self = this;
                self.msg = "Loading User Accounts";

                axios.post('/admin/get-users?page=' + page, {'user_account_id':self.user_account_id, 'filters': self.filters})
                    .then(function (response) {
                        let count = 0;
                        if (self.filters.search != '') {
                            count++;
                        }
                        if (self.filters.per_page != '10') {
                            count++;
                        }
                        console.error(response.data.data)
                        self.filter_count = count;
                        self.paginationResponse = response.data.data;
                        self.users = self.paginationResponse.data;
                        console.error(self.users);
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
                this.getUsers();
            },

            imageUrlAlt(event) {
                event.target.src = "/storage/uploads/user_images/no_image.png";
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
