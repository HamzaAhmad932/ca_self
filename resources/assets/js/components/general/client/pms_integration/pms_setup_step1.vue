<template v-if="$can('accountSetup')">
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <account-enable-disable-button></account-enable-disable-button>
                        <div class="page-body pmsintegration_page_content">
                            <div class="content-box">
                                <div class="setup-box account-setup-content">
                                    <!-- STEPS NAV-BAR BEGIN-->
                                    <pms_setup_steps_navbar :step="1"></pms_setup_steps_navbar>
                                    <!-- STEPS NAV-BAR END-->

                                    <div class="setup-body">
                                        <h4 class="setup-page-title">Connect your Property Management System</h4>
                                        <form class="pms-setup-form">
                                            <div class="form-group">
                                                <label for="select-box-Pms">Select PMS</label>
                                                <select @change="getSelectedPmsCredentialsForm($event.target.value)" class="custom-select custom-select-sm"
                                                        id="select-box-Pms">
                                                    <option :data-backend-name="pms.backend_name" :value="pms.id"
                                                            v-for="pms in supportedPmsList"> {{ pms.name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group" v-for="credential in pmsForm.credentials"
                                                 v-if="show_api_key_section">
                                                <label :title="credential.desc"> {{credential.label}} </label>

                                                <!-- Instruction button on 1st tab-->
                                                <a data-direction="left" data-width="auto"
                                                   target="_blank" title="Get help with filling up this form" v-bind:href="instruction_page"
                                                   v-if="credential.name === 'api-key'">
                                                    <span>
                                                        &nbsp;<i class="fas fa-question-circle"></i>
                                                        <span>Instructions</span>
                                                    </span>
                                                </a>
                                                <input :name="credential.name" :placeholder="'Type '+credential.label"
                                                       :type="credential.type" class="form-control form-control-sm"
                                                       v-model="credential.value">
                                                <span v-if="typeof errorMessage[credential.name] !== 'undefined'" class="invalid-feedback d-block" role="alert">
                                                    <strong>{{errorMessage[credential.name][0]}}</strong>
                                                </span>
                                            </div>
                                            <div class="form-group text-center" v-if="!show_api_key_section">
                                                <div class="alert alert-info">Coming Soon</div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="setup-footer d-flex justify-content-center">
                                        <a :class="show_api_key_section ? '' : 'disabled'"
                                           @click="savePmsForm()" class="btn btn-success px-md-4"
                                           href="javascript:void(0);">Save and Continue <i
                                                class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--<loader v-show="preLoader"></loader>-->
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    import Pms_setup_steps_navbar from "./pms_setup_steps_navbar";
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';

    Vue.use(VueToast);
    export default {
        name: "pms_setup_step1",
        components: {Pms_setup_steps_navbar},

        data() {
            return {
                preLoader: true,
                msg: 'Please Wait',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                supportedPmsList: {},
                pmsForm: '',
                show_api_key_section: false,
                instruction_page: '/pmsintegration/instructions',
                errorMessage:{}
            }
        },
        methods: {

            /**
             * Get List of Supported Gateways
             */
            getUserSupportedPmsList() {
                let _this = this;
                axios.post('/client/v2/get-user-supported-pms-list')
                    .then(function (response) {
                        if (response.data.status) {
                            _this.supportedPmsList = response.data.data;
                            _this.preLoader = false;
                            _this.getSelectedPmsCredentialsForm(response.data.data[0].id);
                        }
                    }).catch(function (error) {
                });
            },

            /**
             * Generate PMS Credentials Form
             */
            getSelectedPmsCredentialsForm(pmsFormId) {

                if ((pmsFormId != null) && (pmsFormId != '')) {

                    if (pmsFormId == 1 || pmsFormId == 6) {
                        this.show_api_key_section = true;
                    } else {
                        this.show_api_key_section = false;
                    }

                    this.block = true;
                    let _this = this;
                    axios.post('/client/v2/get-pms-credential-form-along-user-saved-keys', {'pmsFormId': pmsFormId})
                        .then(function (response) {
                            if (response.data.status) {
                                _this.pmsForm = response.data.data;
                                _this.instruction_page = _this.pmsForm.instruction_page;
                            } else {
                                _this.pmsForm = '';
                                Vue.$toast.open({
                                    message: 'Error!  Fail to Load PMS Settings.',
                                    duration: 3000,
                                    type: 'error',
                                    position: 'top-right',
                                });
                            }
                            _this.block = false;
                        }).catch(function (error) {
                        console.log(error);
                        _this.pmsForm = '';
                        _this.block = false;
                        Vue.$toast.open({
                            message: 'Error!  Fail to Load PMS Settings.',
                            duration: 3000,
                            type: 'error',
                            position: 'top-right',
                        });
                    });
                }
            },

            /**
             * Save and validate PMS form keys
             */
            savePmsForm() {
                if (this.pmsForm.credentials !== undefined) {
                    this.block = true;
                    let _this = this;
                    let credentialsKeys = {};
                    $.each(_this.pmsForm.credentials, function (key, credential) {
                        credentialsKeys[credential.name] = credential.value;
                    });
                    axios.post('/client/v2/save-pms-credentials', {
                        'pmsFormId': document.querySelector('#select-box-Pms').value,
                        'credentials': credentialsKeys
                    })
                        .then(function (response) {
                            if (response.data.status) {

                                //update intercom data
                                updateIntercomData('pms_connected', {credentials:credentialsKeys, selected_pms:_this.pmsForm.pmsName});

                                Vue.$toast.open({
                                    message: ' PMS Settings Updated',
                                    duration: 3000,
                                    type: 'success',
                                    position: 'top-right',
                                });

                                setTimeout(function() {
                                    window.location.href = response.data.data.nextStepUrl;
                                }, 1000);
                            } else {
                                if (typeof response.data.message['api-key'] === 'undefined' && typeof response.data.message['username'] === 'undefined') {
                                    Vue.$toast.open({
                                        message: ' Error!  ' + response.data.message,
                                        duration: 3000,
                                        type: 'error',
                                        position: 'top-right',
                                    });
                                } else {
                                    _this.errorMessage = response.data.message;
                                }
                            }
                            _this.block = false;
                        }).catch(function (error) {
                        _this.block = false;
                        Vue.$toast.open({
                            message: 'Error!  Fail to save PMS Settings.',
                            duration: 3000,
                            type: 'error',
                            position: 'top-right',
                        });
                    });
                    //Redirect
                }
            },
        },//Methods End
        mounted() {
            this.getUserSupportedPmsList();
        },

    }
</script>

<style scoped>

</style>