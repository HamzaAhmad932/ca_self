<template>
    <div>
        <div class="page-content" id="page_content_dashboard">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-header has-border-bottom mb-4">
                            <h1 class="page-title">Welcome {{analytics.client.name | splitted}}</h1>
                            <div class="page-header-legend">
                                <div class="page-header-legend-item">
                                    <div>
                                        <h5 style="line-height: 2.25rem !important; ; ">Active Properties</h5>
<!--                                        <span>{{analytics.all_booking_sources}} Booking Sources</span>-->
                                    </div>
                                    <div class="legend-value" >{{analytics.active_properties}} / {{analytics.all_properties}} </div>
                                    <a class="overlay-link" href="#0"></a>
                                </div>
<!--                                <div class="page-header-legend-item">-->
<!--                                    <div>-->
<!--                                        <h5>Sales</h5><span>Last Month</span>-->
<!--                                    </div>-->
<!--                                    <div class="dropdown user-menu">-->
<!--                                        <a aria-expanded="false"-->
<!--                                           aria-haspopup="true"-->
<!--                                           class="dropdown-toggle"-->
<!--                                           data-toggle="dropdown"-->
<!--                                           id="dropdownMenuBtn" style="background: transparent !important;">-->
<!--                                            <span class="d-none d-sm-block d-md-block d-lg-block">-->
<!--                                                <i class="fas fa-circle"-->
<!--                                                   style="font-size: 5px; display: inherit; float:left; margin:0 1px;"></i>-->
<!--                                                <i class="fas fa-circle"-->
<!--                                                   style="font-size: 5px; display: inherit; float:left; margin:0 1px;"></i>-->
<!--                                                <i class="fas fa-circle"-->
<!--                                                   style="font-size: 5px; display: inherit; float:left; margin:0 1px;"></i>-->
<!--                                            </span>-->
<!--                                        </a>-->
<!--                                        <div aria-labelledby="dropdownMenuBtn" class="dropdown-menu dropdown-menu-right"-->
<!--                                             style="z-index: 1">-->
<!--                                            <div v-if="analytics.total_sale.length != 0">-->
<!--                                                <a class="dropdown-item" v-for="i in analytics.total_sale">{{i.currency_code}}{{i.price}}</a>-->
<!--                                            </div>-->
<!--                                            <div v-else>-->
<!--                                                <a class="dropdown-item">No record yet</a>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
                            </div>
                        </div>
                        <div class="page-body">
                            <div class="content-box"
                                 v-if="analytics.show_line_graph == true && pieChartData[0].values.length != 0"
                                 >
<!--                                <div class="content-box-header">-->
<!--                                    <h2 class="box-title">Successful Transactions</h2>-->
<!--                                </div>-->
                                <div class="content-box-body"
                                     style="min-height: 300px;overflow: hidden;">
                                    <div class="report-widget">
                                        <div class="row">
                                            <div class="col-lg-7 col-md-7"
                                                 v-if="analytics.show_line_graph">
                                                <apexchart width="100%" type="line" :options="line_bar.options" :series="line_bar.series"></apexchart>
<!--                                                <vue-plotly :data="lineChartData" :layout="lineChartLayout" :options="lineChartOptions"/>-->
                                            </div>
                                            <div class="col-lg-5 col-md-5"
                                                 v-if="pieChartData[0].values.length != 0">
                                                <apexchart width="100%" type="donut" :options="donut.options" :series="donut.series"></apexchart>
<!--                                                <vue-plotly :data="pieChartData" :layout="pieChartLayout" :options="pieChartOptions"/>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-if="$can('bookings')">
                                <div class="col-md-12 col-lg-12">
                                    <div class="content-box">
                                        <div class="content-box-header">
                                            <h2 class="box-title">Upcoming Arrivals</h2><a class="viewmore-link"
                                                                                           href="/client/v2/bookings"><span
                                                class="d-none d-sm-inline d-md-inlin d-lg-inline">View All Bookings</span>
                                            →</a>
                                        </div>
                                        <div class="booking-list-widget">
                                            <div class="table-header d-none d-lg-block">
                                                <div class="row no-gutters">
                                                    <div class="col-1 col-lg">
                                                        <div class="table-box-check d-flex">
                                                            <span>ID</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <span>Guest</span>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="booking-box-dates">
                                                            <div class="booking-box-checkin">
                                                                <span>Check-In </span>
                                                            </div>
                                                            <div class="booking-box-duration"> &nbsp;&nbsp; →</div>
                                                            <div class="booking-box-checkout">
                                                                <span>Check-Out</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-2 col-lg">
                                                        <span>Security Deposit</span>
                                                    </div>
                                                    <div class="col-2 col-lg">
                                                        <span>Amount</span>
                                                    </div>
                                                    <div class="col-2 col-lg">
                                                        <span>Payment Status</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <booking-list :booking_list="upcoming_arrivals"
                                                          is_booking_list_page="false"></booking-list>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
        </div>
    </div>
