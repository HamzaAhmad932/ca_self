<template>
    <div>

        <div aria-hidden="true" aria-labelledby="editFullClient" class="modal fade" data-backdrop="false" id="editFullClient"
             role="dialog" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form autocomplete="off">
                        <div class="modal-body">
                            <div class="row">
                                <!-- User Details -->
                                <div class="col-sm-6 updete_full_client_profile_model_left_side">
                                    <div class="card-section-title mt-2 mb-2"><h4 class="mb-0 edit-profile-page-header">
                                        User Details</h4></div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group text-center">
                                                <div class="user-image" style="max-width:80px;height:80px">
                                                    <span style="display: block;text-align: center;line-height: 2rem;"
                                                          v-if="u_initial != ''">{{u_initial}}</span>
                                                    <img :src="u_img" v-else/>
                                                </div>
                                                <a class="btn btn-xs user_img_btn" id="user_img_btn">
                                                    Upload Photo
                                                    <input @change="UserImage($event)" class="user_img"
                                                           id="user_img"
                                                           name="user_img"
                                                           ref="user_img"
                                                           type="file"
                                                           v-bind:data-id="u_id"/>
                                                </a>
                                                <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.user_img">
                                                    <strong>{{ errorMessage.user_img }}</strong>
                                                </span>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-6">
                                                    <label for="u_name">Name</label>
                                                    <input aria-describedby="u_name" class="form-control form-control-sm" id="u_name"
                                                           placeholder="Name" type="text"
                                                           v-model="updateData.u_name">
                                                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.u_name">
                                                        <strong>{{ errorMessage.u_name }}</strong>
                                                    </span>
                                                </div>

                                                <div class="form-group col-6">
                                                    <label for="u_email">Email</label>
                                                    <input aria-describedby="u_email" class="form-control form-control-sm" disabled
                                                           id="u_email" placeholder="Email"
                                                           readonly
                                                           style="cursor: not-allowed" type="email" v-model="updateData.u_email">
                                                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.u_email">
                                                        <strong>{{errorMessage.u_email}}</strong>
                                                    </span>
                                                </div>
                                            </div>


                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label>Contact Number</label>
                                                    <vue-tel-input @country-changed="phoneNoChange($event)" inputId="u_phone"
                                                                   v-bind="bindProps"
                                                                   v-model="updateData.u_phone"></vue-tel-input>
                                                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.u_phone">
                                                        <strong>{{errorMessage.u_phone}}</strong>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label for="u_address">Address</label>
                                                    <input aria-describedby="u_address" class="form-control form-control-sm" id="u_address"
                                                           placeholder="Address" type="text"
                                                           v-model="updateData.u_address">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Company Details -->
                                <div class="col-sm-6 updete_full_client_profile_model_right_side">
                                    <div class="card-section-title mt-2 mb-2"><h4 class="mb-0 edit-profile-page-header">
                                        Company Details</h4></div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group text-center">
                                                <div class="user-image" style="max-width:80px;height:80px">
                                                    <span style="display: block;text-align: center;line-height: 2rem;"
                                                          v-if="c_initial != ''">{{c_initial}}</span>
                                                    <img :src="c_img" v-else/>
                                                </div>
                                                <a class="btn btn-xs company_img_btn" id="company_img_btn">
                                                    Upload Photo
                                                    <input @change="CompanyImage($event)" class="company_img"
                                                           id="company_img"
                                                           name="company_img"
                                                           ref="company_img"
                                                           type="file"
                                                           v-bind:data-id="c_id"/>
                                                </a>
                                                <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.company_img">
                                                    <strong>{{ errorMessage.company_img }}</strong>
                                                </span>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-6">
                                                    <label for="c_name">Company Name</label>
                                                    <input aria-describedby="c_name" class="form-control form-control-sm" id="c_name"
                                                           placeholder="Company Name" type="text"
                                                           v-model="updateData.c_name">
                                                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.c_name">
                                                        <strong>{{ errorMessage.c_name }}</strong>
                                                    </span>
                                                </div>

                                                <div class="form-group col-6">
                                                    <label for="c_email">Email</label>
                                                    <input aria-describedby="c_email" class="form-control form-control-sm" id="c_email"
                                                           placeholder="Email" type="email"
                                                           v-model="updateData.c_email">
                                                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.c_email">
                                                        <strong>{{ errorMessage.c_email }}</strong>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label>Contact Number</label>
                                                    <vue-tel-input inputId="c_phone" v-bind="bindProps"
                                                                   v-model="updateData.c_phone"></vue-tel-input>
                                                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.c_phone">
                                                        <strong>{{ errorMessage.c_phone }}</strong>
                                                    </span>
                                                </div>
                                            </div>


                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label for="c_address">Address</label>
                                                    <input aria-describedby="c_address" class="form-control form-control-sm" id="c_address"
                                                           placeholder="Address" type="text"
                                                           v-model="updateData.c_address">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer mt-3">
                            <button @click.prevent="EmptyFields()" class="btn btn-sm btn-secondary px-3" data-dismiss="modal"
                                    id="update_client_porfile_modal_close_btn" type="button">Cancel
                            </button>
                            <button @click.prevent="EditMethod($event)" class="btn btn-sm btn-success px-3" name="Save Changes"
                                    type="button"
                                    v-bind:data-id="c_id"
                                    v-bind:data-status="u_id"
                                    v-bind:id="['u_id_' + u_id]">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div aria-hidden="true" aria-labelledby="changePassword" class="modal fade" data-backdrop="false" id="changePassword"
             role="dialog" tabindex="-2">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form autocomplete="off">
                        <div class="modal-body">
                            <div class="row">

                                <!-- Offset-1 -->
                                <div class="col-lg-1 col-md-1 col-sm-1"></div>

                                <!-- Change Password -->
                                <div class="col-lg-10 col-md-10 col-sm-10">
                                    <div class="card-section-title mt-2 mb-3"><h4
                                            class="mb-0 edit-profile-page-header text-center">Change Password</h4></div>
                                    <div class="row">
                                        <div class="col-sm-12">

                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label for="old_password">Old Password</label>
                                                    <input aria-describedby="old_password" autocomplete="false" class="form-control form-control-sm"
                                                           id="old_password"
                                                           onfocus="this.removeAttribute('readonly');"
                                                           placeholder="Old Password" readonly
                                                           style="background: #fff;" type="password"
                                                           v-model="updateData.old_password">
                                                    <span class="invalid-feedback" role="alert" v-if="hasErrors.old_password">
                                                  <strong>{{ errorMessage.old_password }}</strong>
                                                </span>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label for="new_password">New Password</label>
                                                    <input aria-describedby="new_password" autocomplete="false" class="form-control form-control-sm"
                                                           id="new_password"
                                                           onfocus="this.removeAttribute('readonly');"
                                                           placeholder="New Password" readonly
                                                           style="background: #fff;" type="password"
                                                           v-model="updateData.new_password">
                                                    <span class="invalid-feedback" role="alert" v-if="hasErrors.new_password">
                                                  <strong>{{ errorMessage.new_password }}</strong>
                                                </span>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label for="new_password_confirmation">Confirm Password</label>
                                                    <input aria-describedby="new_password_confirmation" autocomplete="false"
                                                           class="form-control form-control-sm"
                                                           id="new_password_confirmation"
                                                           onfocus="this.removeAttribute('readonly');"
                                                           placeholder="Confirm Password" readonly
                                                           style="background: #fff;" type="password"
                                                           v-model="updateData.new_password_confirmation">
                                                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.new_password_confirmation">
                                                        <strong>{{ errorMessage.new_password_confirmation }}</strong>
                                                    </span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Offset-1 -->
                                <div class="col-lg-1 col-md-1 col-sm-1"></div>

                            </div>
                        </div>

                        <div class="modal-footer mt-3">
                            <button @click.prevent="EmptyFields()" class="btn btn-sm btn-secondary px-3" data-dismiss="modal"
                                    id="update_client_password_modal_close_btn" type="button">Cancel
                            </button>
                            <button @click.prevent="ChangePassword($event)" class="btn btn-sm btn-success px-3" name="Save Password"
                                    type="button">
                                Save Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div class="header-action ml-auto">
            <a class="header-action-btn" href="#" @click.prevent="toggleNotificationPanel()">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationCounts" v-if="total_notifications>0">0</span>
            </a>
            <div class="dropdown user-menu">
                <a aria-expanded="false" aria-haspopup="true" class="dropdown-toggle" data-toggle="dropdown" id="dropdownMenuButton">
                    <div class="user-avatar">
                        <span style="display:block;text-align:center;line-height:2rem;" v-if="company_initial != ''">{{company_initial}}</span>
                        <img :src="company_logo" v-else/>
                    </div>
                    
                    <span class="tooltip_wrapper tooltip_outter">
                        <span class="d-none d-sm-block d-md-block d-lg-block">{{user_account_name | short_name}}</span>
                        <div class="tooltip_for_header_company_name">
                            <span class="tooltip_text">{{ user_account_name }}</span>
                        </div>
                    </span>
                    
                </a>
                <div aria-labelledby="dropdownMenuButton" class="dropdown-menu dropdown-menu-right">
                    <button @click.prevent="EditData()" class="editFullClient dropdown-item" data-target="#editFullClient"
                            data-toggle="modal" name="Profile Update">
                        Profile Update
                    </button>
                    <button class="changePassword dropdown-item" data-target="#changePassword" data-toggle="modal"
                            name="Change Password">
                        Change Password
                    </button>
                    <a class="dropdown-item" href="/client/v2/manageteam"> Team </a>
                    <div class="dropdown-divider"></div>
                    <a @click.prevent="LogOut()" class="dropdown-item" style="cursor: pointer">Logout</a>
                </div>
            </div>
        </div>
        <loader v-show="isShowing"></loader>
        <BlockUI :html="html" message="Please Wait" v-if="block === true"></BlockUI>
    </div>
