<template>
    <div class="gp-header">
        <a class="company-logo" href="#">
            <h4 style="color: #102A43; font-size: 23px; font-weight: bold; font-family: 'Roboto', sans-serif;">
                {{property_name}}</h4>
        </a>
        <div class="gp-nav">
            <a :href="'tel:'+tel" v-if="tel !== '' && tel !== undefined">
                <i class="fas fa-phone-volume"> </i><span>{{tel}}</span>
            </a>
            <a :href="'mailto:'+email" v-if="email !== '' && email !== undefined">
                <i class="far fa-envelope"> </i><span>Email Us</span>
            </a>
        </div>
        <div v-if="is_chat_active">
            <button @click="makeBookingIdReactiveForCommunication(booking_id)"
                    aria-controls="chat-panel" class="btn btn-success text-white chat-open chat-btn-v2">
                <i class="far fa-comment"> </i>
                <span>Live Chat </span>
            </button>
            <a data-target="#chat_panel_right" ref="openChatPanel" style="display: none">-</a>
        </div>
    </div>
</template>
<script>
    export default {
        name: 'guest-header',
        props: ['property_name', 'is_chat_active', 'email', 'tel', 'booking_id'],
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
                this.$refs.openChatPanel.click();
                this.$store.dispatch('booking_id_action_chat', payload);
            },
            openChatTab(){
                if(location.hash === '#open_chat'){
                    this.makeBookingIdReactiveForCommunication(this.booking_id);
                }
            }
        }
    }
</script>
