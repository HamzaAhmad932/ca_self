<template>
    <!-- Chat-->
    <div :class="show_chat_panel ? 'chat-visible': ''" :id="calling_id" class="chat-panel">
        <a @click.prevent="dismiss_chat()" class="chat-dismiss" href="#">
            <span>Close </span><i class="fas fa-times"></i>
        </a>
        <div class="chat-panel-title chat-panel-guest-name-portion">
            <h4>
                {{guest.guest_name | shortName }}
                <span>{{ guest.property_name | shortName }} - {{ guest.room_name  | shortName }}</span>
            </h4>
        </div>
        <div class="chat-content" id="chat-box-div"
             v-chat-scroll="{always: true, smooth: true, scrollonremoved:true, smoothonremoved: false}">
            <!--    v-chat-scroll="{always: false, smooth: true}" ,  v-chat-scroll="{always: false, smooth: true, scrollonremoved:true, smoothonremoved: false}" -->
            <!-- Chat Messages-->
            <div v-for="(m,index) in messages.messages" v-if="messages.messages !== undefined">

                <span class="text-center"
                      style="clear:both;color: #83909d;padding-left: 20%;" v-if="parseInt(messages.lastUnSeenMessageId) === parseInt(m.id) && (m.is_guest == 1)"> ------- Unread messages ------- </span>
                <div class="chat-message message-inbox" v-if="m.is_guest == 1">
                    <div class="message-bulb">
                        <div class="message-author">Guest:</div>
                        <div class="message-description">
                            {{m.message}}
                        </div>
                        <div class="message-legend">{{m.created_at | msg_time}}</div>
                    </div>
                </div>
                <div class="chat-message message-sent" v-else-if="m.is_guest == 0">
                    <div class="message-bulb">
                        <div class="message-author">You:</div>
                        <div class="message-description">
                            {{m.message}}
                        </div>
                        <div class="message-legend">
                            <!--                            <i class="fas fa-check"></i> -->
                            {{m.created_at | msg_time}}
                        </div>
                    </div>
                </div>
            </div>
            <!-- end Chat Messages-->
        </div>
        <div class="chat-controls">
            <!--            <a class="fa fa-arrow-circle-down" title="Jump to Latest Message" id="scroll-arrow" @click="scrollChatToBottom()"  v-if="!isChatDivMaxBottomScrolled()">-->
            <!--                <span class="fa fa-comment" v-if="(messages.unSeenMessagesCount !== undefined) && (messages.unSeenMessagesCount > 0)"></span>-->
            <!--                <span class="num" v-if="(messages.unSeenMessagesCount != undefined) && (messages.unSeenMessagesCount > 0)">{{messages.unSeenMessagesCount}}</span>-->
            <!--            </a>-->
            <textarea :disabled="sendingMsg" @keydown.enter="sendmsg" class="form-control chat-input"
                      placeholder="Type message" v-model="message"></textarea>
            <a @click.prevent="dismiss_chat()" class="chat-attachment-btn">
                <span>Close </span> &nbsp;<i class="fas fa-times"></i>
                <!--                <i class="fas fa-times"></i>-->
            </a>
            <button :disabled="sendingMsg" @click.prevent="sendMessage()" class="btn btn-success"><i
                    class="fas fa-paper-plane"> </i><span>Send</span></button>
        </div>
    </div>
</template>

