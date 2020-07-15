<template>
    <div :aria-labelledby="calling_id" :id="calling_id" aria-hidden="true" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Admin</h4>
                    <button aria-label="Close" class="close" id="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">
                    <form class="m-form m-form--fit" method="post" id="memForm" >

                        <div class="m-portlet__body">
                            <div class="m-form__section m-form__section--first">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="name">Full Name:</label>
                                        <input id="name" name="name" class="form-control m-input" v-model="registerData.name">
                                        <span v-if="hasErrors.name" class="invalid-feedback" role="alert"><strong>{{errorMessage.name}}</strong></span>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Email address:</label>
                                        <input class="form-control m-input" type="email" name="email" v-model="registerData.email" autocomplete="off">
                                        <span v-if="hasErrors.email" class="invalid-feedback" role="alert"><strong>{{errorMessage.email}}</strong></span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Password:</label>
                                        <input type="password" name="password" class="form-control m-input" v-model="registerData.password" autocomplete="off">
                                        <span v-if="hasErrors.password" class="invalid-feedback" role="alert"><strong>{{errorMessage.password}}</strong></span>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Contact:</label>
                                        <div class="m-input-icon m-input-icon--left" >
                                            <input class="form-control m-input" type="text" name="phone" v-model="registerData.phone">
                                            <span class="m-input-icon__icon m-input-icon__icon--left"><span><i class="la la-phone"></i></span></span>
                                        </div>
                                        <span v-if="hasErrors.phone" class="invalid-feedback" role="alert"><strong>{{errorMessage.phone}}</strong></span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label class="" for="admin_role">Roles:</label>
                                        <select id="admin_role" class="form-control" name="admin_role" v-model="registerData.admin_role">
                                            <option value="" selected>Select Role</option>
                                            <option v-for="admin_role in admin_roles" :value="admin_role.name">{{admin_role.name}}</option>
                                        </select>
                                        <span v-if="hasErrors.admin_role" class="invalid-feedback" role="alert"><strong>{{errorMessage.admin_role}}</strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="m-form__seperator m-form__seperator--dashed"></div>
                        </div>
                        <div class="modal-footer text-right">
                            <button type="submit" id="submit" class="btn btn-primary" @click.prevent="addNewAdmin()">Submit</button>
                            <button type="reset" id="cancel" class="btn btn-danger" data-dismiss="modal" aria-label="Close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script scoped>
    import {mapState} from 'vuex';

    export default {
        props: ['calling_id'],
        mounted() {
            this.getAdminRoles();
        },
        data() {
            return {
                show_model: false,
                admin_roles: [],
                registerData:{
                    name: '',
                    phone: '',
                    email: '',
                    password: '',
                    admin_role: '',
                },
                hasErrors:{
                    name:false,
                    phone:false,
                    email:false,
                    password:false,
                    admin_role:false,
                },
                errorMessage:{
                    name:null,
                    phone:null,
                    email:null,
                    password:null,
                    admin_role:false,
                }
            }
        },
        methods: {
            getAdminRoles(){
                var self = this;
                axios.get('/admin/get-admin-roles')
                    .then(function (response) {
                        if(response.status == 200){
                            self.admin_roles = response.data.data;
                        }
                    })
                    .catch(function (error) {
                        var errors = error.response;
                    });
            },
            addNewAdmin() {
                var self = this;
                axios.post('/admin/save-admin', self.registerData)
                .then((response) => {
                    if(response.status == 200){
                        toastr.success(response.data.message);
                        this.$parent.getAdmins();
                        $('#cancel').click();
                    }

                })
                .catch((error) => {
                    var errors = error.response
                    if(errors.status == 422){
                        if(errors.data){
                            if(errors.data.errors.name){
                                let err = errors.data.errors
                                self.hasErrors.name = true
                                self.errorMessage.name = Array.isArray(err.name) ? err.name[0]: err.name
                            }

                            if(errors.data.errors.email){
                                let err = errors.data.errors
                                self.hasErrors.email = true
                                self.errorMessage.email = Array.isArray(err.email) ? err.email[0]: err.email
                            }

                            if(errors.data.errors.password){
                                let err = errors.data.errors
                                self.hasErrors.password = true
                                self.errorMessage.password = Array.isArray(err.password) ? err.password[0]: err.password
                            }
                            if(errors.data.errors.phone){
                                let err = errors.data.errors
                                self.hasErrors.phone = true
                                self.errorMessage.phone = Array.isArray(err.phone) ? err.phone[0]: err.phone
                            }
                            if(errors.data.errors.admin_role){
                                let err = errors.data.errors
                                self.hasErrors.admin_role = true
                                self.errorMessage.admin_role = Array.isArray(err.admin_role) ? err.admin_role[0]: err.admin_role
                            }
                        }
                    }
                });
            },
        },
        computed: {
            //
        },
        watch: {
            //
        },
    }

</script>
<style scoped>

</style>
