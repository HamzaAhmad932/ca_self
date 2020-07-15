<template>
    <div>
        <header-steps :meta="meta"></header-steps>
        <div class="gp-box gp-box-of-inner-pages">
            <read-only-mode :meta="meta"></read-only-mode>
            <div class="gp-box-content box-hv">
                <div class="gp-inset">
                    <div class="row">
                        <div class="col" style="margin-bottom: 1rem !important;">
                            <h3 class="lead fw-500 mb-0" >{{guest_images_status.credit_card !== undefined || guest_images_status.passport !== undefined ? 'Uploaded' : 'Upload'}} Document(s)</h3>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col" v-if="meta.is_passport_scan_required && guest_images_status.passport == undefined">
                            <div class="UploadImages text-center"
                                 style="margin: 0 auto; margin-bottom: 20px;">
                                <label for="passport_file">
                                    <div>
                                        <h4>
                                            <img alt="Click Here To Upload Your Image"
                                                 src="/v2/img/id_card.png"
                                                 style="cursor: pointer;">
                                        </h4>
                                    </div>
                                    <small class="form-text  text-danger" v-if="error_status.passport">{{error_message.passport}}</small>
                                </label>
                                <br>
                                <div class="fileUpload btn btn-danger btn-sm">
                                    <i class="fas fa-upload"></i>
                                    <span>Upload Passport/Government ID</span>
                                    <input
                                            @change="uploadDocument($event)"
                                            accept="image/*"
                                            class="upload"
                                            data-notify-id="passport_uploaded"
                                            id="passport_file"
                                            name="passport"
                                            type="file"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col" v-if="meta.is_credit_card_scan_required && guest_images_status.credit_card == undefined">
                            <div class="UploadImages text-center"
                                 style="margin: 0 auto; margin-bottom: 20px;">
                                <label for="credit_card_file">
                                    <div>
                                        <h3>
                                            <img alt="Click Here To Upload Your Image"
                                                 src="/v2/img/credit_card_instructions.png"
                                                 style="cursor: pointer; min-height: 128px">
                                        </h3>
                                    </div>
                                    <small class="form-text  text-danger" v-if="error_status.credit_card">{{error_message.credit_card}}</small>
                                </label>
                                <br>
                                <div class="fileUpload btn btn-danger btn-sm">
                                    <i class="fas fa-upload"></i>
                                    <span>Upload Credit Card</span>
                                    <input
                                            @change="uploadDocument($event)"
                                            accept="image/*"
                                            class="upload"
                                            data-notify-id="credit_card_uploaded"
                                            id="credit_card_file"
                                            name="credit_card"
                                            type="file"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col" v-for="image in step_3.images" v-if="image.type == 'passport' || image.type == 'credit_card'">
                            <div class="verification-document text-center">

                                <div  class="form-group col-md-1 cross-div" v-if="image.status != 1">
                                    <button class="cross-btn" style="border: 2px solid #f7d074; !important;"
                                            @click.prevent="updateScan(image.type)"
                                            :title="'Update ('+image.type+') Image'">
                                        &#9998;
                                    </button>
                                </div>

                                <img :src="'/storage/uploads/guestImages/'+image.image" alt="">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <footer-statement></footer-statement>
        </div>

        <input style="display:none" @change="uploadDocument($event)" accept="image/*" data-notify-id="credit_card_uploaded" ref="credit_card_file_update" name="credit_card" type="file"/>
        <input style="display:none" @change="uploadDocument($event)" accept="image/*" data-notify-id="passport_uploaded" ref="passport_file_update" name="passport" type="file"/>

        <pre-checkin-footer
                @saveAndContinue="saveAndContinue"
                :button_text="'Save & Continue'"
                :booking_id="booking_id"
                :show_forward_arrow="true">
        </pre-checkin-footer>
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';
    import FooterStatement from "./FooterStatement";

    export default {
        props: ['booking_id'],
        components: {
            FooterStatement
        },
        mounted() {
            this.fetchStepThreeData(this.booking_id);
        },
        data() {
            return {
                is_passport_scan_required: false,
                is_credit_card_scan_required: false,
                error_status: {
                    passport: false,
                    credit_card: false
                },
                error_message: {
                    passport: 'Passport/Government ID is required.',
                    credit_card: 'Credit card is required.'
                }
            }
        },
        methods: {
            ...mapActions('general/', [
                'saveVerificationImage',
                'fetchStepThreeData',
                'goToPreviousStep',
                'goToNextStep',
                'deletePreCheckinImages',
            ]),

            updateScan(image_type) {
                if (image_type == 'passport') {
                    this.$refs.passport_file_update.click();
                } else if (image_type == 'credit_card') {
                    this.$refs.credit_card_file_update.click();
                }
            },

            uploadDocument: function (event) {

                if (!this.meta.is_guest && this.meta.read_only_mode == 1) {
                    toastr.warning('You are in Read-Only mode. You cannot update image.');
                    return;
                }

                let is_invalid = this.imageValidator(event.target.files[0]);

                if (!is_invalid) {

                    let data = new FormData();
                    data.append('name', event.target.name);
                    data.append('file', event.target.files[0]);
                    data.append('alert_type', event.target.dataset.notifyId);
                    data.append('booking_id', this.booking_id);
                    data.append('requested_by', 'pre_checkin');
                    this.saveVerificationImage(data);
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

            saveAndContinue() {

                let self = this;
                let data = {
                    booking_id: this.booking_id,
                    meta: this.meta,
                };

                if (!this.meta.is_guest && this.meta.read_only_mode == 1) {
                    this.goToNextStep(data);
                    return;
                }

                this.step_3.images.forEach(function (img) {
                    if (img.type == 'passport') {
                        self.is_passport_scan_required = false;
                    }
                    if (img.type == 'credit_card') {
                        self.is_credit_card_scan_required = false;
                    }
                });

                if (this.validateScanRequired()) {
                    this.goToNextStep(data);
                }
            },
            validateScanRequired() {

                let flag = true;
                this.error_status.passport = false;
                this.error_status.credit_card = false;

                if (this.is_credit_card_scan_required) {
                    this.error_status.credit_card = true;
                    flag = false;
                }

                if (this.is_passport_scan_required) {
                    this.error_status.passport = true;
                    flag = false;
                }

                return flag;
            },

            previous() {

                let data = {
                    booking_id: this.booking_id,
                    meta: this.meta
                };

                this.goToPreviousStep(data);
            },
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                step_3: (state) => {
                    return state.general.pre_checkin.step_3;
                },
                meta: (state) => {
                    return state.general.pre_checkin.meta;
                },
                guest_images_status: (state) => {
                    return state.general.pre_checkin.guest_images_status;
                }
            }),
        },
        watch: {
            step_3: {
                deep: true,
                immediate: true,
                handler(new_value, old_value) {

                    this.error_status = {
                        passport: false,
                        credit_card: false
                    };
                }
            },
            meta: {
                deep: true,
                immediate: true,
                handler(n_value, o_value) {
                    this.is_passport_scan_required = this.meta.is_passport_scan_required;
                    this.is_credit_card_scan_required = this.meta.is_credit_card_scan_required;
                    if (n_value.is_completed) {
                        window.location.href = this.meta.next_link;
                    }
                }
            }
        }
    }
</script>
<style type="text/css">
    [v-cloak] {
        display: none;
    }

    .input-group-prepend button.btn, .input-group-append button.btn {
        z-index: 0;
    }

    .gp-dl {
        text-align: left !important;
    }

    span.initial_icon {
        background-color: #334e68;
        color: #fff;
        border-radius: 100%;
        width: 32px;
        line-height: 32px;
        text-align: center;
        font-weight: 600;
        font-size: 16px;
        margin-right: 5px;
    }
    .cross-btn:hover{
        width: 30px;
        height: 30px;
    }
    .cross-btn {
        color: #f77474;
        background-color: transparent;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid #f77474;
        width: 26px;
        height: 26px;
        text-align: center;
        padding: 3px 3px 3px 3px;
        font-size: 10px;
        margin: 0px 0px 6px 0px;
        float: right;
    }

    .cross-div{
        right: 1rem;
        top: 1rem;
        position: absolute;
    }

</style>