<script>

    import {mapState} from 'vuex';
    import moment from 'moment';

    import VueChatScroll from 'vue-chat-scroll';

    Vue.use(VueChatScroll);

    export default {
        props: ['calling_id'],
        data() {
            return {
                sendingMsg: false,
                show_chat_panel: false,
                message: '',
                messages: {},
                totalReadMsgCount: 0,
                guest: {
                    guest_name: '',
                    property_name: '',
                    room_name: '',
                }
            }
        },
        methods: {
            dismiss_chat() {
                this.show_chat_panel = !this.show_chat_panel;
                let payload = {};
                this.$store.dispatch('general/booking_id_action_chat', payload);
            },
            reset() {
                Object.assign(this.$data, this.$options.data());
            },

            getMax(obj) {
                if (obj != undefined)
                    return Math.max.apply(null, Object.keys(obj));
                else
                    return 0;
            },

            fetchChat(msgSent = false) {
                let self = this;
                let seenMessageId = 0;
                if (self.isChatDivMaxBottomScrolled()) {
                    if (self.messages.messages != undefined) {
                        //let indexCount = _.size(self.messages.messages);
                        let lastKey = this.getMax(self.messages.messages);
                        if (lastKey != undefined) {
                            let lastMessage = self.messages.messages[lastKey];
                            seenMessageId = lastMessage != undefined ? lastMessage.id : 0;
                        }
                    }
                }

                axios({
                    url: '/client/v2/allmsgs',
                    method: 'POST',
                    data: {'bookingInfoId': self.booking.booking_id, 'lastSeenMessageId': seenMessageId}
                })
                    .then((resp) => {
                        self.messages = resp.data.data;
                        let booking = resp.data.data.booking;
                        if (booking.guest_name != null && booking.guest_name != undefined) {
                            self.guest.guest_name = booking.guest_name;
                        }

                        if (booking.property_info != null && booking.property_info != undefined) {
                            self.guest.property_name = booking.property_info.name;
                        }

                        if (booking.room_info != null && booking.room_info != undefined) {
                            self.guest.room_name = booking.room_info.name;
                        }

                        if (booking.unit != null && booking.unit !== undefined && booking.unit.unit_name != null && booking.unit.unit_name !== undefined) {
                            self.guest.room_name = booking.room_info.name + ' - ' + booking.unit.unit_name;
                        }
                    })
                    .catch((err) => {
                        console.log(err);
                    });
                    setTimeout(() => {
                        if (typeof self.booking.booking_id != "undefined") {
                            self.fetchChat();
                        }
                    }, 20000);
            },
            sendMessage() {
                if (this.message == '' || this.message == null) {
                    return;
                }

                let self = this;
                let payload = {
                    bookingInfoId: self.booking.booking_id,
                    pms_booking_id: self.booking.pms_booking_id,
                    msgtext: self.message,
                    is_guest: 0
                };
                self.sendingMsg = true;
                // var d = new Date();
                let d = new Date();
                let lastMsgId = this.getMax(self.messages.messages);
                lastMsgId = ((lastMsgId == '-Infinity') || (lastMsgId == null) ? -1 : lastMsgId);
                //Subtract 2 Mints to handle time difference Issues
                let olderDate = moment(d).subtract(3, 'minutes').toDate();

                if (self.messages.messages[lastMsgId + 1] =
                    {
                        "id": lastMsgId + 1, "is_guest": 0, "alert_type": "chat", "message": self.message,
                        "message_read_by_guest": 0, "message_read_by_user": 1, "created_at": olderDate,
                        "updated_at": d
                    }) {
                    self.message = '';
                    //self.scrollChatToBottom();
                }
                self.sendingMsg = false;
                axios({
                    url: '/client/v2/guest_chat',
                    method: 'POST',
                    data: payload
                }).then((resp) => {
                    if (resp.data.status == false) {
                        toastr.error(resp.data.message);
                        //self.scrollChatToBottom();
                        self.fetchChat(true);
                    }
                }).catch((err) => {
                    alert('Unprocessable Entity');
                });
            },

            scrollChatToBottom() {
                var elem = document.querySelector('#chat-box-div');
                elem.scrollTop = elem.scrollHeight;
            },

            isChatDivMaxBottomScrolled() {
                var elem = document.querySelector('#chat-box-div');
                if (elem !== undefined && elem !== null) {
                    return Math.ceil(elem.scrollHeight - elem.scrollTop) === (elem.clientHeight);
                }
                return false;
            },

            sendmsg: function (e) {
                e.stopPropagation();
                e.preventDefault();
                e.returnValue = false;
                this.sendMessage();
                /*if (e.keyCode == 13 && !e.shiftKey){
                  e.preventDefault();
                }*/
            }
        },

        mounted() {
            if (this.booking.booking_id) {
                setTimeout(() => {
                    this.fetchChat()
                }, 10000);
                this.scrollChatToBottom();
            }
        },
        computed: {
            ...mapState({
                booking: (state) => {
                    return state.general.booking_id_action_chat;
                }
            })
        },
        watch: {
            booking: {
                deep: true,
                immediate: true,
                handler(newVal) {
                    //Rest chat box
                    this.reset();
                    //open chat box
                    if (newVal.booking_id !== undefined && newVal.booking_id !== 0) {
                        this.show_chat_panel = true;
                        this.fetchChat();
                        // this.guest_name =  newVal.guest_name;
                        // this.property_name =  newVal.property.name;
                        // this.room_name =  newVal.room.room_type + ' ' + newVal.room.unit_name;
                    }
                }
            }
        },
        filters: {
            msg_time: function (date) {
                if (location.hostname === "localhost" || location.hostname === "127.0.0.1") {
                    //return moment.parseZone(date).utc().local().format('Y-m-d h:i:s A');
                    return moment.parseZone(date).utc().local().format('YYYY-MM-DD hh:mm a');

                } else {
                    return moment.parseZone(date).add(140, 'seconds').utc().local().format('YYYY-MM-DD hh:mm a');
                }
            },
            shortName: function (value) {
                if (!value) {
                    return '';
                } else {
                    if (value.length > 70) {
                        return value.substring(0, 70) + '...';
                    } else {
                        return value
                    }
                }
            }
        }
    }
</script>
<style>
    .chat-panel {
        z-index: 1000;
    }

    #scroll-arrow {
        position: absolute;
        font-size: 2em;
        color: grey;
        cursor: pointer;
        bottom: 27%;
    }

    span.fa-comment {
        position: absolute;
        font-size: 0.6em;
        top: -4px;
        color: red;
        right: -4px;
    }

    span.num {
        position: absolute;
        font-size: 0.3em;
        top: 1px;
        color: #fff;
        right: 2px;
    }
</style>
