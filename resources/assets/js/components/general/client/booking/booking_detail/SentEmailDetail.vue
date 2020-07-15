<template>
    <div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Sent Email</h4>
            </div>
            <div class="table-responsive">
                <table class="table text-md table-striped">
                    <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Sent To</th>
                        <th>Sent Date & Time</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(email, i) in sent_emails.data">
                            <td>{{email.email_subject}}</td>
                            <td>{{email.sent_to}}</td>
                            <td>{{email.sent_date_time}}</td>
                        </tr>
                        <tr v-if="sent_emails.length == 0">
                            <td colspan="5" style="text-align: center;">No record available</td>
                        </tr>
                    </tbody>
                </table>

                <pagination :data="pagination_data" :limit="1" @pagination-change-page="fetchSentEmailsTrigger"
                            align="right"></pagination>
            </div>
        </div>
    </div>
</template>
<script>
    import {mapActions, mapState} from "vuex";

    export default {
        name: 'SentEmail',
        props: ['booking_id'],
        data() {
            return {
                //this is done to remove VUE warning as pagination component was unable to read data key -- so I removed data key
                pagination_data:{
                    links:{},
                    meta:{}
                }
            }
        },
        mounted() {
            this.fetchSentEmailsTrigger();
        },

        methods: {
            ...mapActions('general/', [
                'fetchSentEmails'
            ]),

            /**
             * Getting Properties List Using Pagination
             */
            fetchSentEmailsTrigger(page = 1) {
                let url = '/client/v2/get-sent-emails/'+this.booking_id+'?page='+page;
                this.fetchSentEmails(url);
            }
        },

        computed: {
            ...mapState({
                sent_emails: function (state) {
                    return state.general.booking_detail.sent_emails
                }
            })
        },

        watch: {
            sent_emails: function (val, oldVal) {
                this.pagination_data.links = val.links;
                this.pagination_data.meta = val.meta;
            }
        }
    }
</script>