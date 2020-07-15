<template>
    <div>


        <div aria-hidden="true" aria-labelledby="newTeamMember" class="modal fade" id="newTeamMember" role="dialog"
             tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Invite User</h4>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                            <span aria-hidden="true"><i class="fas fa-times"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form autocomplete="off" id="invite-user-form">
                            <div class="form-group">
                                <label for="fname">First Name</label>
                                <input aria-describedby="fname" class="form-control form-control-sm" id="fname"
                                       placeholder="First Name" v-model="registerData.fname">
                                <span class="invalid-feedback" role="alert" style="display:block"
                                      v-if="hasErrors.fname">
                                    <strong>{{errorMessage.fname}}</strong>
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="lname">Last Name</label>
                                <input aria-describedby="lname" class="form-control form-control-sm" id="lname"
                                       placeholder="Last Name" v-model="registerData.lname">
                                <span class="invalid-feedback" role="alert" style="display:block"
                                      v-if="hasErrors.lname">
                                    <strong>{{errorMessage.lname}}</strong>
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input aria-describedby="email" class="form-control form-control-sm" id="email" placeholder="Email"
                                       type="email" v-model="registerData.email">
                                <span class="invalid-feedback" role="alert" style="display:block"
                                      v-if="hasErrors.email">
                                    <strong>{{errorMessage.email}}</strong>
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="slctr">Roles</label>
                                <select class="custom-select custom-select-sm" data-actions-box="true" id="slctr"
                                        multiple v-model="registerData.slctr">
                                    <option v-bind:value="role.name" v-for="role in allRoles">{{ role.name }}</option>
                                </select>
                                <span class="invalid-feedback" role="alert" style="display:block"
                                      v-if="hasErrors.slctr">
                                    <strong>{{errorMessage.slctr}}</strong>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button @click.prevent="EmptyFields()" aria-label="Cancel"
                                class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal" id="invite_team_member_modal_close_btn"
                                type="reset">Cancel
                        </button>
                        <button @click.prevent="registerMem($event)" class="btn btn-sm btn-success px-3" id="_signup_submit"
                                type="submit" v-bind:data-current_page="current_page">Send Invite
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div aria-hidden="true" aria-labelledby="editTeamMember" class="modal fade" id="editTeamMember" role="dialog"
             tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form autocomplete="off">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="card-section-title mt-2 mb-3">
                                        <h4 class="mb-0 edit-profile-page-header">USER DETAILS</h4>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 text-center mb-4">
                                            <div class="user-image">
                                                <span style="display: block;text-align: center;line-height: 2rem;"
                                                      v-if="u_initial != ''">{{u_initial}}</span>
                                                <img :src="u_img" v-else/>
                                            </div>
                                            <a class="btn btn-xs user_logo_btn" id="user_logo_btn">
                                                Upload Photo
                                                <input @change="UserLogo($event)" class="user_logo" id="user_logo" name="user_logo"
                                                       ref="user_logo"
                                                       type="file"
                                                       v-bind:data-id="memberId"/>
                                            </a>
                                            <span class="invalid-feedback" role="alert" style="display:block"
                                                  v-if="hasErrors.user_logo">
                                                <strong>{{ errorMessage.user_logo }}</strong>
                                            </span>
                                        </div>

                                        <div class="col-sm-8">
                                            <div class="form-row">
                                                <div class="form-group col-6">
                                                    <label for="_fname">First Name</label>
                                                    <input aria-describedby="_fname" class="form-control form-control-sm"
                                                           id="_fname"
                                                           placeholder="First Name"
                                                           v-model="registerData._fname">
                                                    <span class="invalid-feedback" role="alert" style="display:block"
                                                          v-if="hasErrors._fname">
                                                        <strong>{{errorMessage._fname}}</strong>
                                                    </span>
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="_lname">Last Name</label>
                                                    <input aria-describedby="_lname" class="form-control form-control-sm"
                                                           id="_lname"
                                                           placeholder="Last Name" v-model="registerData._lname">
                                                    <span class="invalid-feedback" role="alert" style="display:block"
                                                          v-if="hasErrors._lname">
                                                        <strong>{{errorMessage._lname}}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label for="_email">Email</label>
                                                    <input aria-describedby="_email" class="form-control form-control-sm" id="_email"
                                                           placeholder="Email"
                                                           type="email" v-model="registerData._email" :disabled="registerData._email_verified_at === null ? false : true">
                                                    <span class="invalid-feedback" role="alert" style="display:block"
                                                          v-if="hasErrors._email">
                                                        <strong>{{errorMessage._email}}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label>Phone</label>
                                                    <vue-tel-input :autofocus="false" inputId="_phone"
                                                                   v-bind="bindProps"
                                                                   v-model="registerData._phone"></vue-tel-input>
                                                    <span class="invalid-feedback" role="alert" style="display:block" v-if="hasErrors._phone">
                                                        <strong>{{errorMessage._phone}}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="card-section-title mt-2 mb-3">
                                        <h4 class="mb-0">USER PERMISSIONS</h4>
                                    </div>
                                    <div class="card-section-title mt-3 mb-3">Role</div>
                                    <div class="row no-gutters" style="height: auto; overflow-y: auto;">
                                        <div :key="'01'+role_index" class="col-md-6"
                                             v-for="(role, role_index) in allRoles">
                                            <div class="custom-checkbox custom-control-inline">
                                                <div class="checkbox-toggle checkbox-choice">
                                                    <input :data-current_page="current_page" :data-roleid="role.id"
                                                           :data-userid="memberId" :id="'checkbox-'+role.id"
                                                           :name="'checkbox-'+role.id" :value="role.name"
                                                           @change="editTeamMemberRole($event)" type="checkbox"
                                                           v-model="registerData._slctr"/>
                                                    <label :for="'checkbox-'+role.id" class="checkbox-label float-left"
                                                           data-off="OFF" data-on="ON">
                                                        <span class="toggle-track">
                                                            <span class="toggle-switch"></span>
                                                        </span>
                                                        <span class="toggle-title"></span>
                                                    </label>
                                                    <span class="toggle-label float-left" style="margin: 3px 0 0 5px;">{{ role.name | capitalize }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-section-title mt-1 mb-3">Access Permissions</div>
                                    <div class="row no-gutters" style="height:auto; overflow-y:auto;">
                                        <div :key="'001'+permission_index"
                                             class="col-md-6" v-for="(permission, permission_index) in allPermissions">
                                            <div class="checkbox-toggle checkbox-choice">
                                                <input :data-permissionid="permission.id" :data-userid="memberId"
                                                       :id="'checkbox-'+permission.id"
                                                       :name="'checkbox-'+permission.id" :value="permission.name"
                                                       @change="editTeamMemberPermission($event)" type="checkbox"
                                                       v-model="registerData._slctp"/>
                                                <label :for="'checkbox-'+permission.id"
                                                       class="checkbox-label float-left" data-off="OFF" data-on="ON">
                                                    <span class="toggle-track">
                                                        <span class="toggle-switch"></span>
                                                    </span>
                                                    <span class="toggle-title"></span>
                                                </label>
                                                <span class="toggle-label float-left" style="margin: 3px 0 0 5px;">{{ permission.name | capitalize }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer mt-3">
                            <button @click.prevent="MemStatusbtn($event)"
                                    class="DeleteUserBtn btn btn-sm btn-outline-danger mr-auto"
                                    name="Delete User"
                                    type="button"
                                    v-bind:accessKey="indexNumber"
                                    v-bind:data-current_page="current_page"
                                    v-bind:data-id="memberId" v-bind:data-status="indexNumber"
                                    v-bind:id="['DeleteUserBtn-' + indexNumber]">
                                Delete User
                            </button>
                            <button @click.prevent="EmptyFields()" class="btn btn-sm btn-secondary px-3"
                                    data-dismiss="modal" id="edit_team_member_modal_close_btn"
                                    type="button">Cancel
                            </button>
                            <button @click.prevent="MemEditMethod($event)"
                                    class="btn btn-sm btn-success px-3"
                                    name="Save Changes"
                                    v-bind:accessKey="indexNumber"
                                    v-bind:data-current_page="current_page"
                                    v-bind:data-id="memberId"
                                    v-bind:data-status="indexNumber" v-bind:id="['UpdateUserBtn-' + indexNumber]">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-header has-border-bottom mb-0">
                            <h1 class="page-title">Team - <span class="text-muted" id="numberOfmember"> {{ paginationResponse.total }}</span>
                            </h1>
                            <div class="booking-filter-stack">
                                <div class="form-group pr-3 mb-2 w-200 d-lg-none">
                                    <select @change.prevent="GetTeamData()" class="custom-select custom-select-sm"
                                            v-model="filters.sort.sortColumn">
                                        <option>Sort by:</option>
                                        <option value="name">Name</option>
                                        <option value="id">Roles</option>
                                        <option value="email">Email</option>
                                        <option value="phone">Phone</option>
                                    </select>
                                </div>
                                <div class="form-group pr-3 mb-2 w-200">
                                    <input @keyup.prevent="SearchTeamMember($event)" class="form-control form-control-sm"
                                           id="filter-search" placeholder="Search…" type="text"
                                           v-model="filters.search.searchStr">
                                </div>
                                <button class="btn btn-sm btn-success mb-2 px-3" data-target="#newTeamMember"
                                        data-toggle="modal">
                                    Add Team Member
                                </button>
                            </div>
                        </div>
                        <div class="page-body" id="team_member_page-body">
                            <div class="content-box">
                                <div class="table-header d-none d-lg-block">
                                    <div class="row no-gutters">
                                        <div class="col-4">
                                            <div class="table-box-check d-flex">
                                                <a :class="selectedSortOrder" @click.prevent="SelectedSortOrderChanged()" data-id="name"
                                                   id="SortOrder-name"> <span>Name</span></a>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <a data-id="id" id="SortOrder-role"> <span>Role</span></a>
                                        </div>
                                        <div class="col-2">
                                            <a data-id="email" id="SortOrder-email"> <span>Email</span></a>
                                        </div>
                                        <div class="col-2">
                                            <a data-id="phone" id="SortOrder-phone"> <span>Phone</span></a>
                                        </div>
                                        <div class="col-1">
                                            <a href="#0"><span>Action</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div :class="[team_data.status!=1 ? 'user-disconnected' : team_data.email_verified_at !== null ? 'user-connected'  : ''  ]"
                                 class="user-card team_member-card" v-bind:id="['user-de-card-da-id' + team_data.id]"
                                 v-for="(team_data, ind) in teamMembers">
                                <div class="card-pane">
                                    <div class=""><!--for-booking-list-page-only-outer-->
                                        <div class="row align-items-center for-teamMember-list-page-only">
                                            <!--for-booking-list-page-only-inner-->
                                            <div class="col-md-4 col-sm-4 col-xs-6 col-style">
                                                <div class="table-box-check d-flex">
                                                    <div class="user-info">
                                                        <div class="user-name single-line">
                                                            {{ team_data.name }}
                                                            <span class="badge badge-warning"
                                                                  v-if="team_data.email_verified_at === null">Invite Sent</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-2 col-xs-6 col-style">
                                            <span v-for="(role, role_repeat) in team_data.roles"
                                                  v-if="team_data.roles.length>0">
                                                <span v-if="role_repeat === 0"> {{ role.name }}</span><span
                                                    v-if="role_repeat > 0">, {{ role.name }}</span>
                                            </span>
                                            </div>
                                            <div class="col-md-2 col-sm-3 col-xs-4 col-style">
                                                <a :href="'mailto:'+team_data.email" class="single-line">{{
                                                    team_data.email | shortName}}</a>
                                            </div>
                                            <div class="col-md-2 col-sm-2 col-xs-4 col-style">{{ (team_data.phone == ''
                                                || team_data.phone == null ? '&nbsp -- &nbsp' : team_data.phone) }}
                                            </div>
                                            <div class="col-md-1 col-sm-1 col-xs-4 col-style">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="dropdown dropdown-sm">
                                                        <a aria-expanded="false" aria-haspopup="true"
                                                           class="btn btn-xs dropdown-toggle" data-toggle="dropdown"
                                                           href="#" role="button"
                                                           v-bind:id="['moreMenu-' + team_data.id]">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right"
                                                             v-bind:aria-labelledby="['moreMenu-' + team_data.id]">
                                                            <button @click.prevent="ResendInvite($event)"
                                                                    class="ResendInviteBtn dropdown-item"
                                                                    name="Resend Invite"
                                                                    v-bind:accessKey="ind"
                                                                    v-bind:data-id="team_data.id"
                                                                    v-bind:data-status="ind"
                                                                    v-bind:id="['ResendInviteBtn-' + ind]"
                                                                    v-if="team_data.email_verified_at === null">Resend Invite
                                                            </button>
                                                            <button @click.prevent="MemStatusbtn($event)"
                                                                    class="DeactivateBtn dropdown-item"
                                                                    name="Deactivate"
                                                                    v-bind:accessKey="ind"
                                                                    v-bind:data-current_page="current_page"
                                                                    v-bind:data-id="team_data.id"
                                                                    v-bind:data-status="2"
                                                                    v-bind:id="['DeactivateBtn-' + ind]"
                                                                    v-if="team_data.status == 1">Deactivate
                                                            </button>
                                                            <button @click.prevent="MemStatusbtn($event)"
                                                                    class="ActivateBtn dropdown-item"
                                                                    name="Activate"
                                                                    v-bind:accessKey="ind"
                                                                    v-bind:data-current_page="current_page"
                                                                    v-bind:data-id="team_data.id"
                                                                    v-bind:data-status="1"
                                                                    v-bind:id="['ActivateBtn-' + ind]" v-else-if="team_data.status == 2">
                                                                Activate
                                                            </button>
                                                            <button @click.prevent="MemDataEdit($event)"
                                                                    class="DataEditBtn dropdown-item"
                                                                    data-target="#editTeamMember"
                                                                    data-toggle="modal"
                                                                    name="Edit"
                                                                    v-bind:accessKey="ind" v-bind:data-id="team_data.id"
                                                                    v-bind:data-status="ind"
                                                                    v-bind:id="['DataEditBtn-' + ind]">Edit
                                                            </button>
                                                            <div class="dropdown-divider"></div>
                                                            <button @click.prevent="MemStatusbtn($event)"
                                                                    class="DeleteBtn text-danger dropdown-item"
                                                                    name="Delete"
                                                                    v-bind:accessKey="ind"
                                                                    v-bind:data-current_page="current_page"
                                                                    v-bind:data-id="team_data.id"
                                                                    v-bind:data-status="ind"
                                                                    v-bind:id="['DeleteBtn-' + ind]">Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a aria-expanded="false" class="card-collapse collapsed" data-toggle="collapse" name="open"
                                   role="button"
                                   v-bind:aria-controls="['user-' + team_data.id]"
                                   v-bind:data-id="team_data.id"
                                   v-bind:href="['#user-' + team_data.id]"
                                   v-bind:id="['index_' + ind]">
                                    <i @click.prevent="getActivityLogs($event)"
                                       class="fas fa-chevron-up"
                                       data-status="open"
                                       style="display:block;height:100%;line-height:22px;"
                                       v-bind:data-id="team_data.id"
                                       v-bind:id="['index' + ind]"></i>
                                </a>
                                <component :user_id="team_data.id" :user_logs="user_logs[team_data.id]"
                                           is="userLogs"></component>
                            </div>
                            <div class="user-card user-connected" v-if="paginationResponse.total == 0">
                                <div class="card-pane">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-12">
                                            No team member found.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="float: right !important;">
                            <pagination :data="paginationResponse" :limit="1"
                                        @pagination-change-page="GetTeamData"></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <BlockUI :html="html" message="Please Wait" v-if="block === true"></BlockUI>
        <BlockUI :html="html" message="Please Wait" v-if="loader.block === true"></BlockUI>

    </div>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        data() {
            return {
                registerData: {
                    fname: '',
                    lname: '',
                    email: '',
                    slctr: [],

                    user_logo: '',
                    _fname: '',
                    _lname: '',
                    _email: '',
                    _phone: '',
                    _password: '',
                    _slctr: [],
                    _slctp: [],
                    _email_verified_at: '',
                },
                hasErrors: {
                    fname: false,
                    lname: false,
                    email: false,
                    slctr: false,

                    user_logo: '',
                    _fname: false,
                    _lname: false,
                    _email: false,
                    _phone: false,
                    _password: false,
                    _slctr: false,
                    _slctp: false,
                },
                errorMessage: {
                    fname: null,
                    lname: null,
                    email: null,
                    slctr: null,

                    user_logo: null,
                    _fname: null,
                    _lname: null,
                    _email: null,
                    _phone: null,
                    _password: null,
                    _slctr: null,
                    _slctp: null,
                },

                bindProps: {
                    autocomplete: "on",
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

                paginationResponse: {},

                teamMembers: [],
                allRoles: [],

                allPermissions: [],
                transactionPermissions: [],

                indexNumber: '',
                memberId: '',

                u_initial: '',
                u_img: '/storage/uploads/user_images/no_image.png',

                checkBoxForAll: false,

                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',

                activeval: '1',
                deactiveval: '2',
                active: 'Activate',
                deactive: 'Deactivate',

                selectedSortOrder: 'selected sort-asc',

                filters: {
                    recordsPerPage: 10,
                    page: 1,
                    columns: ["id", "name", "email", "phone", "email_verified_at", "status"],
                    relations: ["roles"],
                    sort: {
                        sortOrder: "ASC",
                        sortColumn: "name",
                    },
                    constraints: [],
                    search: {
                        searchInColumn: ["id", "name", "email", "phone"],
                        searchStr: ""
                    },
                },

                log_filter: {
                    recordsPerPage: 10,
                    page: 1,
                    columns: ["id", "event", "old_values", "new_values", "created_at"],
                    relations: [],
                    sort: {
                        sortOrder: "DESC",
                        sortColumn: "id",
                    },
                    constraints: [],
                    search: {
                        searchInColumn: ["id", "event", "old_values", "new_values"],
                        searchStr: ""
                    },
                },

                current_page: 1,
            }
        },
        methods: {
            editTeamMemberPermission(event) {

                this.block = true;
                let self = this;

                let permission_id = event.target.dataset.permissionid;
                let user_id = event.target.dataset.userid;
                let status = 0;
                let permission_name = event.target.value;

                if (event.target.checked) {
                    status = 1;
                }

                let _vm = self.errorMessage;
                axios({
                    url: '/client/v2/edit-team-member-permission',
                    method: 'POST',
                    data: {
                        'permission_id': permission_id,
                        'user_id': user_id,
                        'status': status,
                        'permission_name': permission_name
                    }
                }).then(function (response) {
                    if (response.data.status_code == 200) {
                        self.allPermissions = response.data.data.all_permission;
                        self.transactionPermissions = response.data.data.transaction_permissions;
                        self.registerData._slctp = response.data.data.user_permissions;
                        self.block = false;
                        toastr.success(response.data.message);
                    } else if (response.data.status_code == 500) {
                        self.block = false;
                        toastr.error(response.data.message);
                    }
                }).catch(function (error) {
                    self.block = false;
                    console.log(error);
                });
            },
            editTeamMemberRole(event) {

                this.block = true;
                let self = this;

                let role_id = event.target.dataset.roleid;
                let user_id = event.target.dataset.userid;
                let current_page = event.target.dataset.current_page;
                let status = 0;
                let role_name = event.target.value;

                if (event.target.checked) {
                    status = 1;
                }

                let _vm = self.errorMessage;
                axios({
                    url: '/client/v2/edit-team-member-role',
                    method: 'POST',
                    data: {
                        'role_id': role_id,
                        'user_id': user_id,
                        'status': status,
                        'role_name': role_name
                    }
                }).then(function (response) {
                    if (response.data.status_code == 200) {
                        self.GetTeamData(current_page);
                        toastr.success(response.data.message);
                    } else if (response.data.status_code == 422) {
                        toastr.error(response.data.message);
                        event.target.checked = !event.target.checked;
                        self.block = false;
                    } else if (response.data.status_code == 500) {
                        self.block = false;
                        toastr.error(response.data.message);
                    }
                }).catch(function (error) {
                    self.block = false;
                    console.log(error);
                });
            },
            GetTeamData(page = 1) {
                let _this = this;
                _this.block = true;
                axios.post('/client/v2/v2team_list?page=' + page, {'filters': _this.filters})
                    .then(function (response) {
                        _this.paginationResponse = response.data.data;
                        _this.teamMembers = _this.paginationResponse.data;
                        _this.current_page = _this.paginationResponse.current_page;
                        _this.block = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        _this.block = false;
                    });
            },
            GetAllRolesAndPermissions() {
                let _this = this;
                axios({url: '/client/v2/v2GetAllRolesAndPermissions', method: 'GET',})
                    .then(function (response) {
                        _this.allRoles = response.data.all_Roles;
                        _this.allPermissions = response.data.all_permission;
                        _this.transactionPermissions = response.data.transaction_permissions;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            registerMem(event) {
                let _this = this;
                let vm = _this.hasErrors;
                _this.block = false;
                _this.errorMessage = {
                    fname: null,
                    lname: null,
                    email: null,
                    slctr: null
                };
                let _vm = _this.errorMessage;
                let current_page = event.target.dataset.current_page;
                _this.block = true;
                axios.post('v2member_create', _this.registerData)
                    .then(function (response) {
                        if (response.data.done == 1) {
                            _this.GetTeamData(current_page);
                            document.getElementById("invite_team_member_modal_close_btn").click();
                            toastr.success("Team Member successfully added.");
                        } else {
                            _this.block = false;
                            toastr.info("Some error while being processing your request. Please try again.");
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        if (errors.status !== undefined && errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.fname !== undefined) {
                                    let err = errors.data.errors;
                                    vm.fname = true;
                                    _vm.fname = Array.isArray(err.fname) ? err.fname[0] : err.fname;
                                }
                                if (errors.data.errors.lname !== undefined) {
                                    let err = errors.data.errors;
                                    vm.lname = true;
                                    _vm.lname = Array.isArray(err.lname) ? err.lname[0] : err.lname
                                }
                                if (errors.data.errors.email !== undefined) {
                                    let err = errors.data.errors;
                                    vm.email = true;
                                    _vm.email = Array.isArray(err.email) ? err.email[0] : err.email
                                }
                                if (errors.data.errors.slctr !== undefined) {
                                    let err = errors.data.errors;
                                    vm.slctr = true;
                                    _vm.slctr = Array.isArray(err.slctr) ? err.slctr[0] : err.slctr;
                                }
                            }
                            _this.block = false;
                        }
                        _this.block = false;
                    })
            },
            ResendInvite(event) {
                let _this = this;

                let id = event.target.id;
                let memberId = event.target.dataset.id;
                let st = event.target.dataset.status;
                let indexNumber = event.target.accessKey;
                let name = event.target.name;
                let inrtxt = event.target.innerText;
                let btnText = inrtxt.trim();


                swal.fire({
                    title: "Are You Sure Want To Resend Invitation?",
                    type: "question",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, Resend"
                })
                    .then(function (e) {
                        if (e.value == true) {
                            _this.block = true;
                            axios.post('v2resend_invite/' + memberId)
                                .then(function (response) {
                                    if (response.data.status === 1) {
                                        _this.block = false;
                                        toastr.success("Invitation successfully resend to team member.");
                                    } else if (response.data.status == _this.activeval) {
                                        _this.block = false;
                                        toastr.warning("Sorry can't perform this action.");
                                    }
                                })
                                .catch(function (error) {
                                    _this.block = false;
                                    toastr.info("Some error while sending invitation. Please try again.");
                                });
                        }
                    });
            },
            MemStatusbtn(event) {
                let _this = this;

                let id = event.target.id;
                let memberId = event.target.dataset.id;
                let st = event.target.dataset.status;
                let indexNumber = event.target.accessKey;
                let current_page = event.target.dataset.current_page;
                let name = event.target.name;
                let inrtxt = event.target.innerText;
                let btnText = inrtxt.trim();

                if (btnText == _this.active || btnText == _this.deactive) {
                    if (btnText == _this.active) {
                        var per = 'Activate';
                    } else if (btnText == _this.deactive) {
                        var per = 'Deactivate';
                    }
                    swal.fire({
                        title: "Are You Sure Want To " + per + " Team Member?",
                        type: "question",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, do it!"
                    })
                        .then(function (e) {
                            if (e.value == true) {
                                _this.block = true;
                                axios.post('v2memberstatus/' + memberId + '/' + st)
                                    .then(function (response) {
                                        if (response.data.status == _this.deactiveval) {
                                            //event.target.dataset.status = _this.activeval;
                                            //event.target.innerHTML = _this.active;
                                            _this.GetTeamData(current_page);
                                            toastr.success("Team member successfully deactivated.");
                                        } else if (response.data.status == _this.activeval) {
                                            //event.target.dataset.status = _this.deactiveval;
                                            //event.target.innerHTML = _this.deactive;
                                            _this.GetTeamData(current_page);
                                            toastr.success("Team member successfully activated.");
                                        }
                                    })
                                    .catch(function (error) {
                                        _this.block = false;
                                        toastr.info("Some error occurred please try again.");
                                    });
                            }
                        });
                } else if (btnText == 'Delete' || btnText == 'Delete User') {
                    swal.fire({
                        title: "Are You Sure Want To Delete Team Member?",
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonText: "Yes, Delete it!"
                    })
                        .then(function (e) {
                            if (e.value == true) {
                                _this.block = true;
                                axios.delete('soft_delete/' + memberId)
                                    .then(function (response) {
                                        if (response.data == 1) {
                                            //document.getElementById("user-de-card-da-id"+memberId).style.display = "none";
                                            //_this.teamMembers.splice(indexNumber,0);
                                            //var x = document.getElementById("numberOfmember").innerText;
                                            //document.getElementById("numberOfmember").innerHTML = x-1;
                                            //_this.paginationResponse.total = x-1;
                                            _this.GetTeamData(current_page);
                                            if (btnText == 'Delete User') {
                                                document.getElementById("edit_team_member_modal_close_btn").click();
                                            }
                                            toastr.success("Team member successfully deleted.");
                                        } else {
                                            _this.block = false;
                                            toastr.error("Some error while deleting team member.");
                                        }
                                    })
                                    .catch(function (error) {
                                        _this.block = false;
                                        toastr.info("Some error occurred please try again.");
                                    });
                            }
                        });
                }
            },
            MemDataEdit(event) {

                let _this = this;

                let id = event.target.id;
                let memberId = event.target.dataset.id;
                let st = event.target.dataset.status;
                let indexNumber = event.target.accessKey;
                let name = event.target.name;
                let inrtxt = event.target.innerText;
                let btnText = inrtxt.trim();
                _this.block = true;

                axios({url: '/client/v2/v2memberprofile/' + memberId, method: 'GET',})
                    .then(function (response) {

                        if (response.data.user_initial === undefined || response.data.user_initial == '') {
                            _this.u_img = '/storage/uploads/user_images/' + response.data.user_image;
                        } else {
                            _this.u_initial = response.data.user_initial;
                        }
                        if (response.data.member.phone != '' && response.data.member.phone != null && response.data.member.phone !== undefined) {
                            _this.registerData._phone = response.data.member.phone;
                        }

                        _this.registerData._email = response.data.member.email;
                        _this.registerData._email_verified_at = response.data.member.email_verified_at;
                        _this.registerData._slctr = response.data.userRoles;
                        _this.registerData._slctp = response.data.userPermissions;
                        _this.memberId = memberId;
                        _this.indexNumber = indexNumber;
                        let nameArray = response.data.member.name.split(' ');
                        let ArrayLength = nameArray.length;
                        let remLength = parseFloat(ArrayLength - 1);
                        _this.registerData._fname = nameArray[0];
                        let rem_array = nameArray.splice(1, remLength);
                        _this.registerData._lname = rem_array.join(' ');
                        //console.log(response.data);
                        _this.block = false;
                    })
                    .catch(function (error) {
                        _this.block = false;
                        document.getElementById("edit_team_member_modal_close_btn").click();
                        toastr.error("Some error while getting information.");
                    });
            },
            MemEditMethod() {
                let _this = this;

                let id = event.target.id;
                let memberId = event.target.dataset.id;
                let st = event.target.dataset.status;
                let indexNumber = event.target.accessKey;
                let current_page = event.target.dataset.current_page;
                let name = event.target.name;
                let inrtxt = event.target.innerText;
                let btnText = inrtxt.trim();
                _this.block = false;

                let vm = _this.hasErrors;
                _this.errorMessage = {
                    _fname: null,
                    _lname: null,
                    _email: null,
                    _phone: null,
                    _password: null,
                    _slctr: null,
                    _slctp: null,
                };
                let _vm = _this.errorMessage;
                _this.block = true;

                axios.post('v2memberupdate/' + memberId, _this.registerData)
                    .then(function (response) {
                        if (response.data.done == 1) {
                            _this.GetTeamData(current_page);
                            document.getElementById("edit_team_member_modal_close_btn").click();
                            toastr.success("Team Member record successfully update.");
                        } else {
                            _this.block = false;
                            toastr.error("Some error while Team member info successfully update.");
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors._fname) {
                                    let err = errors.data.errors;
                                    vm._fname = true;
                                    _vm._fname = Array.isArray(err._fname) ? err._fname[0] : err._fname
                                }
                                if (errors.data.errors._lname) {
                                    let err = errors.data.errors;
                                    vm._lname = true;
                                    _vm._lname = Array.isArray(err._lname) ? err._lname[0] : err._lname
                                }
                                if (errors.data.errors._email) {
                                    let err = errors.data.errors;
                                    vm._email = true;
                                    _vm._email = Array.isArray(err._email) ? err._email[0] : err._email
                                }
                                if (errors.data.errors._phone) {
                                    let err = errors.data.errors;
                                    vm._phone = true;
                                    _vm._phone = Array.isArray(err._phone) ? err._phone[0] : err._phone
                                }
                                if (errors.data.errors._password) {
                                    let err = errors.data.errors;
                                    vm._password = true;
                                    _vm._password = Array.isArray(err._password) ? err._password[0] : err._password
                                }
                                if (errors.data.errors._slctr) {
                                    let err = errors.data.errors;
                                    vm._slctr = true;
                                    _vm._slctr = Array.isArray(err._slctr) ? err._slctr[0] : err._slctr
                                }
                            }
                        }
                        _this.block = false;
                    });
            },
            getActivityLogs(e) {
                let _this = this;
                let id = e.target.id;
                let user_id = e.target.dataset.id;
                let status = e.target.dataset.status;
                if (status == 'open') {
                    e.target.dataset.status = 'close';
                    this.$store.dispatch('fetchUserLogs', {'log_filter': this.log_filter, 'user_id': user_id});
                } else {
                    e.target.dataset.status = 'open';
                }
            },
            UserLogo(event) {
                let _this = this;
                let vm = _this.hasErrors;
                _this.block = false;
                _this.errorMessage = {
                    user_logo: null,
                };
                let _vm = _this.errorMessage;
                _this.block = true;

                let id = event.target.dataset.id;
                _this.user_logo = _this.$refs.user_logo.files[0];
                let formData = new FormData();
                formData.append('user_img', _this.user_logo);
                axios.post('v2userimage/' + id, formData, {
                    headers: {'Content-Type': 'multipart/form-data'}
                })
                    .then(function (response) {
                        if (response.data.done == 0) {
                            _this.block = false;
                            toastr.info("Some error occurred, while uploading image. Please try again.");
                        } else {
                            _this.u_img = '/storage/uploads/user_images/' + response.data.done;
                            _this.block = false;
                            toastr.success("Team member's profile image successfully changed.");
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.user_img) {
                                    let err = errors.data.errors;
                                    vm.user_logo = true;
                                    _vm.user_logo = Array.isArray(err.user_img) ? err.user_img[0] : err.user_img
                                }
                            }
                            _this.block = false;
                        }
                    });
            },
            SearchTeamMember(event) {
                if (event.keyCode === 13) {
                    this.GetTeamData();
                }
            },
            SelectedSortOrderChanged(event) {
                let _this = this;

                _this.filters.sort.sortColumn = 'name';
                if (_this.selectedSortOrder === 'selected sort-desc') {
                    _this.selectedSortOrder = 'selected sort-asc';
                    _this.filters.sort.sortOrder = 'Asc';
                } else {
                    _this.selectedSortOrder = 'selected sort-desc';
                    _this.filters.sort.sortOrder = 'Desc';
                }
                _this.GetTeamData();
            },
            checkBoxForAllChanged() {
                let _this = this;
                $.each(document.getElementsByName('checkedMemberInList'), function (key, event) {
                    event.checked = _this.checkBoxForAll;
                })
            },
            EmptyFields() {
                let _this = this;
                _this.registerData = {
                    fname: '',
                    lname: '',
                    email: '',
                    slctr: [],

                    user_logo: '',
                    _fname: '',
                    _lname: '',
                    _email: '',
                    _phone: '',
                    _password: '',
                    _slctr: [],
                    _slctp: [],
                },
                    _this.hasErrors = {
                        fname: false,
                        lname: false,
                        email: false,
                        slctr: false,

                        user_logo: '',
                        _fname: false,
                        _lname: false,
                        _email: false,
                        _phone: false,
                        _password: false,
                        _slctr: false,
                        _slctp: false,
                    },
                    _this.errorMessage = {
                        fname: null,
                        lname: null,
                        email: null,
                        slctr: null,

                        user_logo: null,
                        _fname: null,
                        _lname: null,
                        _email: null,
                        _phone: null,
                        _password: null,
                        _slctr: null,
                        _slctp: null,
                    },
                    _this.u_img = '/storage/uploads/user_images/no_image.png'
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                user_id: (state) => {
                    return state.general.team_member.user_id;
                },
                user_logs: (state) => {
                    return state.general.team_member.user_logs;
                },
            })
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.replace(/([A-Z])/g, ' $1').trim();
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1);
            },
            shortName: function (value) {
                if (!value) {
                    return '';
                } else {
                    //return value.length;
                    if (value.length > 21) {
                        return value.substring(0, 18) + '...';
                    } else {
                        return value
                    }
                }
            },
        },
        mounted() {
            this.GetTeamData();
            this.GetAllRolesAndPermissions();
        },
        watch: {
            checkBoxForAll: function () {
                this.checkBoxForAllChanged();
            },
        },
    }
</script>
