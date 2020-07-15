<template>
    <div>
           <div class="card-section" v-if="isBookingCapableForPayments(booking_detail.capabilities)">
                    <div class="card-section-title">Activity Log</div>
                    <div class="card-inset-table">
                        <div class="table-responsive">
                            <table class="table activity-log-table">
                                <tr>
                                    <th>Order</th>
                                    <th>Transaction Reference</th>
                                    <th>Event Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Actual Response</th>
                                    <th>Attempted</th>
                                </tr>
                                <tr v-for="(log, i) in booking_detail.activity_log">
                                    <td>{{parseInt(i)+parseInt(1)}}</td>
                                    <td>{{log.id}}</td>
                                    <td>{{log.event_date}}</td>
                                    <td><div class="badge" :class="log.class">{{log.t_type}}</div></td>
                                    <td>{{log.desc_cc}}</td>
                                    <td>{{log.status_msg}}</td>
                                    <td>{{log.attempted}}</td>
                                </tr>
                                <tr v-if="booking_detail.activity_log.length == 0">
                                    <td>No record available</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
    </div>
</template>


<script>
export default {
    
}
</script>