<template>
    <div>

        <document-upload :booking_id="booking_id" calling_id="upload_doc"></document-upload>
        <guest-document-description calling_id="get_image_description"></guest-document-description>

        <div aria-hidden="true" aria-labelledby="uploadGuestID" class="modal fade" id="uploadGuestID" role="dialog" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <!-- modal-header -->
                    <div class="modal-header">
                        <h4 class="modal-title"> Guest Documents Upload </h4>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true"><i class="fas fa-times"></i></span>
                        </button>
                    </div>

                    <!-- modal-body -->
                    <div class="modal-body">
                        <div class="dataTables_wrapper dt-bootstrap4 no-footer" id="m_table_1_wrapper">
                            <div style="align-items:center;display:flex;justify-content:center;">
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <a class="btn btn-secondary btn-sm helper-btn pull-right" data-target="#upload_doc" data-toggle="modal"
                                           href="">Upload <span class="hidden-xs"> Document</span></a>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="image-container">
                                <div v-for="image in images.all">
                                    <div>
                                        <div class="mt-1 mb-3 text-center">
                                            {{image.title}}
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-12" style="height: 22px" v-if="!image.client_action"></div>
                                            <div class="col-12 text-center" v-if="image.client_action">
                                                <span v-if="image.status == 0" class="badge badge-warning">Pending</span>
                                                <span v-else-if="image.status == 1" class="badge badge-success">Approved</span>
                                                <span v-else-if="image.status == 2" class="badge badge-danger">Rejected</span>
                                            </div>
                                            <!--<div class="col-6 pull-right" v-if="image.client_action">
                                                <span @click="deleteDocument({id: image.id, booking_id})" class="flaticon-close fas fa-trash" style="color: red; cursor: pointer; float: right;"></span>
                                            </div>-->
                                        </div>
                                    </div>

                                    <div class="guest-documents-outter-wrapper">
                                        <a :href="image.image" target="_blank">
                                            <img :src="image.image" alt="Upload image">
                                        </a>
                                    </div>

                                    <div class="dropdown dropdown-sm mt-1 mb-3" v-if="image.client_action && image.status != 1">
                                        <a id="id_uploaded_action" class="btn btn-xs dropdown-toggle" aria-expanded="false" aria-haspopup="true" data-toggle="dropdown" role="button">
                                            Action
                                        </a>
                                        <div aria-labelledby="moreMenu" class="dropdown-menu dropdown-menu-right" >
<!--                                            <a-->
<!--
-->
<!--                                                    data-target="#get_image_description"-->
<!--                                                    data-toggle="modal" class="dropdown-item"-->
<!--                                                    @click.prevent="setDocumentDescriptionData({id: image.id, booking_id, value: 2})"-->
<!--                                            >-->
<!--                                                <i class="flaticon-close"></i>Reject-->
<!--                                            </a>-->
                                            <a v-if="image.status == 2" @click.prevent="updateDocumentStatus({id: image.id, booking_id, value: 1})" class="dropdown-item">
                                                <i class="la la-check-circle-o"></i> Approve
                                            </a>
                                            <span v-else>
                                                <a @click.prevent="updateDocumentStatus({id: image.id, booking_id, value: 1})" class="dropdown-item">
                                                    <i class="la la-check-circle-o"></i> Approve
                                                </a>
                                                <a
                                                        data-target="#get_image_description"
                                                        data-toggle="modal"
                                                        class="dropdown-item"
                                                        @click.prevent="setDocumentDescriptionData({id: image.id, booking_id, value: 2})"
                                                >
                                                    <i class="flaticon-close"></i> Reject
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- modal-footer -->
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary px-3" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {mapState, mapActions, mapMutations} from 'vuex';
    import DocumentUpload from "./DocumentUpload";
    import GuestDocumentDescription from "./GuestDocumentDescriptionModalBox";

    export default {
        components: {
            DocumentUpload,
            'guest-document-description': GuestDocumentDescription
        },
        computed: {
            ...mapState({
                booking_id: (state) => {
                    return state.general.guest_upload_doc_id;
                },
                images: (state)=> {
                    return state.general.images;
                }
            })
        },
        watch: {
            booking_id: {
                immediate: true,
                handler(newVal, oldVal) {
                    this.fetchGuestDocuments(newVal);
                }
            }
        },
        methods: {

            ...mapActions('general/', [
                'fetchGuestDocuments',
                'deleteDocument',
                'updateDocumentStatus'
            ]),

            ...mapMutations('general/',{
                setDocumentDescriptionData: 'SET_DOCUMENT_DESCRIPTION_DATA'
            })
        }
    }
</script>
<style>
    .image-container {
        display: flex;
        flex-wrap: wrap;
    }

    .image-container > div {
        display: flex;
        flex-direction: column;
        margin: 5px;
    }

    #id_uploaded_action {
        width: 100%;
    }
</style>
