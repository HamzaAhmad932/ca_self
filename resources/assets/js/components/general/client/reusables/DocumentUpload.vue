<template>
    <div>
        <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog"
             tabindex="-1" style="z-index: 1051;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New Document</h4>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                aria-hidden="true"><i class="fas fa-times"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label class="mb-0" for="passport_file">Passport/Government ID: </label><small
                                    class="form-text text-muted mb-2">(Passport, ID or Driver License)</small>
                                <div class="custom-file">
                                    <input
                                            @change="uploadDocument($event)"
                                            accept="image/*"
                                            class="custom-file-input"
                                            data-notify-id="passport_uploaded"
                                            id="passport_file"
                                            name="passport"
                                            type="file"
                                    />
                                    <label class="custom-file-label" for="passport_file">Choose file</label>
                                    <small class="form-text text-muted mb-2">Max file size: 5MB (JPG or PNG)</small>
                                    <small class="form-text text-error" v-if="document_upload.error_status.passport">{{document_upload.error_message.passport}}</small>
                                </div>
                            </div>
                            <hr/>

                            <div class="form-group">
                                <label class="mb-0" for="credit_card_file">Credit Card Scan: </label><small
                                    class="form-text text-muted mb-2">(Credit card scan)</small>
                                <div class="custom-file">
                                    <input
                                            @change="uploadDocument($event)"
                                            accept="image/*"
                                            class="custom-file-input"
                                            data-notify-id="credit_card_uploaded"
                                            id="credit_card_file"
                                            name="credit_card"
                                            type="file"
                                    />
                                    <label class="custom-file-label" for="credit_card_file">Choose file</label>
                                    <small class="form-text text-muted mb-2">Max file size: 5MB (JPG or PNG)</small>
                                    <small class="form-text text-error" v-if="document_upload.error_status.credit_card">{{document_upload.error_message.credit_card}}</small>
                                </div>
                            </div>
                            <hr/>

                            <div class="form-group">
                                <label class="mb-0" for="credit_card_file">Self portrait: </label><small
                                    class="form-text text-muted mb-2">(Selfie)</small>
                                <div class="custom-file">
                                    <input
                                            @change="uploadDocument($event)"
                                            accept="image/*"
                                            class="custom-file-input"
                                            data-notify-id="selfie"
                                            id="selfie_file"
                                            name="selfie"
                                            type="file"
                                    />
                                    <label class="custom-file-label" for="credit_card_file">Choose file</label>
                                    <small class="form-text text-muted mb-2">Max file size: 5MB (JPG or PNG)</small>
                                    <small class="form-text text-error" v-if="document_upload.error_status.selfie">{{document_upload.error_message.selfie}}</small>
                                </div>
                            </div>
                            <hr/>

                        </form>
                        <!--                        <a class="btn btn-sm btn-outline-secondary" href=""> <i class="fas fa-plus-circle"> </i> Add More</a>-->
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary mx-auto px-3" data-dismiss="modal" type="button">Close
                        </button>
                        <!--                        <button class="btn btn-sm btn-success px-3" type="button">Upload Files</button>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';

    export default {
        name: 'DocumentUpload',
        props: ['calling_id', 'booking_id'],
        methods: {

            ...mapActions('general/', [
                'saveDocument'
            ]),

            uploadDocument: function (event) {

                let is_invalid = this.imageValidator(event.target.files[0]);

                if (!is_invalid) {

                    let data = new FormData();
                    data.append('name', event.target.name);
                    data.append('file', event.target.files[0]);
                    data.append('alert_type', event.target.dataset.notifyId);
                    data.append('booking_id', this.booking_id);
                    data.append('requested_by', 'client_document_upload');
                    this.saveDocument(data);
                }


            },

            imageValidator(file) {

                var errorFlag = false;
                var types = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!types.includes(file.type)) {
                    toastr.error("Image type must be 'JPG', 'PNG', 'JPEG'.");
                    errorFlag = true;
                }
                if (file.size > 5000000) {
                    toastr.error("Image size must be less than 5 MB.");
                    errorFlag = true;
                }

                return errorFlag;
            },
        },

        computed: {
            ...mapState({
                document_upload: function (state) {
                    return state.general.document_upload
                }
            })
        }

    }
</script>