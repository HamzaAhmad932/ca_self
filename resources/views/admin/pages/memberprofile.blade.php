@extends('layouts.admin')
@section('content')
    <style>
        .ermsg{
            display: none;
        }
    </style>
    <div class="m-content">
        <div class="row">
            <div class="col-xl-3 col-lg-4">
                <div class="m-portlet m-portlet--full-height  ">
                    <div class="m-portlet__body">
                        <div class="m-card-profile">
                            <div class="m-card-profile__title m--hide">
                                Your Profile
                            </div>
                            <div class="m-card-profile__pic">
                                <div class="m-card-profile__pic-wrapper">
                                    <img src="../assets/app/media/img/users/user4.jpg" alt="">
                                </div>
                            </div>
                            <div class="m-card-profile__details">
                                <span class="m-card-profile__name">{{ $member->name }}</span>
                                <a href="" class="m-card-profile__email m-link">{{ $member->email }}</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-8">
                <div class="m-portlet m-portlet--full-height m-portlet--tabs  ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-tools">
                            <ul class="nav nav-tabs m-tabs m-tabs-line   m-tabs-line--left m-tabs-line--primary"
                                role="tablist">
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link active show" data-toggle="tab"
                                       href="#m_user_profile_tab_1" role="tab" aria-selected="true">
                                        <i class="flaticon-share m--hide"></i>
                                        Update Profile
                                    </a>
                                </li>
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_user_profile_tab_2"
                                       role="tab" aria-selected="false">
                                        Messages
                                    </a>
                                </li>
                                <li class="nav-item m-tabs__item">
                                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_user_profile_tab_3"
                                       role="tab" aria-selected="false">
                                        Settings
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>
                    <div class="tab-content" id="MemUpdate">
                        <div class="tab-pane active show" id="m_user_profile_tab_1" >
                            <form  class="m-form m-form--fit m-form--label-align-right" method='post'>
                                @csrf

                                <div class="m-portlet__body">
                                    <div class="row">
                                        <div class="col-xl-10">
                                            <div class="m-form__section m-form__section--first">
                                                <div class="m-form__heading">
                                                    <h3 class="m-form__heading-title">{{ __('admin/adminteamcontent.client_details') }}</h3>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 col-form-label">Name:</label>
                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.name}">
                                                        <input type="text" name="name" class="form-control m-input"
                                                             v-model="updateData.name" >
                                                        <span v-if="hasErrors.name" class="invalid-feedback ermsg" role="alert">
                                                         <strong>@{{errorMessage.name}}</strong>
                                                         </span>
                                                    </div>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{__('admin/adminteamcontent.email')}}</label>
                                                    <div class="col-xl-9 col-lg-9">
                                                        <input type="email" name="email" class="form-control m-input"
                                                              value="{{ $member->email }}" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{__('admin/adminteamcontent.phone')}}</label>
                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.phone}">
                                                        <div class="input-group">
                                                            <div class="input-group-prepend"><span
                                                                        class="input-group-text"><i
                                                                            class="la la-phone"></i></span></div>
                                                            <input type="text" name="phone" class="form-control m-input" v-model="updateData.phone" >
                                                                   <span v-if="hasErrors.phone" class="invalid-feedback ermsg" role="alert">
                                                            <strong>@{{errorMessage.phone}}</strong>
                                                            </span>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="m-separator m-separator--dashed m-separator--lg"></div>
                                            <div class="m-form__section">
                                                <div class="m-form__heading">
                                                    <h3 class="m-form__heading-title">
                                                        {{__('admin/adminteamcontent.mailing_address')}}
                                                        <i data-toggle="m-tooltip" data-width="auto"
                                                           class="m-form__heading-help-icon flaticon-info"
                                                           title="{{__('admin/adminteamcontent.mailing_address_help')}}"></i>
                                                    </h3>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{__('admin/adminteamcontent.address')}}</label>
                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.address}">
                                                        <input type="text" name="address" class="form-control m-input" v-model="updateData.address">
                                                        <span v-if="hasErrors.address" class="invalid-feedback ermsg" role="alert">
                                                            <strong>@{{errorMessage.address}}</strong>
                                                            </span>

                                                    </div>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{__('admin/adminteamcontent.address')}}</label>
                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.address2}">
                                                        <input type="text" name="address2" class="form-control m-input" v-model="updateData.address2">
                                                        <span v-if="hasErrors.address2" class="invalid-feedback ermsg" role="alert">
                                                            <strong>@{{errorMessage.address2}}</strong>
                                                            </span>
                                                    </div>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{__('admin/adminteamcontent.city')}}</label>
                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.city}">
                                                        <input type="text" name="city" class="form-control m-input" v-model="updateData.city">
                                                        <span v-if="hasErrors.city" class="invalid-feedback ermsg" role="alert">
                                                            <strong>@{{errorMessage.city}}</strong>
                                                            </span>
                                                    </div>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{__('admin/adminteamcontent.state')}}</label>
                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.state}">
                                                        <input type="text" name="state" class="form-control m-input" v-model="updateData.state">
                                                        <span v-if="hasErrors.state" class="invalid-feedback ermsg" role="alert">
                                                            <strong>@{{errorMessage.state}}</strong>
                                                            </span>
                                                    </div>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{__('admin/adminteamcontent.country')}}</label>
                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.country}">
                                                        <input type="text" name="country" class="form-control m-input" v-model="updateData.country">
                                                        <span v-if="hasErrors.country" class="invalid-feedback ermsg" role="alert">
                                                            <strong>@{{errorMessage.country}}</strong>
                                                            </span>
                                                    </div>
                                                </div>
                                                <div class="form-group m-form__group row">
                                                    <label class="col-xl-3 col-lg-3 form-control-label">{{__('admin/adminteamcontent.website')}}</label>
                                                    <div class="col-lg-9" :class="{'has-error' : hasErrors.website}">
                                                        <input type="url" name="website" class="form-control m-input" v-model="updateData.website">
                                                        <span v-if="hasErrors.website" class="invalid-feedback ermsg" role="alert">
                                                            <strong>@{{errorMessage.website}}</strong>
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="m-separator m-separator--dashed m-separator--lg"></div>
                                            <div class="m-form__section m-form__section--first">
                                                <div class="m-form__heading">
                                                    <h3 class="m-form__heading-title">{{__('admin/adminteamcontent.account_details')}}</h3>
                                                </div>

                                                <div class="form-group m-form__group row">
                                                    <div class="col-lg-8 m-form__group-sub">
                                                        <label class="form-control-label">{{__('admin/adminteamcontent.user_name')}}</label>
                                                        <input type="text" name="username" class="form-control m-input" value="{{ $member->email }}" disabled>
                                                        <span class="m-form__help">Your username to login to your dashboard</span>
                                                    </div>
                                                    <div class="col-lg-4 m-form__group-sub">

                                                        {{--<label class="form-control-label">* Password:</label>--}}
                                                        {{--<input type="password" name="password" class="form-control m-input"  >--}}
                                                        {{--<span class="m-form__help">Please enter min 6 charcter</span>--}}

                                                    </div>
                                                </div>
                                                <div class="form-group m-form__group row">

                                                    <div class="col-lg-4 m-form__group-sub">

                                                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#m_modal_5">Change Password?</button>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>


                                <div class="m-portlet__foot m-portlet__foot--fit">
                                    <div class="m-form__actions">
                                        <div class="row">
                                            <div class="col-2">
                                            </div>
                                            <div class="col-7">
                                                <button type="submit"
                                                        class="btn btn-accent m-btn m-btn--air m-btn--custom"
                                                        @click.prevent="MemUpdateBtn()">{{__('admin/adminteamcontent.save')}}</button>
												<a href="/client/manageteam" class="btn btn-secondary m-btn m-btn--air
                                                m-btn--custom">{{__('admin/adminteamcontent.cancel')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="m_user_profile_tab_2">

                        </div>
                        <div class="tab-pane" id="m_user_profile_tab_3">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--begin::Modal-->
    <div class="modal fade" id="m_modal_5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{__('admin/adminteamcontent.new_password')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="newpass">
                <form name="passwordForm" menthod="post" >
                <div class="modal-body" >

                    <div :class="{'has-error' : hasErrors.password}">
                    <label class="form-control-label">{{__('admin/adminteamcontent.new_password')}}</label>
                    <input type="password" name="password" class="form-control m-input" v-model="passData.password"  >
                    <span class="m-form__help">{{__('admin/adminteamcontent.password_help')}}</span><br>
                    <span v-if="hasErrors.password" class="invalid-feedback" role="alert">
                       <strong>@{{errorMessage.password}}</strong>
                          </span>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="reset" id="mdlcls" class="btn btn-secondary" data-dismiss="modal">{{__('admin/adminteamcontent.cancel')}}</button>
                    <button type="submit" class="btn btn-info" @click.prevent="MemPassBtn()" >{{__('admin/adminteamcontent.submit')}}</button>
                </div>
                </form>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->
