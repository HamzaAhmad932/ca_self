<template>
    <div>
        <booking-sync-time-popup-modal ></booking-sync-time-popup-modal>
        <div class="page-content" id="booking-listing-page">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-header has-border-bottom mb-0">
                            <h1 class="page-title d-inline">
                                <span>Bookings</span>
                                <button aria-controls="filter-collapse" aria-expanded="false" data-toggle="collapse"
                                        class="btn btn-sm btn-theme m-0 filter-btn-sm-width sm-flex-auto d-inline-block d-md-none"
                                        href="#filter-collapse" role="button">
                                    <i class="fas fa-filter"> </i>
                                    <span class="d-none d-xs-inline d-sm-inline"> Filter</span>
                                    <div class="filter-badge" v-if="filter_count > 0">{{filter_count}}</div>
                                </button>
                                <button  v-if="can_sync_booking" class="btn btn-sm btn-success sync-booking-btn-sm-width md-0 d-inline-block d-md-none" id="sync_properties_btn0" data-target="#booking-sync-time-popup-modal"
                                        data-toggle="modal">
                                    <i class="fa fa-sync"></i> Sync Bookings
                                </button>
                            </h1>
                            <button aria-controls="filter-collapse" aria-expanded="false" data-toggle="collapse"
                                    class="btn btn-sm btn-theme mb-2 ml-2 filter-btn-margin sm-flex-auto d-none d-md-inline-block"
                                    href="#filter-collapse" role="button">
                                <i class="fas fa-filter"> </i>
                                <span class="d-none d-xs-inline d-sm-inline"> Filter</span>
                                <div class="filter-badge" v-if="filter_count > 0">{{filter_count}}</div>
                            </button>
                            <div class="booking-filter-stack">
                                <div class="d-flex sm-flex-auto">
                                    <select @change="applyFilter(null, true)" class="custom-select custom-select-sm mb-2 mr-1"
                                            id="inlineFormInputName3" ref="recordsPerPage"
                                            v-model="filter.recordsPerPage">
                                        <option selected value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="d-flex sm-flex-auto">
                                    <select @change="exportBookings()" class="custom-select custom-select-sm mb-2 mr-1"
                                            id="inlineFormInputName2" style="min-width:10rem;"
                                            v-model="export_format">
                                        <option value="-1">Select To Export</option>
                                        <option value="pdf">PDF</option>
                                        <option value="csv">CSV</option>
                                        <option value="xls">XLS</option>
                                    </select>
                                </div>
                                <button  v-if="can_sync_booking" class="btn btn-sm btn-success mb-2 ml-1 sm-flex-auto d-none d-md-block" id="sync_properties_btn" data-target="#booking-sync-time-popup-modal"
                                        data-toggle="modal">
                                    <i class="fa fa-sync"></i> Sync Bookings
                                </button>
                            </div>
                        </div>
                        <div class="page-body">
                            <div class="content-box">
                                <div class="collapse" id="filter-collapse"><!--show-->
                                    <div :class="'filter-form'+(can_sync_booking? '' : ' sync-booking')">
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-3 col-sm-6">
                                                <label for="filter-search">Search</label>
                                                <input @keyup="applyFilterForSearch()"
                                                       class="form-control form-control-sm" id="filter-search"
                                                       placeholder="Start typing …" type="text"
                                                       v-model="filter.search.searchStr"/>
                                            </div>
                                            <div class="form-group col-md-2 col-sm-6">
                                                <label for="filter-pms-property">Property</label>
                                                <select @change="fetchBookingList()" class="custom-select custom-select-sm"
                                                        id="filter-pms-property" v-model="filter.property_id">
                                                    <option value="">All Properties</option>
                                                    <option :value="property.pms_property_id"
                                                            v-for="property in properties">
                                                        {{property.pms_property_id}} -- {{property.name}}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 col-sm-9">
                                                <label>Check-in Date</label>
                                                <div aria-label="Basic example" class="btn-group" role="group">
                                                    <button :class="activate == 'all' ? 'active' : ''"
                                                            @click="checkin_filter($event)"
                                                            class="btn btn-outline-secondary btn-sm" data-checkin="all"
                                                            type="button">All
                                                    </button>
                                                    <button :class="activate == 'today' ? 'active' : ''"
                                                            @click="checkin_filter($event)"
                                                            class="btn btn-outline-secondary btn-sm" data-checkin="today"
                                                            type="button">Today
                                                    </button>

                                                    <div class="datepicker-trigger" style="z-index:887">
                                                        <button :class="activate == 'custom' ? 'active' : ''"
                                                                class="btn btn-outline-secondary btn-sm"
                                                                id="datepicker-trigger"
                                                                type="button">
                                                            {{formatDates(filter.dateOne, filter.dateTwo) == '' ||
                                                            activate != 'custom' ? 'Select date' :
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

                                            <div class="form-group col-md-3 col-sm-3">
                                                <a @click.prevent="resetFilters()"
                                                   class="btn btn-sm btn-block btn-outline-danger float-right" href="#" id="reset-btn">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Mobile Sorting Options (hidden on desktop)-->
                                <div class="booking-table-filter-mobile d-block d-lg-none">
                                    <div class="form-group">
                                        <select class="custom-select custom-select-sm">
                                            <option selected>Sort by:</option>
                                            <option value="pms_booking_id">ID</option>
                                            <option value="guest_name">Guest Name</option>
                                            <option value="check_in_date">Check-In Date</option>
                                            <option value="check_out_date">Check-Out Date</option>
                                            <option value="total_amount">Amount</option>
                                            <!--                                            <option>Deposit</option>-->
                                            <!--                                            <option>Payment Status</option>-->
                                        </select>
                                    </div>
                                </div>
                                <!-- Desktop Sorting Options (hidden on mobile)-->
                                <div class="table-header d-none d-lg-block">
                                    <div class="row no-gutters">
                                        <div class="col-1 col-lg">
                                            <div class="table-box-check d-flex">
                                                <a :class="filter.sort.sortColumn == 'pms_booking_id' ? sortClass : ''" @click.prevent="sortBy('pms_booking_id')"
                                                   href="#">
                                                    <span>ID</span></a>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <a :class="filter.sort.sortColumn == 'guest_name' ? sortClass : ''" @click.prevent="sortBy('guest_name')"
                                               href="#">
                                                <span>Guest</span>
                                            </a>
                                        </div>
                                        <div class="col-3">
                                            <div class="booking-box-dates">
                                                <a :class="filter.sort.sortColumn == 'check_in_date' ? sortClass : ''" @click.prevent="sortBy('check_in_date')"
                                                   class="booking-box-checkin"
                                                   href="#">
                                                    <span>Check-In </span>
                                                </a>
                                                <div class="booking-box-duration"> →</div>
                                                <a :class="filter.sort.sortColumn == 'check_out_date' ? sortClass : ''" @click.prevent="sortBy('check_out_date')"
                                                   class="booking-box-checkout"
                                                   href="#">
                                                    <span>Check-Out</span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-2 col-lg">
                                            <a href="#">
                                                <span>Security Deposit</span>
                                            </a>
                                        </div>
                                        <div class="col-2 col-lg">
                                            <a :class="filter.sort.sortColumn == 'total_amount' ? sortClass : ''" @click.prevent="sortBy('total_amount')"
                                               href="#">
                                                <span>Amount</span>
                                            </a>
                                        </div>
                                        <div class="col-2 col-lg">
                                            <a href="#">
                                                <span>Payment Status</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <booking-list
                                        :booking_list="booking_list.data"
                                        :is_booking_list_page="true"
                                        :redirect_to_record="filter.redirect_to_record"
                                        :redirect_which_record="filter.redirect_which_record"
                                ></booking-list>

                                <pagination :data="booking_list" :limit="1" @pagination-change-page="fetchBookingList"
                                            align="right"></pagination>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <!-- Block UI Loader-->
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
        <!-- End Block UI-->

        <!-- Downloading CSV/XLS component-->
        <xls-export :file_name="file_name" :json_data="booking_list.data" ref="xlsExport"></xls-export>
        <csv-export :file_name="file_name" :json_data="booking_list.data" ref="csvExport"></csv-export>
        <!-- End Downloading CSV/XLS component-->
    </div>
</template>

<script>

    import BookingList from "./BookingList";
    import format from 'date-fns/format';
    import {mapActions, mapState} from 'vuex';
    import pdfMake from "pdfmake/build/pdfmake";
    import pdfFonts from "pdfmake/build/vfs_fonts";
    import CsvExport from "../export/CsvExport";
    import XlsExport from "../export/XlsExport";
    import CustomPopover from "../../../../general/client/reusables/CustomPopover";

    pdfMake.vfs = pdfFonts.pdfMake.vfs;

    var months = {
        'jan': '01', 'feb': '02', 'mar': '03', 'apr': '04',
        'may': '05', 'jun': '06', 'jul': '07', 'aug': '08',
        'sep': '09', 'oct': '10', 'nov': '11', 'dec': '12'
    };
    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var now_date = moment(new Date()).format("YYYY-MM-DD");
    export default {
        components: {
            'csvExport': CsvExport,
            'xlsExport': XlsExport,
            'BookingList': BookingList,
            CustomPopover
        },
        created() {
            let self = this;
            let page_url = new URL(window.location.href);
            if (page_url.searchParams.get("booking-id")) {
                self.filter.redirect_to_record = true;
                self.filter.redirect_which_record = page_url.searchParams.get("booking-id");
            }
            this.$store.dispatch('ba/fetchBookingList', {filter: self.filter});

            //now make redirect to null so it will not happen for other requests
            setTimeout(function () {
                self.filter.redirect_to_record = false;
                self.filter.redirect_which_record = null;
            }, 2000);
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
                    p_status: '',
                    v_status: '',
                    date: '',
                    dateOne: '',
                    dateTwo: '',
                    // date_timeZone: moment.tz.guess(true),
                    recordsPerPage: 10,
                    page: 1,
                    redirect_to_record: false,
                    redirect_which_record: null,
                    columns: ["*"],
                    relations: [
                        "transaction_init_charged",
                        "credit_card_authorization_sd_cc",
                        "credit_card_authorization_sd_cc.ccinfo",
                        "guest_images",
                        "cc_Infos",
                        //"room_info",
                        "transaction_init_charged.transactions_detail",
                        'upsellOrders',
                        'upsellOrders.upsellOrderDetails',
                        'upsellOrders.upsellOrderDetails.upsell',
                        'upsellOrders.upsellOrderDetails.upsell.upsellType',
                    ],
                    sort: {
                        sortOrder: "DESC",
                        sortColumn: "id",
                    },
                    constraints: [],
                    search: {
                        searchInColumn: ["pms_booking_id", "guest_name", "guest_last_name", "CONCAT(guest_name, ' ', guest_last_name)"],
                        searchStr: ""
                    },
                    property_id: '',
                },
                sortClass: 'selected sort-asc',
                supported_export_formats: ['csv', 'pdf', 'xls'],
                export_format: -1,
                file_name: 'Booking-List',
                properties: [],
            }
        },
        methods: {
            fetchBookings() {
                // if (this.isPropertySelected())
                this.$store.dispatch('ba/fetchBookingList', {filter: this.filter});
            },
            applyFilter(sort = null, recordsPerPageTrigger = false) {
                if (recordsPerPageTrigger) {
                    this.filter.page = 1;
                }
                this.fetchBookings()
            },
            sortBy(col) {
                //this.setPaginationPageNumberRecordsPerPage(true);
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
                    if (dateOne === dateTwo && dateOne == now_date) {
                        this.filter.date = 'today';
                        this.activate = 'today';
                    } else {
                        this.filter.is_custom_date = true;
                        this.filter.date = '';
                        this.activate = 'custom';
                    }
                } else {
                    this.filter.is_custom_date = false;
                }
                return formattedDates
            },
            checkin_filter(e) {
                let s = e.target.dataset.checkin;
                if (s == "all") {
                    this.resetCustomDateFilter();
                } else {
                    this.filter.dateOne = now_date;
                    this.filter.dateTwo = now_date;
                    this.filter.is_custom_date = true;
                }
                this.date = s;
                this.activate = s;
                this.setPaginationPageNumberRecordsPerPage(true);
                this.applyFilter();
                // setTimeout(this.resetCustomDateFilter,500);

            },
            resetCustomDateFilter: function () {
                this.filter.is_custom_date = false;
                this.filter.dateOne = '';
                this.filter.dateTwo = '';
            },
            resetFilters() {
                Object.assign(this.$data.filter, this.$options.data().filter);
                this.activate = 'all';
                this.fetchBookings()
            },
            fetchBookingList(page) {
                this.filter.page = page;
                //console.log(page);
                //console.log(this.filter);
                this.fetchBookings()
            },
            getBookingDetail(e) {
                let self = this;
                let id = e.target.id;
                let door = e.target.dataset.open;
                if (door == 'true') {

                    this.$store.dispatch('ba/fetchBookingDetail', id);
                    e.target.dataset.open = 'false';
                } else {
                    e.target.dataset.open = 'true';
                }
            },
            applyPayment(id) {
                this.$store.dispatch('applyPayment', id);
            },
            validateAdditionalCharge() {
                let flag = false;
                if (this.additional_charge.booking_info_id == '') {
                    this.hasError.booking_info_id = true;
                    this.errorMessage.booking_info_id = 'Booking No. is missing.';
                    flag = true;
                }
                if (this.additional_charge.amount == '') {
                    this.hasError.amount = true;
                    this.errorMessage.amount = 'Amount is missing.';
                    flag = true;
                }
                return flag;
            },
            refundAmount() {

            },
            daterangeFilter() {
                if (this.filter.dateOne != '' && this.filter.dateTwo != '') {
                    this.setPaginationPageNumberRecordsPerPage(true);
                    this.applyFilter();
                }
            },
            makeBookingIdReactiveForCreditCard(booking_id) {
                this.$store.dispatch('general/guestCreditCardActiveID', booking_id);
            },
            makeBookingIdReactiveForUploadID(booking_id) {
                this.$store.dispatch('guestUploadActiveID', booking_id);
            },
            makeBookingIdReactiveForCommunication(booking_id, pms_booking_id, is_cancelled, check_out_date) {
                let payload = {
                    booking_id,
                    pms_booking_id,
                    is_cancelled,
                    check_out_date
                };
                this.$store.dispatch('booking_id_action_chat', payload);
            },


            wait(time) {
                return new Promise(resolve => {
                    setTimeout(() => {
                        resolve();
                    }, time);
                });
            },

            async exportBookings() {
                if (this.supported_export_formats.includes(this.export_format)) {
                    this.$store.commit('SHOW_LOADER', null, {root: true});
                    if (this.export_format === 'csv') {
                        let assign = this.setFileNameWithAppliedConstraintsString('csv');
                        if (assign)
                            await this.wait(5000);
                        this.$refs.csvExport.$refs.csvDownload.click();
                    } else if (this.export_format === 'xls') {
                        let assign = this.setFileNameWithAppliedConstraintsString('xls');
                        if (assign)
                            await this.wait(5000);
                        this.$refs.xlsExport.$refs.xlsDownload.click();
                    } else if (this.export_format === 'pdf') {
                        let assign = this.setFileNameWithAppliedConstraintsString('pdf');
                        if (assign)
                            await this.wait(5000);
                        this.generatePDF();
                    }
                    //reset the export dropdown
                    this.export_format = -1;
                    this.file_name = 'Booking-List';
                } else {
                    toastr.error('Unsupported export format.');
                }
                this.$store.commit('HIDE_LOADER', null, {root: true});
            },
            generatePDF() {
                let bodyHeaders = [
                    // { text: 'Sr No.', style: 'tableHeader' },
                    {text: 'Booking No.', style: 'tableHeader'},
                    {text: 'Property No.', style: 'tableHeader'},
                    {text: 'Guest Name', style: 'tableHeader'},
                    {text: 'Guest Email', style: 'tableHeader'},
                    {text: 'Check-in', style: 'tableHeader'},
                    {text: 'Check-out', style: 'tableHeader'},
                    {text: 'Security Deposit', style: 'tableHeader'},
                    {text: 'Deposit Status', style: 'tableHeader'},
                    {text: 'Booking Amount', style: 'tableHeader'},
                    {text: 'Payment Status', style: 'tableHeader'},
                ];

                let bodyRows = [];
                bodyRows.push(bodyHeaders);
                $.each(this.booking_list.data, function (key, value) {
                    bodyRows.push([
                        // {text: parseInt(key) + 1, style: 'tdStyle' },
                        {text: value['pms_booking_id'], style: 'tdStyle'},
                        {text: value['property']['pms_property_id'], style: 'tdStyle'},
                        {text: value['guest_name'], style: 'tdStyle'},
                        {text: value['guest_email'], style: 'tdStyle'},
                        {text: value['check_in_pdf'], style: 'tdStyle'},
                        {text: value['check_out_pdf'], style: 'tdStyle'},
                        {text: value['deposit'], style: 'tdStyle'},
                        {text: value['deposit_status']['deposit_status'], style: 'tdStyle'},
                        {text: value['amount'], style: 'tdStyle'},
                        {text: value['payment_status']['status'], style: 'tdStyle'},
                    ]);
                });
                var documentDefinition = {
                    header: {text: 'Booking List', style: 'header'},
                    content:
                        [
                            {
                                table:
                                    {
                                        headerRows: 1,
                                        width: ['*', '*', '*', '*', '*', '*', '*', '*'],
                                        body: bodyRows,
                                    },
                            }
                        ],
                    footer: {
                        text: 'PDF Generated By Charge Automation on ' + new Date(Date.now()).toDateString(),
                        style: 'footerCaption'
                    },
                    styles:
                        {
                            header: {
                                fontSize: 18,
                                bold: true,
                                margin: [0, 20, 0, 5],
                                alignment: 'center',
                            },
                            footerCaption: {
                                fontSize: 11,
                                bottom: 0,
                                margin: [10, 0, 0, 0],
                                alignment: 'center'
                            },
                            tableHeader: {
                                fontSize: 9,
                                fillColor: '#4CAF50',
                                color: 'white',
                                alignment: 'center',
                            },
                            tdStyle: {
                                fontSize: 8
                            }
                        },
                    pageOrientation: 'portrait',
                    pageMargins: [20, 60, 20, 30],
                };

                pdfMake.createPdf(documentDefinition).download(this.file_name); //'Booking-List' + this.getAppliedConstraintsString() + '.pdf');
                this.exportType = '';
            },

            /**
             * @param resetPageNumber
             */
            setPaginationPageNumberRecordsPerPage(resetPageNumber = false) {
                if (((this.filter.dateOne.length > 0) && (this.filter.dateTwo.length > 0)) || ((this.filter.date.length > 0) && (this.filter.date.length == 'today'))) {
                    this.filter.recordsPerPage = 20;
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
            isPropertySelected() {
                if ((this.filter.dateOne.length > 0) && (this.filter.dateTwo.length > 0)
                    || ((this.filter.date.length > 0) && (this.filter.date == 'today'))) {
                    if (this.filter.property_id == '' || this.filter.property_id == null) {
                        toastr.error('Kindly Select Property to use Check-in Filter, Check-in dates are shown in Property Local TimeZone.');
                        return false;
                    }
                }
                return true;
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

                if (this.filter.property_id > 0) {
                    constraintStr += (constraintStr.length == 0 ? '(' : ' --') + ' Property - ' + this.filter.property_id;
                }

                let today = new Date();
                let file_name = 'Booking-List-' + today.getDate() + '-' + monthNames[today.getMonth()] + '-' + today.getFullYear();

                this.file_name = (file_name + ' ' + constraintStr + (constraintStr.length > 0 ? ')' : '') + '.' + exportExtension).toString();
                return true;
            },

            get_properties() {
                let self = this;
                axios.post('/client/v2/get-properties-names')
                    .then(response => {
                        this.properties = response.data;
                    });
            },
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
                    return state.ba.booking.booking_list;
                },
                // booking_detail: (state) => {
                //     return state.ba.booking.booking_detail;
                // },
                can_sync_booking: (state) => {
                    return state.ba.booking.can_sync_booking;
                },
            }),

            ...mapActions('ba/', [
                'canAddSyncTime',
            ]),
        },
        watch: {
            filter: {
                deep: true,
                immediate: true,
                handler(nv, ov) {
                    // console.log({'old': ov, 'new': nv});
                    // alert(ov);
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
                    if ((f.dateOne != nv.dateOne && f.dateTwo != nv.dateTwo && nv.date != 'today')) {
                        count++;
                    }
                    if (nv.property_id > 0) {
                        count++;
                    }
                    this.filter_count = count;
                }
            }
        },
        mounted() {
            this.get_properties();
            this.canAddSyncTime;
        }

    }
</script>
<style>
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 80%;
        color: #EB3B5A;
    }

    .date-vue-picker .row {
        margin: 0 !important;
    }

    .booking-box-dates .booking-box-duration {
        flex: none !important;
        flex-grow: initial !important;
    }

    #booking-listing-page .filter-form.sync-booking::before {
        right: 20px;
    }
</style>