</template>

<script>

    import {mapState} from 'vuex';
    import general from "../../../store/general";
    import ba from "../../../store/ba";

    export default {
        data() {
            return {
                updateData: {
                    u_name: '',
                    u_email: '',
                    u_phone: '',
                    old_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                    u_address: '',
                    u_country: '',
                    u_state: '',
                    u_city: '',
                    u_website: '',
                    user_img: '',

                    c_name: '',
                    c_email: '',
                    c_phone: '',
                    c_address: '',
                    c_country: '',
                    c_state: '',
                    c_city: '',
                    company_img: '',
                },
                hasErrors: {
                    u_name: false,
                    u_email: false,
                    u_phone: false,
                    old_password: false,
                    new_password: false,
                    new_password_confirmation: false,
                    u_address: false,
                    u_country: false,
                    u_state: false,
                    u_city: false,
                    u_website: false,
                    user_img: false,

                    c_name: false,
                    c_email: false,
                    c_phone: false,
                    c_address: false,
                    c_country: false,
                    c_state: false,
                    c_city: false,
                    company_img: false,
                },
                errorMessage: {
                    u_name: null,
                    u_email: null,
                    u_phone: null,
                    old_password: null,
                    new_password: null,
                    new_password_confirmation: null,
                    u_address: null,
                    u_country: null,
                    u_state: null,
                    u_city: null,
                    u_website: null,
                    user_img: null,

                    c_name: null,
                    c_email: null,
                    c_phone: null,
                    c_address: null,
                    c_country: null,
                    c_state: null,
                    c_city: null,
                    company_img: null,
                },

                bindProps: {
                    autocomplete: "off",
                    autofocus: false,
                    defaultCountry: "",
                    disabled: false,
                    disabledFetchingCountry: false,
                    disabledFormatting: false,
                    dropdownOptions: {disabledDialCode: false, tabindex: 0},
                    dynamicPlaceholder: false,
                    enabledCountryCode: false,
                    enabledFlags: true,
                    ignoredCountries: [],
                    inputClasses: [],
                    inputOptions: {showDialCode: true, tabindex: 0},
                    maxLen: 18,
                    mode: "international",
                    name: "phone_input",
                    onlyCountries: [],
                    placeholder: "Enter Phone Number",
                    preferredCountries: [],
                    required: true,
                    validCharactersOnly: true,
                    wrapperClasses: [],
                },

                isShowing: false,
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',

                u_id: '',
                c_id: '',


                u_initial: '',
                u_img: '/storage/uploads/user_images/no_image.png',

                c_initial: '',
                c_img: '/storage/uploads/companylogos/no_image.png',

                user_account_name: '',
                company_initial: '',
                company_logo: '/storage/uploads/companylogos/no_image.png',
            }
        },
        methods: {
            onUpdate(data) {
                //console.log(data);
            },
            toggleNotificationPanel(){
                $('#wrapper').toggleClass('sidebar-displayed');
            },
            GetInfo() {
                let _this = this;
                axios({
                    url: '/client/v2/v2user_profile',
                    method: 'GET',
                })
                    .then(function (response) {
                        if (response.data.company_initial === undefined || response.data.company_initial == '') {
                            _this.company_logo = '/storage/uploads/companylogos/' + response.data.company_logo;
                        } else {
                            _this.company_initial = response.data.company_initial;
                        }
                        _this.user_account_name = response.data.company.name;
                    });
            },
            EditData() {
                let _this = this;
                _this.block = true;
                axios({
                    url: '/client/v2/v2user_profile',
                    method: 'GET',
                })
                    .then(function (response) {
                        //console.log(response.data);

                        _this.u_id = response.data.u_id;
                        _this.updateData.u_name = response.data.user.name;
                        _this.updateData.u_email = response.data.user.email;
                        //_this.updateData.u_phone = response.data.user.phone;
                        _this.updateData.u_address = response.data.user.address;
                        _this.updateData.u_country = response.data.user.country;
                        _this.updateData.u_state = response.data.user.state;
                        _this.updateData.u_city = response.data.user.city;

                        if (response.data.user.phone != '' && response.data.user.phone != null && response.data.user.phone !== undefined) {
                            _this.updateData.u_phone = response.data.user.phone;
                        }
                        if (response.data.user_initial === undefined || response.data.user_initial == '') {
                            _this.u_img = '/storage/uploads/user_images/' + response.data.user_image;
                        } else {
                            _this.u_initial = response.data.user_initial;
                        }

                        _this.c_id = response.data.c_id;
                        _this.updateData.c_name = response.data.company.name;
                        _this.updateData.c_email = response.data.company.email;
                        //_this.updateData.c_phone = response.data.company.contact_number;
                        _this.updateData.c_address = response.data.company.address;
                        _this.updateData.c_country = response.data.company.country;
                        _this.updateData.c_state = response.data.company.state;
                        _this.updateData.c_city = response.data.company.city;
                        if (response.data.company.contact_number != '' && response.data.company.contact_number != null && response.data.company.contact_number !== undefined) {
                            _this.updateData.c_phone = response.data.company.contact_number;
                        }
                        if (response.data.company_initial === undefined || response.data.company_initial == '') {
                            _this.c_img = '/storage/uploads/companylogos/' + response.data.company_logo;
                        } else {
                            _this.c_initial = response.data.company_initial;
                        }
                        _this.block = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        _this.block = false;
                    });
            },
            EditMethod(event) {
                let _this = this;
                let vm = _this.hasErrors;
                _this.block = false;
                _this.errorMessage = {
                    u_name: null,
                    u_email: null,
                    u_phone: null,
                    u_address: null,
                    u_country: null,
                    u_state: null,
                    u_city: null,
                    u_website: null,

                    c_name: null,
                    c_email: null,
                    c_phone: null,
                    c_address: null,
                    c_country: null,
                    c_state: null,
                    c_city: null,
                };
                let _vm = _this.errorMessage;
                _this.block = true;
                //let u_id = event.target.id;
                let u_id = event.target.dataset.status;
                let c_id = event.target.dataset.id;

                axios.post('/client/v2/v2userupdate/' + u_id + '/' + c_id, _this.updateData)
                    .then(function (response) {
                        //console.log(response);
                        _this.block = false;
                        $('#update_client_porfile_modal_close_btn').click();
                        if (response.data.status_code == 200) {
                            toastr.success(response.data.message);
                        } else {
                            toastr.error(response.data.message);
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.u_name) {
                                    let err = errors.data.errors;
                                    vm.u_name = true;
                                    _vm.u_name = Array.isArray(err.u_name) ? err.u_name[0] : err.u_name
                                }
                                if (errors.data.errors.u_phone) {
                                    let err = errors.data.errors;
                                    vm.u_phone = true;
                                    _vm.u_phone = Array.isArray(err.u_phone) ? err.u_phone[0] : err.u_phone
                                }
                                if (errors.data.errors.old_password) {
                                    let err = errors.data.errors;
                                    vm.old_password = true;
                                    _vm.old_password = Array.isArray(err.old_password) ? err.old_password[0] : err.old_password
                                }
                                if (errors.data.errors.new_password) {
                                    let err = errors.data.errors;
                                    vm.new_password = true;
                                    _vm.new_password = Array.isArray(err.new_password) ? err.new_password[0] : err.new_password
                                }
                                if (errors.data.errors.new_password_confirmation) {
                                    let err = errors.data.errors;
                                    vm.new_password_confirmation = true;
                                    _vm.new_password_confirmation = Array.isArray(err.new_password_confirmation) ? err.new_password_confirmation[0] : err.new_password_confirmation
                                }
                                if (errors.data.errors.c_name) {
                                    let err = errors.data.errors;
                                    vm.c_name = true;
                                    _vm.c_name = Array.isArray(err.c_name) ? err.c_name[0] : err.c_name
                                }
                                if (errors.data.errors.c_email) {
                                    let err = errors.data.errors;
                                    vm.c_email = true;
                                    _vm.c_email = Array.isArray(err.c_email) ? err.c_email[0] : err.c_email
                                }
                                if (errors.data.errors.c_phone) {
                                    let err = errors.data.errors;
                                    vm.c_phone = true;
                                    _vm.c_phone = Array.isArray(err.c_phone) ? err.c_phone[0] : err.c_phone
                                }
                            }
                        }
                        _this.block = false;
                    });
            },
            UserImage(event) {
                let _this = this;
                let vm = _this.hasErrors;
                _this.block = false;
                _this.errorMessage = {
                    user_img: null,
                };
                let _vm = _this.errorMessage;
                _this.block = true;
                let id = event.target.dataset.id;
                _this.user_img = _this.$refs.user_img.files[0];
                let formData = new FormData();
                formData.append('user_img', _this.user_img);
                axios.post('/client/v2/v2userimage/' + id, formData, {
                    headers: {'Content-Type': 'multipart/form-data'}
                })
                    .then(function (response) {
                        if (response.data.done == 0) {
                            _this.block = false;
                            toastr.info("Some error occurred, while uploading image. Please try again.");
                        } else {
                            _this.u_img = '/storage/uploads/user_images/' + response.data.done;
                            _this.block = false;
                            toastr.success("Profile image successfully changed.");
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.user_img) {
                                    let err = errors.data.errors;
                                    vm.user_img = true;
                                    _vm.user_img = Array.isArray(err.user_img) ? err.user_img[0] : err.user_img
                                }
                            }
                            _this.block = false;
                        }
                    });
            },
            CompanyImage(event) {
                let _this = this;
                let vm = _this.hasErrors;
                _this.block = false;
                _this.errorMessage = {
                    company_img: null,
                };
                let _vm = _this.errorMessage;
                _this.block = true;
                let id = event.target.dataset.id;
                _this.company_img = _this.$refs.company_img.files[0];
                let formData = new FormData();
                formData.append('company_img', _this.company_img);
                axios.post('/client/v2/v2companylogo/' + id, formData, {
                    headers: {'Content-Type': 'multipart/form-data'}
                })
                    .then(function (response) {
                        if (response.data.done == 0) {
                            _this.block = false;
                            toastr.info("Some error occurred, while uploading company logo. Please try again.");
                        } else {
                            _this.c_img = '/storage/uploads/companylogos/' + response.data.done;
                            _this.block = false;
                            toastr.success("Company logo successfully changed.");
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.company_img) {
                                    let err = errors.data.errors;
                                    vm.company_img = true;
                                    _vm.company_img = Array.isArray(err.company_img) ? err.company_img[0] : err.company_img
                                }
                            }
                            _this.block = false;
                        }
                    });
            },
            LogOut() {
                axios({
                    url: '/logout',
                    method: 'POST',
                    dataType: 'JSON',
                    headers: {
                        'application': 'application/json',
                        'content-type': 'application/json'
                    },
                    data: {'_token': myToken.csrfToken}
                })
                    .then(function (response) {
                        //location.reload();
                        window.location.href = '/logout';
                    })
                    .catch(function (error) {
                        //location.reload();
                        window.location.href = '/logout';
                    });
            },
            ChangePassword() {
                let _this = this;
                let vm = _this.hasErrors;
                _this.block = false;
                _this.errorMessage = {
                    old_password: null,
                    new_password: null,
                    new_password_confirmation: null,
                };
                let _vm = _this.errorMessage;
                _this.block = true;

                axios.post('/client/v2/v2ChangePassword', _this.updateData)
                    .then(function (response) {
                        if (response.data.status_code == 200) {
                            _this.block = false;
                            $('#update_client_password_modal_close_btn').click();
                            toastr.success(response.data.message);
                        } else if (response.data.status_code == 422) {
                            _this.block = false;
                            vm.old_password = true;
                            _vm.old_password = response.data.message;
                        } else {
                            _this.block = false;
                            $('#update_client_password_modal_close_btn').click();
                            toastr.error(response.data.message);
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.old_password) {
                                    let err = errors.data.errors;
                                    vm.old_password = true;
                                    _vm.old_password = Array.isArray(err.old_password) ? err.old_password[0] : err.old_password
                                }
                                if (errors.data.errors.new_password) {
                                    let err = errors.data.errors;
                                    vm.new_password = true;
                                    _vm.new_password = Array.isArray(err.new_password) ? err.new_password[0] : err.new_password
                                }
                                if (errors.data.errors.new_password_confirmation) {
                                    let err = errors.data.errors;
                                    vm.new_password_confirmation = true;
                                    _vm.new_password_confirmation = Array.isArray(err.new_password_confirmation) ? err.new_password_confirmation[0] : err.new_password_confirmation
                                }
                            }
                        }
                        _this.block = false;
                    });
            },
            phoneNoChange(country) {

                /*let _this = this;
                var input = document.getElementById("u_phone");
                input.selectionStart = input.selectionEnd = input.value.length;*/

                /*
                var originalValue = input.value;
                input.value='';
                input.value=originalValue;
                input.focus();*/

                //console.log(input.value);

                //_this.updateData.u_phone=_this.updateData.u_phone;

            },
            EmptyFields() {
                let _this = this;

                _this.updateData = {
                    u_name: '',
                    u_email: '',
                    u_phone: '',
                    old_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                    u_address: '',
                    u_country: '',
                    u_state: '',
                    u_city: '',
                    u_website: '',
                    user_img: '',

                    c_name: '',
                    c_email: '',
                    c_phone: '',
                    c_address: '',
                    c_country: '',
                    c_state: '',
                    c_city: '',
                    company_img: '',
                };
                _this.hasErrors = {
                    u_name: false,
                    u_email: false,
                    u_phone: false,
                    old_password: false,
                    new_password: false,
                    new_password_confirmation: false,
                    u_address: false,
                    u_country: false,
                    u_state: false,
                    u_city: false,
                    u_website: false,
                    user_img: false,

                    c_name: false,
                    c_email: false,
                    c_phone: false,
                    c_address: false,
                    c_country: false,
                    c_state: false,
                    c_city: false,
                    company_img: false,
                };
                _this.errorMessage = {
                    u_name: null,
                    u_email: null,
                    u_phone: null,
                    old_password: null,
                    new_password: null,
                    new_password_confirmation: null,
                    u_address: null,
                    u_country: null,
                    u_state: null,
                    u_city: null,
                    u_website: null,
                    user_img: null,

                    c_name: null,
                    c_email: null,
                    c_phone: null,
                    c_address: null,
                    c_country: null,
                    c_state: null,
                    c_city: null,
                    company_img: null,
                };
                _this.u_id = '';
                _this.c_id = '';
                _this.u_img = '';
                _this.c_img = '';
            }
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.replace(/([A-Z])/g, ' $1').trim();
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1);
            },
            short_name: function (value) {
                if (!value) return '';
                if (value.length>12){ value = value.substring(0,10)+" ..."};
                return value;
            }
        },
        mounted() {
            this.GetInfo();
        },
        computed: {
            ...mapState({
                total_notifications: (state) => {
                    return state.general.notification.total_available_notifications;
                }
            })
        }
    }
</script>