@endsection



@section('ajax_script')
    <script type="application/javascript">
        var MemProf = new Vue({
            el: '#MemUpdate',
            data(){
                return {
                    updateData: {
                        id:'{{ $member->id }}',
                        name:'{{$member->name}}',
                        phone:'{{$member->phone}}',
                        address:'{{$member->address}}',
                        address2:'{{$member->address2}}',
                        city:'{{$member->city}}',
                        state:'{{$member->state}}',
                        country:'{{$member->country}}',
                        website:'{{$member->website}}'
                    },
                    hasErrors: {
                        name: false,
                        phone: false,
                        address:false,
                        address2:false,
                        city:false,
                        state:false,
                        country:false,
                        website:false
                    },
                    errorMessage: {
                        name: null,
                        phone: null,
                        address:null,
                        address2:null,
                        city:null,
                        state:null,
                        country:null,
                        website:null
                    }
                    //passwordMatch:null
                }
            },
            methods: {
                MemUpdateBtn() {
                    var _this = this;
                    var vm = this.hasErrors;
                    var _vm = this.errorMessage;
                    var id = _this.updateData.id;
                    axios.post('/admin/adminupdate/' + id,  _this.updateData)
                        .then(function (response) {
                            //console.log(response);
                            if (response.data.done == 1) {
                                toastr.success('Profile Updated Successfully');

                                } else {
                                    toastr.error("Somthing wrong.");

                                }


                        }).catch(function (error) {
                            var errors = error.response
                            if (errors.status == 422) {
                                if (errors.data) {
                                    if (errors.data.errors.name) {
                                        let err = errors.data.errors
                                        vm.name = true
                                        _vm.name = Array.isArray(err.name) ? err.name[0] : err.name
                                    }


                                    if (errors.data.errors.phone) {
                                        let err = errors.data.errors
                                        vm.phone = true
                                        _vm.phone = Array.isArray(err.phone) ? err.phone[0] : err.phone
                                    }
                                }
                            }
                        });
                }
            }

        });
//===================================================================================================
        var MemPass = new Vue({
            el: '#newpass',
            data(){
                return {
                    passData: {
                        id:'{{ $member->id }}',
                        password:''
                    },
                    hasErrors: {
                        password: false
                    },
                    errorMessage: {
                        password: null
                    }
                    //passwordMatch:null
                }
            },
            methods: {
                MemPassBtn() {

                    var _this = this;
                    var vm = this.hasErrors;
                    var _vm = this.errorMessage;
                    var id = _this.passData.id;
                    axios.post('/admin/adminupdatepass/' + id,  _this.passData)
                        .then(function (response) {
                            //console.log(response);
                            if (response.data.done == 1) {
                                toastr.success('Password Updated Successfully');
                                    window.location.reload();
                            } else {
                                toastr.error("Somthing wrong.");


                            }


                        }).catch(function (error) {
                        var errors = error.response
                        if (errors.status == 422) {
                            if (errors.data) {

                                if (errors.data.errors.password) {
                                    let err = errors.data.errors
                                    vm.password = true
                                    _vm.password = Array.isArray(err.password) ? err.password[0] : err.password
                                }
                            }
                        }
                    });
                }
            }

        });


        //===========================================================================================

    </script>
@endsection