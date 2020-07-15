<template>
    <div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Activity Log</h4>
            </div>
            <div class="table-responsive">
                <table class="table text-md table-striped activity-log-table">
                    <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Transaction Reference</th>
                        <th>Event Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Actual Response</th>
                        <th>Attempted</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(log, i) in activity_log">
                        <td>{{parseInt(i)+parseInt(1)}}</td>
                        <td>{{log.id}}</td>
                        <td>{{log.event_date}}</td>
                        <td><div class="badge" :class="log.class">{{log.t_type}}</div></td>
                        <td>{{log.desc_cc}}</td>
                        <td>{{log.status_msg}}</td>
                        <td>{{log.attempted}}</td>
                    </tr>
                    <tr v-if="activity_log.length == 0">
                        <td colspan="6" style="text-align: center;">No record available</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script>
    import {mapActions, mapState} from "vuex";

    export default {
        name: 'PaymentAttemptsActivityLog',
        props: ['booking_id', 'pms_prefix'],
        mounted() {
            this.fetchActivityLogs(this.booking_id);
        },

        methods: {
            ...mapActions('general/',[
                'fetchActivityLogs'
            ]),
        },

        computed: {
            ...mapState({
                activity_log: function (state) {
                    return state.general.booking_detail.activity_log
                }
            })
        }
    }
</script>