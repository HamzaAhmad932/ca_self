<template>
    <div>
        <div aria-hidden="true" aria-labelledby="permissionInstructions" class="modal fade" id="permission_instructions"
             role="dialog" tabindex="-1">
            <div class="modal-dialog modal-lg" role="document" style="margin-top:10%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="permissionInstructions">
                            <i class="fas fa-info-circle"></i> Instructions For Grant Permission
                        </h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>
                            <h3>1. Click Access Persmission icon at top right side of the address bar.</h3>
                        <br>
                        <div class="text-center">
                            <img class="img img-responsive" src="/v2/img/instruction_1.png" height="500px" alt="Instruction image 1">
                        </div>
                        </p>
                        <hr>
                        <br>
                        <p>
                            <h3>2. Check Allow to access your camera and click "Done".</h3>
                        <br>
                        <div class="text-center">
                            <img class="img img-responsive" src="/v2/img/instruction_2.png" height="500px" alt="Instruction image 2">
                        </div>
                        </p>
                        <hr>
                        <br>
                        <p>
                            <h3>3. Reload page.</h3>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" data-dismiss="modal" type="button">Ok</button>
                    </div>
                </div>
            </div>
        </div>

        <header-steps :meta="meta"></header-steps>
        <div class="gp-box gp-box-of-inner-pages">
            <read-only-mode :meta="meta"></read-only-mode>
            <div class="gp-box-content box-hv">
                <div class="gp-inset">
                    <form>
                        <div class="form-group">
                            <p style="color: #2c2e3e; text-align: center; font-weight: bold;">Take a selfie to authenticate your identity</p>

                            <div style="width: 100%;">
                                <div class="camera-preview" id="preview-container">
                                    <video autoplay height="100%" id="video-preview" playsinline
                                           width="100%"></video>
                                    <img alt="" hidden id="image-preview" src="" width="100%"/>
                                    <canvas hidden id="snapshot-canvas"
                                            style="position: absolute; z-index: -9999;"></canvas>
                                </div>

                                <div class="camera-button-container">
                                    <a @click.prevent="openCamera()" class="btn btn-info btn-sm"
                                       href="javascript:" v-bind:hidden="btnTryAgain"><i
                                            class="fa fa-sync"></i> Not satisfied? Retake!</a>
                                    <a @click.prevent="takeSnapShot()" class="btn btn-success btn-sm"
                                       href="javascript:" v-bind:hidden="btnClick"><i
                                            class="fa fa-camera"></i> Take Picture</a>
                                </div>

                            </div>

                            <p style="color: #D93371; text-align: center; font-weight: lighter;" v-html="error">
                            </p>

                        </div>
                    </form>

                </div>

            </div>
            <footer-statement></footer-statement>
        </div>

        <pre-checkin-footer @saveAndContinue="saveAndContinue"
                            :button_text="'Save & Continue'"
                            :booking_id="booking_id"
                            :show_forward_arrow="true">

        </pre-checkin-footer>


        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>

