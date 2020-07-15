<template>
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-10">

                        <div class="page-header all-notifications-page-header has-border-bottom mt-4 mb-4">
                            <!--<a class="text-muted d-block text-md mb-1" href="#">← Back</a>-->
                            <h1 class="page-title">Notifications
                                <span class="badge badge-primary" v-if="unread_notifications > 0">{{ unread_notifications }} Unread</span>
                            </h1>
                            <div class="booking-pagination" v-if="unread_notifications > 0 && ($can('full client') || is_manager)">
                                <a :data-id="current_page" @click.prevent="markAllAsRead($event)" class="btn btn-secondary btn-sm"
                                   href="#">Mark All as Read</a>
                            </div>
                        </div>

                        <div class="page-body">
                            <div class="content-box notification-expanded">
                                <div :class="[notification.action_performed==1 ? 'read' : '', notifications_type_text['icon_color_class'][notification.alert_type] ]" class="notification-item"
                                     v-for="(notification, index) in notifications">
                                    <h4>{{ notifications_type_text['alert_type_text'][notification.alert_type] }}</h4>
                                    <div class="text-md text-muted">{{notification.created_at | time_format}} • Booking
                                        ID {{ notification.pms_booking_id }}
                                    </div>
                                    <hr>
                                    <div class="my-2 text-dark">{{
                                        notifications_type_text['alert_type_messages'][notification.alert_type] +
                                        notification.pms_booking_id }}
                                    </div>
                                    <div class="notification-icon">
                                        <i :class="notifications_type_text['fa_class'][notification.alert_type]"></i>
                                    </div>
                                    <a :data-original-title="[notification.action_performed==1 ? 'Mark as unread' : 'Mark as read']" :title="[notification.action_performed==1 ? 'Mark as unread' : 'Mark as read']" class="notification-status"
                                       data-placement="left" data-toggle="tooltip"
                                       style="cursor: default"
                                       v-if="$can('full client') || is_manager">
                                        <span :accessKey="index"
                                              :data-id="current_page"
                                              :data-status="[notification.action_performed==1 ? 0 : 1]"
                                              :id="notification.id"
                                              @click.prevent="readOrUnread($event)"
                                              style="cursor: pointer"
                                        ></span>
                                    </a>
                                    <a class="notification-dismiss cross-btn" data-original-title="Delete"
                                       data-placement="right" data-toggle="tooltip"
                                       title="Delete" v-if="$can('full client') || is_manager">
                                        <i :data-id="current_page" :data-status="notification.id" @click.prevent="deleteNotification($event)"
                                           class="fas fa-times"></i>
                                    </a>
                                </div>

                                <div class="user-card user-connected" v-if="notifications.length == 0">
                                    <div class="card-pane">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-12">
                                                No notification found.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div style="float: right !important">
                                    <pagination :data="paginationResponse" :limit="1"
                                                @pagination-change-page="getAllNotifications"></pagination>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <BlockUI :html="html" message="Please Wait" v-if="block == true"></BlockUI>
    </div>
</template>

<script>
    import moment from "moment";
    export default {
        data() {
            return {
                paginationResponse: {},
                notifications: [],

                notifications_type_text: [],
                unread_notifications: 0,
                is_manager: false,

                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',

                current_page: 1,

                filters: {
                    recordsPerPage: 10,
                    page: 1,
                    columns: [],
                    relations: [],
                    sort: {
                        sortOrder: "DESC",
                        sortColumn: "created_at",
                    },
                    constraints: [],
                    search: {
                        searchInColumn: [],
                        searchStr: ""
                    },
                },

            }
        },
        mounted() {
            this.getAllNotifications();
        },
        methods: {
            getAllNotifications(page = 1) {
                let _this = this;
                _this.block = true;
                _this.filters.page = page;
                axios.post('/client/v2/get_all_notifications?page=' + page, {'filters': _this.filters})
                    .then(function (response) {
                        //console.log(response);
                        _this.unread_notifications = response.data.data.unread_notifications;
                        _this.notifications_type_text = response.data.data.notifications_type_text;
                        _this.paginationResponse = response.data.data.all_notifications;
                        _this.notifications = _this.paginationResponse.data;
                        _this.current_page = _this.paginationResponse.current_page;
                        _this.is_manager = response.data.data.is_manager;
                        _this.block = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        _this.block = false;
                    });
            },
            readOrUnread(event) {
                let _this = this;

                let notification_id = event.target.id;
                let current_page = event.target.dataset.id;
                let status = event.target.dataset.status;
                let indexNumber = event.target.accessKey;

                var read = 'read';
                var unread = 'unread';

                var txt = (status == 1 ? read : unread);

                /*swal.fire({
                    title: "Are you sure want to mark as " + txt + " notification",
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, do it!"
                })
                    .then(function (e) {
                        if (e.value == true) {*/
                _this.block = true;
                axios.post('/client/v2/notification_read/' + notification_id + '/' + status)
                    .then(function (response) {
                        toastr.success("Notification successfully " + txt);
                        _this.$store.dispatch('fetchNotificationsData', 5);
                        _this.getAllNotifications(current_page);
                    })
                    .catch(function (error) {
                        _this.block = false;
                        console.log(error);
                        toastr.info("Some error occurred please try again.");
                    });
                /*}
            });*/
            },
            markAllAsRead(event) {
                let _this = this;
                let current_page = event.target.dataset.id;

                swal.fire({
                    title: "Are you sure want to mark all as read",
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, do it!"
                })
                    .then(function (e) {
                        if (e.value == true) {
                            _this.block = true;
                            axios.post('/client/v2/mark_all_as_read')
                                .then(function (response) {
                                    toastr.success("Notifications successfully read");
                                    _this.$store.dispatch('fetchNotificationsData', 5);
                                    _this.getAllNotifications(current_page);
                                })
                                .catch(function (error) {
                                    _this.block = false;
                                    console.log(error);
                                    toastr.info("Some error occurred please try again.");
                                });
                        }
                    });
            },
            deleteNotification(event) {
                let _this = this;

                let current_page = event.target.dataset.id;
                let notification_id = event.target.dataset.status;

                swal.fire({
                    title: "Are you sure want to delete",
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, do it!"
                })
                    .then(function (e) {
                        if (e.value == true) {
                            _this.block = true;
                            axios.delete('/client/v2/notification_destroy/' + notification_id)
                                .then(function (response) {
                                    toastr.success("Notifications successfully deleted");
                                    _this.$store.dispatch('fetchNotificationsData', 5);
                                    _this.getAllNotifications(current_page);
                                })
                                .catch(function (error) {
                                    _this.block = false;
                                    toastr.info("Some error occurred please try again.");
                                });
                        }
                    });


            }
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.replace(/([A-Z])/g, ' $1').trim();
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1);
            },
            time_format: function (date) {
                if (location.hostname === "localhost" || location.hostname === "127.0.0.1") {
                    return moment.parseZone(date).utc().local().format('Do-MMM-YYYY hh:mm a');
                } else {
                    return moment.parseZone(date).add(140, 'seconds').utc().local().format('Do-MMM-YYYY hh:mm a');
                }
            },
        }
    }
</script>