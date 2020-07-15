<template>
    <!-- Property Guide Booking Detail Model -->
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i :class="guide_book_type.icon"> </i>
                        {{ guide_book_type.title }}
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row" style="margin-bottom: 5% !important;" v-for="guide_book in guide_book_type.guide_books">
                        <div class="container" style="border-bottom: #0a0a0a solid 1px;" v-html="guide_book.text_content"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary btn-sm" data-dismiss="modal" type="button"> Ok</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End: Property Guide Booking Detail Model -->
</template>

<script>

    import {mapState} from 'vuex';

    import VueChatScroll from 'vue-chat-scroll';

    Vue.use(VueChatScroll);

    export default {
        props: ['calling_id'],
        computed: {
            ...mapState({
                guide_book_type: (state) => {
                    return state.guest_portal.guide_book_type;
                }
            })
        },
    }
</script>
