<template>
    <div>
        <a class="tp-chat-btn chat-open"
           @click="makeBookingIdReactiveForCommunication(booking_id)"
           href="javascript:void(0)"
           data-target="#chat_panel_right"
           ref="openChatPanel"
           aria-controls="chat-panel"
           v-if="is_chat_active">
            <i class="far fa-comment-alt"></i>
        </a>

        <!--            <button @click="makeBookingIdReactiveForCommunication(booking_id)"-->
        <!--                    aria-controls="chat-panel" class="btn btn-success text-white chat-open chat-btn-v2">-->
        <!--                <i class="far fa-comment"> </i>-->
        <!--                <span>Live Chat </span>-->
        <!--            </button>-->
        <!--            <a data-target="#chat_panel_right" ref="openChatPanel" style="display: none">-</a>-->
    </div>
</template>
<script>
    export default {
        name: 'pre-checkin-chat-button',
        props: ['is_chat_active', 'booking_id'],
        mounted() {
            this.openChatTab();
        },
        methods: {
            makeBookingIdReactiveForCommunication(booking_id) {
                //console.log([booking_id, pms_booking_id]);
                let payload = {
                    booking_id,
                    //pms_booking_id
                };
                //this.$refs.openChatPanel.click();
                this.$store.dispatch('general/booking_id_action_chat', payload);
            },
            openChatTab(){
                if(location.hash === '#open_chat'){
                    this.makeBookingIdReactiveForCommunication(this.booking_id);
                }
            }
        }
    }
</script>
