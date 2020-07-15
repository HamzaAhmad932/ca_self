<template>
    <div>
        <!----Modals Components----------->
        <!-------End Modal Components------>

        <!-- Booking List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-calendar"></i> Bookings
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Bookings List </span>
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
                                    <select @change="applyFilter(null, true)" class="custom-select custom-select-sm"
                                            id="inlineFormInputName3" ref="recordsPerPage"
                                            v-model="filter.recordsPerPage">
                                        <option selected value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 offset-md-3">
                                    <label for="filter-search">Search</label>
                                    <input @keyup="applyFilterForSearch()" class="form-control form-control-sm"
                                           id="filter-search" placeholder="Start typing …" type="text"
                                           v-model="filter.search.searchStr"/>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="check_in_date">Check-in Date</label>
                                    <div aria-label="Basic example" class="btn-group" id="check_in_date" role="group">
                                        <button :class="activate == 'all' ? 'active' : ''" @click="checkin_filter($event)"
                                                class="btn btn-primary btn-sm" data-checkin="all" type="button">All
                                        </button>
                                        <button :class="activate == 'today' ? 'active' : ''" @click="checkin_filter($event)"
                                                class="btn btn-primary btn-sm" data-checkin="today" type="button">Today
                                        </button>

                                        <div class="datepicker-trigger">
                                            <button :class="activate == 'custom' ? 'active' : ''" class="btn btn-primary btn-sm"
                                                    id="datepicker-trigger" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;"
                                                    type="button">
                                                {{formatDates(filter.dateOne, filter.dateTwo) == '' ? 'Select date' :
                                                formatDates(filter.dateOne, filter.dateTwo)}}
                                            </button>
                                            <AirbnbStyleDatepicker
                                                    :date-one="filter.dateOne"
                                                    :date-two="filter.dateTwo"
                                                    :fullscreen-mobile="true"
                                                    :mode="'range'"
                                                    :trigger-element-id="'datepicker-trigger'"
                                                    @closed="daterangeFilter()"
                                                    @date-one-selected="val => { filter.dateOne = val }"
                                                    @date-two-selected="val => { filter.dateTwo = val }"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>&nbsp;</label>
                                    <a @click.prevent="resetFilters()" class="btn btn-sm btn-block btn-danger"
                                       href="javaScript:void(0)">Reset</a>
                                </div>
                            </div>
                            <div class="row heading-row">
                                <div class="col-md-1"><strong>More</strong></div>
                                <div class="col-md-2"><strong>Booking ID</strong></div>
                                <div class="col-md-3"><strong>User</strong></div>
                                <div class="col-md-3"><strong>Property</strong></div>
                                <div class="col-md-1"><strong>Amount</strong></div>
                                <div class="col-md-1"><strong>Status</strong></div>
                                <div class="col-md-1"><strong>Action</strong></div>
                            </div>
                            <admin-booking-list :booking_list="booking_list.data"></admin-booking-list>
                        </div>
                    </div>
                    <!--end: table -->
                    <pagination :data="booking_list" :limit="1" @pagination-change-page="fetchAdminBookingList"
                                align="right"></pagination>
                </div>
            </div>
        </div>
        <!-- End Booking List-->

        <!-- Block UI Loader-->
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
        <!-- End Block UI-->

    </div>
</template>

