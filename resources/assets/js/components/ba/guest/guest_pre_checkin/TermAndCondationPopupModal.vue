<template>
    <div>
        <div aria-hidden="true" aria-labelledby="termAndCondationPopupModalLabel" class="modal fade" id="term-and-condation-popup-modal"
             role="dialog" style="display:none;" tabindex="-1">
            <div class="modal-dialog" role="document" style="margin-top:10%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termAndCondationPopupModalLabel">
                            <i class="fas fa-info-circle"></i> Terms and Conditions
                        </h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" v-html="terms_and_conditions.text_content" >
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" data-dismiss="modal" type="button">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

    export default {
        props: ['terms_and_conditions'],
        mounted() {

        },
        data() {
            return {}
        },
        methods: {},

        computed: {},
        watch: {}
    }
</script>