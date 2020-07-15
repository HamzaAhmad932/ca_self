<!-- Override styles to show small dot for unread notifications -->
<style type="text/css">
    .notification-sidebar .notification-list .custom-control-label {
        cursor: pointer;
    }

    .notification-sidebar .notification-list .custom-control-label::before {
        background-color: #0052cc;
        width: 0.7rem;
        height: 0.7rem;
    }
</style>

<template>
    <div class="notification-sidebar">
        <div class="notification-list">
            <div class="notification-widget">
                <div class="widget-header warning">
                    <h4 class="widget-title">Notifications</h4>
                    <a class="readmore" href="#" v-on:click.prevent="allNotifications">See All ({{ total_notifications
                        }}) →</a>
                </div>
                <div class="widget-body timeline" id="all-notifications">

                    <template v-for="notification in notifications">
                        <div v-bind:key="notification.id">
                            <div class="custom-control custom-radio float-right"
                                 v-if="!notification.action_performed && ($can('full client') || is_manager)">
                                <input :data-alert-id="notification.id" :id="'check-' + notification.id"
                                       class="custom-control-input" type="radio" v-on:click.prevent="alertActionPerformed">
                                <label :for="'check-' +notification.id" class="custom-control-label"
                                       title="Mark as read"></label>
                            </div>
                            <div :class="'timeline-item ' +notificationsIconColorClass[notification.alert_type]">
                                <div class="timeline-icon"><i
                                        :class="notificationsFaClass[notification.alert_type]"></i></div>
                                <div class="timeline-item-content">
                                    <div class="timeline-time">{{ notification.created_at | time_format }}</div>
                                    <div class="timeline-text">
                                        <strong>{{ notificationsTypeText[notification.alert_type] }} for booking #{{
                                            notification.pms_booking_id }}</strong>
                                    </div>
                                    <a :href="getUrlLink(notification.booking_info_id, notification.booking_info.pms_booking_id, notification.alert_type)" class="overlay-link"
                                       target="_blank"></a>
                                </div>
                            </div>
                        </div>

                    </template>

                    <p v-if="total_notifications<=0">You don't have any notification.</p>

                </div>

                <div class="loader" style="position: initial;" v-if="start_loader">
                    <div class="spinner">
                        <svg height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <title>circle 02</title>
                            <g class="nc-icon-wrapper" fill="#486581">
                                <g class="nc-loop_circle-02-24" transform="rotate(132.29857142840046 12 12)">
                                    <path d="M12,24C5.3833008,24,0,18.6166992,0,12S5.3833008,0,12,0s12,5.3833008,12,12 S18.6166992,24,12,24z M12,2C6.4858398,2,2,6.4858398,2,12s4.4858398,10,10,10s10-4.4858398,10-10S17.5141602,2,12,2z" fill="#486581"
                                          opacity="0.4"/>
                                    <path d="M24,12h-2c0-5.5141602-4.4858398-10-10-10V0C18.6166992,0,24,5.3833008,24,12z"
                                          data-color="color-2"/>
                                </g>
                            </g>
                        </svg>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex';
    import moment from "moment";

    export default {
        data() {
            return {
                start_loader: false,
                shown_notifications: 5,
                view_more_notifications: true,
                load_more_notifications: 5 //this will be static and only used to plus count of shown_notification variable
            }
        },
        props: {
            notificationsFaClass: {
                // type: Array,
                default: []
            },
            notificationsIconColorClass: {
                // type: Array,
                default: []
            },
            notificationsTypeText: {
                // type: Array,
                default: []
            },
            notificationsRedirectToTab: {
                // type: Array,
                default: []
            },
            is_manager: {
                default: false
            }
        },
        methods: {
            alertActionPerformed: function (event) {
                if (event.target.value == 'on') {
                    this.$store.dispatch('alertActionPerformed', {
                        'event': event,
                        'notification_shown': this.shown_notifications
                    });
                }
            },
            refreshNotification: function () {
                this.start_loader = true;
                this.$store.dispatch('general/fetchNotificationsData', this.shown_notifications);
                this.start_loader = false;
                setTimeout(this.refreshNotification, 120000);
            },
            getUrlLink(bookingInfoId, pmsBookingId, notificationType) {
                if (notificationType == 'chat') {
                    return '/client/v2/bookings?booking-id=' + bookingInfoId + '#' + bookingInfoId + '-' + pmsBookingId;
                } else {
                    return '/client/v2/bookings?booking-id=' + bookingInfoId;
                }
            },
            allNotifications: function () {
                window.location.href = '/client/v2/all_notifications';
            }
        },
        mounted() {
            //call the method to get notification and count
            this.refreshNotification();
        },
        watch: {
            notifications_counts: function (val, oldVal) {
                // if(val < this.total_notifications) {
                //     this.view_more_notifications = true;
                // } else {
                //     this.view_more_notifications = false;
                // }
                if (val > 5)
                    this.shown_notifications = val;
            },
            // total_notifications : function(val, oldVal){
            //     if(val > this.notifications_counts) {
            //         this.view_more_notifications = true;
            //     } else {
            //         this.view_more_notifications = false;
            //     }
            // },
            total_unread_notifications: function (val, oldVal) {
                $("#notificationCounts").text(val);
            }
        },
        computed: {
            ...mapState({
                notifications: (state) => {
                    return state.general.notification.notifications;
                },
                total_notifications: (state) => {
                    return state.general.notification.total_available_notifications;
                },
                total_unread_notifications: (state) => {
                    return state.general.notification.total_unread_notifications;
                },
                notifications_counts: (state) => {
                    return state.general.notification.current_showing_notification_count;
                }
            })
        },

        filters: {
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