<script>
    import AdminBookingList from "./AdminBookingList";
    import format from 'date-fns/format';
    import {mapState} from 'vuex';

    var months = {
        'jan': '01', 'feb': '02', 'mar': '03', 'apr': '04',
        'may': '05', 'jun': '06', 'jul': '07', 'aug': '08',
        'sep': '09', 'oct': '10', 'nov': '11', 'dec': '12'
    };
    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    export default {
        props: ['user_account_id'],
        components: {
            'AdminBookingList': AdminBookingList
        },
        created() {
            this.$store.dispatch('fetchAdminBookingList', {filter: this.filter});
        },
        data() {
            return {
                booking_info_id: 0,
                refund_amount: {
                    booking_info_id: '',
                    amount: ''
                },
                activate: 'all',
                filter_count: 0,
                dateFormat: 'D MMM',
                filter: {
                    apply_filter: false,
                    query: '',
                    is_custom_date: false,
                    date: '',
                    p_status: '',
                    v_status: '',
                    dateOne: '',
                    dateTwo: '',
                    recordsPerPage: 10,
                    page: 1,
                    columns: ["*"],
                    relations: [
                        "transaction_init_charged",
                        "credit_card_authorization_sd_cc",
                        "credit_card_authorization_sd_cc.ccinfo",
                        "guest_images",
                        "cc_Infos",
                        // "room_info",
                        "transaction_init_charged.transactions_detail",
                        "user",
                        "user_account"
                    ],
                    sort: {
                        sortOrder: "DESC",
                        sortColumn: "id",
                    },
                    user_account_id: 0,
                    constraints: [],
                    search: {
                        searchInColumn: ["pms_booking_id", "guest_name", "guest_last_name"],
                        searchStr: ""
                    },
                },
                sortClass: 'selected sort-asc',
                supported_export_formats: ['csv', 'pdf', 'xls'],
                export_format: -1,
                file_name: 'Booking-List',
            }
        },
        methods: {
            applyFilter(sort = null, recordsPerPageTrigger = false) {
                if (recordsPerPageTrigger) {
                    this.filter.page = 1;
                }
                this.$store.dispatch('fetchAdminBookingList', {filter: this.filter});
            },
            sortBy(col) {
                this.setPaginationPageNumberRecordsPerPage(true);
                this.filter.sort.sortColumn = col;
                if (this.sortClass == 'selected sort-desc') {
                    this.sortClass = 'selected sort-asc';
                    this.filter.sort.sortOrder = 'asc';
                } else {
                    this.sortClass = 'selected sort-desc';
                    this.filter.sort.sortOrder = 'desc';
                }
                this.applyFilter(true);
            },
            formatDates(dateOne, dateTwo) {
                let formattedDates = '';
                if (dateOne) {
                    formattedDates = format(dateOne, this.dateFormat)
                }
                if (dateTwo) {
                    formattedDates += ' - ' + format(dateTwo, this.dateFormat);
                }
                if (formattedDates != '') {
                    this.filter.is_custom_date = true;
                    this.filter.date = '';
                    this.activate = 'custom';
                } else {
                    this.filter.is_custom_date = false;
                }
                return formattedDates
            },
            checkin_filter(e) {
                let s = e.target.dataset.checkin;
                this.filter.dateOne = '';
                this.filter.dateTwo = '';
                this.filter.is_custom_date = false;
                this.filter.date = s;
                this.activate = s;
                this.setPaginationPageNumberRecordsPerPage(true);
                this.applyFilter();
            },
            resetFilters() {
                Object.assign(this.$data.filter, this.$options.data().filter);
                this.activate = 'all';
                this.$store.dispatch('fetchAdminBookingList', {filter: this.filter});
            },
            fetchAdminBookingList(page) {
                this.filter.page = page;
                //console.log(page);
                //console.log(this.filter);
                this.$store.dispatch('fetchAdminBookingList', {filter: this.filter});
            },
            getAdminBookingDetail(e) {
                let self = this;
                let id = e.target.id;
                let door = e.target.dataset.open;
                if (door == 'true') {

                    this.$store.dispatch('fetchAdminBookingDetail', id);
                    e.target.dataset.open = 'false';
                } else {
                    e.target.dataset.open = 'true';
                }
            },
            daterangeFilter() {
                if (this.filter.dateOne != '' && this.filter.dateTwo != '') {
                    this.setPaginationPageNumberRecordsPerPage(true);
                    this.applyFilter();
                }
            },


            wait(time) {
                return new Promise(resolve => {
                    setTimeout(() => {
                        resolve();
                    }, time);
                });
            },

            /**
             * @param resetPageNumber
             */
            setPaginationPageNumberRecordsPerPage(resetPageNumber = false) {
                if (((this.filter.dateOne.length > 0) && (this.filter.dateTwo.length > 0)) || ((this.filter.date.length > 0) && (this.filter.date.length == 'today'))) {
                    this.filter.recordsPerPage = 25;
                    this.filter.page = 1;
                } else {
                    if ((this.$refs.recordsPerPage.value != null) && (this.$refs.recordsPerPage.value != ''))
                        this.filter.recordsPerPage = this.$refs.recordsPerPage.value;
                    else
                        this.filter.recordsPerPage = 10;
                }

                if (resetPageNumber)
                    this.filter.page = 1;
            },

            applyFilterForSearch() {
                this.setPaginationPageNumberRecordsPerPage(true);
                this.applyFilter();
            },

            validBookingToShowByCheckingFilter(bookingInfo) {
                if ((this.filter.dateOne.length > 0) && (this.filter.dateTwo.length > 0)) {
                    return this.isCheckInDateValid(this.getCheckInDateFromBookingInfo(bookingInfo), new Date(this.filter.dateOne + 'T00:00:00'), new Date(this.filter.dateTwo + 'T00:00:00'));
                } else if ((this.filter.date.length > 0) && (this.filter.date == 'today')) {
                    let today = new Date();

                    //add 1 to months as javascript returns month no.s from 0-11
                    let startDate = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
                    startDate = new Date(startDate + 'T00:00:00');
                    return this.isCheckInDateValid(this.getCheckInDateFromBookingInfo(bookingInfo), startDate);
                } else {
                    return true;
                }
            },
            /**
             *
             * @param bookingCheckInDate
             * @param startDate
             * @param endDate
             * @returns {boolean}
             */
            isCheckInDateValid(bookingCheckInDate, startDate = null, endDate = null) {
                //console.log("Start Date = "+startDate);
                //console.log("End Date = "+endDate);
                //console.log("Booking Date = "+bookingCheckInDate);
                if ((startDate != null) && (endDate != null)) {
                    if ((bookingCheckInDate >= startDate) && (bookingCheckInDate <= endDate))
                        return true;
                    else
                        return false;
                } else if ((startDate != null) && (endDate == null)) {
                    //today filter case
                    if ((bookingCheckInDate >= startDate))
                        return true;
                    else
                        return false;
                } else {
                    return true;
                }
            },
            /**
             *
             * @param bookingInfo
             * @returns {Date}
             */
            getCheckInDateFromBookingInfo(bookingInfo) {

                //if day number is single digit then put 0 as prefix
                var day_number = bookingInfo.check_in.day;
                if (typeof day_number == 'number' && day_number < 10)
                    day_number = '0' + day_number;
                else if (typeof day_number == 'string' && day_number.length < 2)
                    day_number = '0' + day_number;

                let dateString = bookingInfo.check_in.year + '-' + months[bookingInfo.check_in.month.toLowerCase()] + '-' + day_number;
                return new Date(dateString + 'T00:00:00');
            },

            /**
             * @returns {string}
             */
            setFileNameWithAppliedConstraintsString(exportExtension) {
                let constraintStr = '';
                if ((this.filter.dateOne.length > 0) && (this.filter.dateTwo.length > 0)) {
                    constraintStr += '(CheckIn -' + new Date(this.filter.dateOne).toDateString() + ' to ' + new Date(this.filter.dateTwo).toDateString();
                } else if ((this.filter.date.length > 0) && (this.filter.date == 'today')) {
                    constraintStr += '(CheckIn ' + new Date().toDateString();//startDate;
                }

                if (this.filter.search.searchStr.length > 0) {
                    constraintStr += (constraintStr.length == 0 ? '(' : '') + ' --  Search - ' + this.filter.search.searchStr;
                }
                let today = new Date();
                let file_name = 'Booking-List-' + today.getDate() + '-' + monthNames[today.getMonth()] + '-' + today.getFullYear();

                this.file_name = (file_name + ' ' + constraintStr + (constraintStr.length > 0 ? ')' : '') + '.' + exportExtension).toString();
                return true;
            }
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.replace(/([A-Z])/g, ' $1').trim();
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1)
            },
            shortName: function (value) {
                if (!value) {
                    return '';
                } else {
                    //return value.length;
                    if (value.length > 70) {
                        return value.substring(0, 70) + '...';
                    } else {
                        return value
                    }

                }
            },
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                booking_list: (state) => {
                    return state.admin_booking.booking_list;
                },
            })
        },
        mounted() {
            this.filter.user_account_id = this.user_account_id;
        },
        watch: {
            filter: {
                deep: true,
                immediate: true,
                handler(nv, ov) {
                    //console.log({'old': ov, 'new': nv});

                    let f = this.$options.data().filter;
                    let count = 0;
                    if (f.date != nv.date && nv.date != 'all') {
                        count++;
                    }
                    if (f.p_status != nv.p_status) {
                        count++;
                    }
                    if (f.search.searchStr != nv.search.searchStr) {
                        count++;
                    }
                    if (f.v_status != nv.v_status) {
                        count++;
                    }
                    if (f.dateOne != nv.dateOne && f.dateTwo != nv.dateTwo) {
                        count++;
                    }
                    this.filter_count = count;
                }
            }
        },

    }
</script>

<style>
    [v-cloak] {
        display: none;
    }

    table.desc_bg {
        background: #efefef;

    }

    .detail_cover {
        overflow: hidden;
        padding: 10px;
        margin: 15px;
        height: auto;;
        width: auto;
        background: #ffffff;
    }

    .pointer {
        width: 212px;
        height: 105px;
        position: relative;
        background: #efefef;
        margin-top: 5px;
        margin-bottom: 5px;
        margin-left: 16px;
        padding: 5px;
        padding-left: 25px;
    }

    .pointer:after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 0;
        height: 105px;
        border-left: 16px solid white;
        border-top: 50px solid transparent;
        border-bottom: 50px solid transparent;
    }

    .pointer:before {
        content: "";
        position: absolute;
        right: -16px;
        bottom: 0;
        width: 0;
        height: 105px;
        border-left: 16px solid #efefef;
        border-top: 50px solid transparent;
        border-bottom: 50px solid transparent;
    }

    .m-nav .m-nav__item > .m-nav__link .m-nav__link-icon {
        width: auto;
    }

/*  Filter and heading row  */
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
</style>
