<template>
    <div>
        <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog"
             tabindex="-1" style="z-index: 1051;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Document Description</h4>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                aria-hidden="true"><i class="fas fa-times"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label class="mb-0" for="description">Please mention reason: </label><small
                                    class="form-text text-muted mb-2">(Rejection reason, improvements, suggestions etc)</small>
                                <div class="custom-file">
                                    <textarea name="description" id="description" class="form-control" v-model="description"></textarea>
                                    <small class="form-text text-error" v-if="error !== ''">{{error}}</small>
                                </div>
                            </div>

                        </form>
                        <!--                        <a class="btn btn-sm btn-outline-secondary" href=""> <i class="fas fa-plus-circle"> </i> Add More</a>-->
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary px-3" data-dismiss="modal" type="button" id="force_close_guest_document_description_modal">Close
                        </button>
                        <button class="btn btn-sm btn-success px-3" type="button" @click.prevent="submitDocumentStatus()">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';

    export default {
        name: 'GuestDocumentDescription',
        props: ['calling_id'],
        data(){
            return {
                description: '',
                error: ''
            }
        },
        methods: {

            ...mapActions('general/', [
                'saveDocument',
                'updateDocumentStatus'
            ]),
            submitDocumentStatus(){

                if(this.description === ''){
                    this.error = 'Document rejection reason is required.';
                    return;
                }
                this.document_description.description = this.description;
                this.updateDocumentStatus(this.document_description);
                this.error = '';
                $('#force_close_guest_document_description_modal').click();
                this.description = '';
            }
        },

        computed: {
            ...mapState({
                document_description: function (state) {
                    return state.general.document_description
                }
            })
        }

    }
</script>