<script>

    // https://webrtc.github.io/samples/

    import {mapActions, mapState} from 'vuex';
    import FooterStatement from "./FooterStatement";

    export default {
        name: 'photo-booth',
        props: ['booking_id', 'type'],
        components: {
            FooterStatement
        },
        data() {
            return {
                btnTryAgain: true,
                btnClick: false,
                image: null,
                stream: null,
                video: null,
                error: null,
                image_action: 'old',
                isPhotoCaptured: false,
                constraints: {
                    audio: false,
                    video: {
                        facingMode: 'user',
                        width: {exact: 640},
                        height: {exact: 480}
                    }
                }
            }
        },
        methods: {

            ...mapActions([
                'saveSelfPortrait',
                'goToPreviousStep',
                'fetchStepSevenData',
                'goToNextStep'
            ]),

            handleSuccess() {
                this.video = document.querySelector('video');

                // User for meta data
                let videoTracks = this.stream.getVideoTracks();
                console.log('Using video device: ' + videoTracks[0].label);

                this.video.srcObject = this.stream;
            },

            handleError(error) {

                let errorName = error.name;

                switch (errorName) {
                    case 'ConstraintNotSatisfiedError':
                    case 'OverconstrainedError':
                        const v = this.constraints.video;
                        this.errorMsg("The resolution " + v.width.exact + "x" + v.height.exact + " is not supported by your device.");
                        break;
                    case 'NotAllowedError':
                    case 'PermissionDeniedError':
                        this.errorMsg('Permission(s) have not been granted to use your camera, you need to allow this page to access camera. For more info '+"<a href=\"#\" data-toggle=\"modal\" data-target=\"#permission_instructions\" id=\"permission_instruction_modal\">click here</a>");
                        break;
                    case 'NotFoundError':
                        this.errorMsg('Camera not found, Please attach webcam or complete it on Mobile/Laptop');
                        break;
                    default:
                        this.errorMsg('Error: ' + errorName, error);
                }

            },

            errorMsg(msg, error) {

                this.error = "*" + msg;

                if (typeof error !== 'undefined') {
                    console.error(error);
                }
            },

            async openCamera() {

                let video = document.querySelector('#video-preview');
                video.removeAttribute('hidden');

                let imagePreview = document.querySelector('#image-preview');
                imagePreview.removeAttribute('src');
                imagePreview.setAttribute('hidden', '');
                imagePreview.removeAttribute('style',);

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia(this.constraints);
                    this.handleSuccess();
                    this.error = '';
                } catch (e) {
                    this.handleError(e);
                }

                this.btnTryAgain = true;
                this.btnClick = false;
                this.isPhotoCaptured = false;

            },

            stopCamera() {
                this.stream.getTracks().forEach(track => track.stop());
            },

            setImagePreview(image) {

                let imagePreview = document.querySelector('#image-preview');
                imagePreview.setAttribute('src', image);
                imagePreview.removeAttribute('hidden');
                imagePreview.setAttribute('style', 'display: block;');

                let video = document.querySelector('#video-preview');
                video.setAttribute('hidden', 'true');
            },

            takeSnapShot() {

                let canvas = document.querySelector('#snapshot-canvas');
                canvas.width = this.video.videoWidth;
                canvas.height = this.video.videoHeight;
                canvas.getContext('2d').drawImage(this.video, 0, 0, canvas.width, canvas.height);
                this.image = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");

                this.setImagePreview(this.image);

                this.stopCamera();
                this.image_action = 'new';
                this.btnTryAgain = false;
                this.btnClick = true;
                this.isPhotoCaptured = true;
                this.error = '';
            },

            previous() {

                let data = {
                    booking_id: this.booking_id,
                    meta: this.meta,
                };

                this.goToPreviousStep(data);
            },

            saveAndContinue() {

                if (!this.meta.is_guest && this.meta.read_only_mode == 1) {
                    let data = {
                        booking_id: this.booking_id,
                        meta: this.meta
                    };

                    this.goToNextStep(data);
                    return;
                }

                if (this.isPhotoCaptured) {
                    let data = {
                        type: this.type,
                        booking_id: this.booking_id,
                        image: this.image,
                        image_action: this.image_action,
                        meta: this.meta
                    };
                    this.saveSelfPortrait(data);
                } else {
                    this.error = 'Please Take your Selfie by pressing "Click" button. Thanks';
                }

            },

        },
        mounted() {

            let self = this;

            this.fetchStepSevenData(this.booking_id).then(function () {

                if (self.step_7.selfie == '') {
                    self.openCamera();

                } else {
                    self.image_action = 'old';
                    self.btnTryAgain = false;
                    self.btnClick = true;
                    self.isPhotoCaptured = true;
                    self.error = '';
                    self.setImagePreview("/" + self.step_7.selfie);
                }

            });
        },

        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                step_7: (state) => {
                    return state.pre_checkin.step_7;
                },
                meta: (state) => {
                    return state.pre_checkin.meta;
                }
            })
        },
        watch: {
            step_7: {
                deep: true,
                immediate: true,
                handler(new_value, old_value) {

                    if (new_value.is_completed) {
                        window.location.href = this.step_7.next_link;
                    }

                }
            }
        }
    }
    /*
    let data = {
                        booking_id: this.booking_id,
                        meta: this.meta,
                    };
                    this.goToNextStep(data);
     */
</script>
<style>
    .camera-preview {
        width: 320px;
        height: 240px;
        margin: 0 auto;
        box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.75);
        -webkit-box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.75);
        -moz-box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.75);
    }

    .camera-button-container {
        margin-top: 10px;
        text-align: center;
    }
</style>