</template>

<script>

    import {mapState} from 'vuex';
    import BookingList from "../booking/booking_list/BookingList";
    import VueApexCharts from 'vue-apexcharts';

    export default {
        components: {
            BookingList,
            'apexchart': VueApexCharts
        },
        data() {
            return {
                lineChartLayout: {
                    autosize: true,
                    height: 300,
                    margin: {
                        l: 60,
                        r: 50,
                        b: 60,
                        t: 50,
                        pad: 3
                    },
                    yaxis: {
                        tickmode: 'array',
                        automargin: false,
                        titlefont: {size: 30},
                        tickformat:",d",
                        fixedrange: true
                    },
                    xaxis : {fixedrange: true}
                },
                lineChartOptions: {
                    responsive: true,
                    displayModeBar: false
                },

                // pieChartLayout
                pieChartLayout: {
                    annotations: [
                        {
                            font: {
                                size: 20,
                            },
                            showarrow: false,
                            text: 'Source',
                            x: 0.50,
                            y: 0.5
                        }
                    ],
                    height: 300,
                    width: 325,
                    margin: {
                        l: 0,
                        r: 0,
                        b: 50,
                        t: 50,
                        pad: 2
                    },
                    showlegend: true,
                    grid: {rows: 1, columns: 1}
                },
                pieChartOptions: {
                    displayModeBar: false,
                    responsive: true
                },
                toggleSale: false,
            }
        },
        methods: {},
        created() {
            this.$store.dispatch('ba/fetchUpcomingArrivals');
            this.$store.dispatch('ba/fetchDashboardAnalytics');
        },
        mounted() {
        },
        filters: {
            splitted(value) {
                if (value) {
                    const f = value.split(" ")[0].charAt(0).toUpperCase();
                    return f + value.split(" ")[0].slice(1).toLowerCase();
                }
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
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                analytics: (state) => {
                    return state.ba.dashboard.analytics;
                },
                upcoming_arrivals: (state) => {
                    return state.ba.dashboard.upcoming_arrivals;
                },
                donut: (state) =>{
                    return {
                        options: {
                            chart: {
                                id: 'graph-donut'
                            },
                            labels: state.ba.dashboard.analytics.pie.labels,
                            title: {
                                text: "Booking Sources",
                                align: 'center'
                            },
                            legend: {
                                position: 'bottom'
                            }
                        },
                        series: state.ba.dashboard.analytics.pie.values
                    }
                },
                line_bar: (state)=>{
                    return {
                        options: {
                            chart: {
                                id: 'graph-line'
                            },
                            title:{
                                text: 'Successful Transactions',
                                align: 'left'
                            },
                            stroke: {
                                curve: 'smooth',
                            },
                            xaxis: {
                                categories: state.ba.dashboard.analytics.line.labels
                            }
                        },
                        responsive: [
                            {

                            }
                        ],
                        series: [{
                            name: 'Booking amount',
                            data: state.ba.dashboard.analytics.line.values
                        }]
                    }
                },
                lineChartData: (state) => {
                    return [
                        {
                            x: state.ba.dashboard.analytics.line.labels,
                            y: state.ba.dashboard.analytics.line.values,
                            mode: 'lines+markers',
                            name: 'Markers + Lines',
                        }
                    ];
                },
                pieChartData: (state) => {
                    return [
                        {
                            values: state.ba.dashboard.analytics.pie.values,
                            labels: state.ba.dashboard.analytics.pie.labels,
                            text: 'Bookings',
                            textposition: 'inside',
                            domain: {column: 0},
                            name: 'Bookings',
                            hoverinfo: 'label+percent+name',
                            hole: .5,
                            type: 'pie',
                        }
                    ]
                },
            })
        },
    }

</script>
<style>
    /*#page_content_dashboard{
        padding-left: 7rem !important;
        padding-right: 7rem !important;
    }*/
    .booking-box-dates .booking-box-duration {
        flex: none !important;
        flex-grow: initial !important;
    }

</style>
