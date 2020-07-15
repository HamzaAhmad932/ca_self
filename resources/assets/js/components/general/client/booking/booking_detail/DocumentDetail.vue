<template>
    <div>
        <document-upload :booking_id="booking_id" calling_id="upload_doc"></document-upload>
        <guest-document-description calling_id="get_image_description"></guest-document-description>

        <div class="row">
            <div class="col-12 text-right">
                <a class="btn btn-secondary btn-sm helper-btn pull-right" data-target="#upload_doc" data-toggle="modal"
                   href="">Upload <span class="hidden-xs"> Document</span></a>
            </div>
        </div>
        <div class="mt-3 mb-4" v-if="documents.documents_to_check.length > 0">
            <div class="card-section-title">
                <h4>Documents to Check <span class="badge badge-warning">{{documents.documents_to_check.length}}</span>
                </h4>
            </div>
            <div class="document-section border-warning" v-for="doc in documents.documents_to_check">
                <div class="row">
                    <div class="col-md-6">
                        <div class="verification-document">
                            <img :src="doc.image">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="document-legend">
                            <p class="fw-500">{{doc.title}}</p>
                            <div class="mb-3">
                                <p class="text-muted text-md mb-1"><span
                                        class="badge badge-info">{{doc.uploaded_info}}</span></p>
                            </div>
                            <button v-if="doc.type == 'passport' || doc.type == 'credit_card'" @click.prevent="updateDocumentStatus({id: doc.id, booking_id, value: 1})"
                                    class="btn btn-sm btn-success mb-1 mt-1">
                                <i class="fas fa-check"> </i>
                                Accept Document
                            </button>&nbsp;
                            <button
                                    v-if="doc.type == 'passport' || doc.type == 'credit_card'"
                                    data-target="#get_image_description"
                                    data-toggle="modal"
                                    @click.prevent="setDocumentDescriptionData({id: doc.id, booking_id, value: 2})"
                                    class="btn btn-sm btn-danger mb-1 mt-1">
                                <i class="fas fa-times"> </i>
                                Decline
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 mb-4" v-if="documents.accepted_documents.length > 0">
            <div class="card-section-title">
                <h4>Approved <span class="badge badge-success">{{documents.accepted_documents.length}}</span></h4>
            </div>
            <div class="document-section border-success" v-for="doc in documents.accepted_documents">
                <div class="row">
                    <div class="col-md-6">
                        <div class="verification-document">
                            <img :src="doc.image">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="document-legend">
                            <p class="fw-500">{{doc.title}}</p>
                            <div class="mb-3">
                                <p class="text-muted text-md mb-1"><span
                                        class="badge badge-info">{{doc.uploaded_info}}</span><br><span
                                        class="badge badge-success">{{doc.status_info}}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 mb-4" v-if="documents.rejected_documents.length > 0">
            <div class="card-section-title">
                <h4>Declined <span class="badge badge-danger">{{documents.rejected_documents.length}}</span></h4>
            </div>
            <div class="document-section border-danger" v-for="doc in documents.rejected_documents">
                <div class="row">
                    <div class="col-md-6">
                        <div class="verification-document">
                            <img :src="doc.image">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="document-legend">
                            <p class="fw-500">{{doc.title}}</p>
                            <div class="mb-3">
                                <p class="text-muted text-md mb-1"><span
                                        class="badge badge-info">{{doc.uploaded_info}}</span><br>
                                    <span class="badge badge-danger">{{doc.status_info}}</span></p>
                            </div>
                            <button v-if="doc.type == 'passport' || doc.type == 'credit_card'" @click.prevent="deleteDocument({id: doc.id, booking_id})"
                                    class="btn btn-sm btn-danger"><i
                                    class="fas fa-times"> </i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 mb-4 mx-auto"
             v-if="documents.rejected_documents.length == 0 && documents.accepted_documents.length == 0 && documents.documents_to_check.length == 0 && documents.deleted_documents.length == 0">
            <p class="text-center">No Documents</p></div>

        <div class="mt-3 mb-4 mx-auto"
             v-if="documents.rejected_documents.length == 0 && documents.accepted_documents.length == 0 && documents.documents_to_check.length == 0 && documents.deleted_documents.length > 0">
            <p class="text-center">{{documents.deleted_documents.length == 1 ? 'Document' : 'Documents'}}  deleted by host</p></div>

    </div>
</template>
<script>
    import {mapActions, mapMutations, mapState} from "vuex";
    import DocumentUpload from "../../reusables/DocumentUpload";
    import GuestDocumentDescriptionModalBox from "../../reusables/GuestDocumentDescriptionModalBox";

    export default {
        name: 'Documents',
        props: ['booking_id'],
        components: {
            DocumentUpload,
            'guest-document-description': GuestDocumentDescriptionModalBox
        },
        mounted() {
            this.fetchGuestDocuments(this.booking_id);
        },
        data() {
            return {
                img: '/storage/uploads/user_images/no_image.png',
            }
        },
        methods: {
            ...mapActions('general/', [
                'fetchGuestDocuments',
                'updateDocumentStatus',
                'deleteDocument'
            ]),
            ...mapMutations('general/', {
                setDocumentDescriptionData: 'SET_DOCUMENT_DESCRIPTION_DATA'
            })
        },
        computed: {
            ...mapState({
                documents: function (state) {
                    return state.general.documents
                }
            })
        }
    }
</script>
<style scoped>
    .document-section {
        margin: 5px 5px;
    }
</style>