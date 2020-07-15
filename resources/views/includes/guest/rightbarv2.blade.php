<div class="chat-panel" id="chat-panel">
    <a class="chat-dismiss" href="#0">
        <span>Close </span>
        <i class="fas fa-times"></i>
    </a>
    <div class="chat-panel-title">
        <h4>Live Chat</h4>
    </div>

    <div style="overflow-x: hidden; overflow-y: scroll;" id="chat-box-div" v-chat-scroll="{always: true, smooth: true, scrollonremoved:true, smoothonremoved: false}">
        <div class="chat-content" style="overflow: hidden;" v-for="b_chat in communication.messages" v-if="communication.messages">
            <span v-if="(parseInt(communication.lastUnSeenMessageId) == parseInt(b_chat.id) && (b_chat.is_guest == 0))" class="text-center" style="clear:both;color: #83909d;padding-left: 20%;"> ------- Unread messages ------- </span>
            <!-- Chat Messages-->
            <div class="chat-message message-inbox" v-if="b_chat.is_guest == 0">
                <div class="message-bulb">
                    <div class="message-author">Support:</div>
                    <div class="message-description">
                        <p>@{{b_chat.message}}</p>
                    </div>
                    <div class="message-legend">@{{b_chat.created_at | msg_time}}</div>
                </div>
            </div>
            <div class="chat-message message-sent message-read" v-if="b_chat.is_guest == 1">
                <div class="message-bulb">
                    <div class="message-author">You:</div>
                    <div class="message-description">
                        <p>@{{b_chat.message}}</p>
                    </div>
                    <div class="message-legend"> @{{b_chat.created_at | msg_time}}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="chat-controls">
{{--        <a class="fa fa-arrow-circle-down" title="Jump to Latest Message" id="scroll-arrow" @click="scrollChatToBottom()" v-if="!isChatDivMaxBottomScrolled()" >--}}
{{--            <span class="fa fa-comment" v-if="(communication.unSeenMessagesCount !== undefined) && (communication.unSeenMessagesCount > 0)"></span>--}}
{{--            <span class="num" v-if="(communication.unSeenMessagesCount != undefined) && (communication.unSeenMessagesCount > 0)">@{{communication.unSeenMessagesCount}}</span>--}}
{{--        </a>--}}
        <textarea class="form-control chat-input" id="clrForm" v-model="textMessage.msgtext" placeholder="Type message" @keydown.enter="sendmsg"></textarea>
        <a class="chat-attachment-btn" href="#0" @click="closeChat()">
            <span>Close </span>
        </a>
        <button class="btn btn-success pull-left" @click.prevent="((textMessage.msgtext.length > 0) && (textMessage.msgtext != '') ? msgBtn() : '')" :disabled="communication_submit">
            <i class="fa fa-paper-plane"></i>
            <span>Send</span>
        </button>

    </div>
</div>