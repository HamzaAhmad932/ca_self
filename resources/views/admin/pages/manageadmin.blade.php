@extends('layouts.admin')
@section('content')
<div class="m-grid__item m-grid__item--fluid m-wrapper">
    
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/adminteamcontent.admin')}}</h3>
                <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                    {{--<li class="m-nav__item m-nav__item--home">
                        <a href="{{route('dashboard')}}" class="m-nav__link m-nav__link--icon">
                            <i class="m-nav__link-icon la la-home"></i>
                        </a>
                    </li>--}}

                    <li class="m-nav__separator">-</li>

                    <li class="m-nav__item">

                            <span class="m-nav__link-text">Admin List </span>

                    </li>
                </ul>
            </div>

            {{--<div>
                <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" m-dropdown-toggle="hover" aria-expanded="true">
                    <a href="#" class="m-portlet__nav-link btn btn-lg btn-secondary  m-btn m-btn--outline-2x m-btn--air m-btn--icon m-btn--icon-only m-btn--pill  m-dropdown__toggle">
                        <i class="la la-plus m--hide"></i>
                        <i class="la la-ellipsis-h"></i>
                    </a>
                    <div class="m-dropdown__wrapper">
                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                        <div class="m-dropdown__inner">
                            <div class="m-dropdown__body">
                                <div class="m-dropdown__content">
                                    <ul class="m-nav">
                                        <li class="m-nav__section m-nav__section--first m--hide">
                                            <span class="m-nav__section-text">Quick Actions</span>
                                        </li>
                                        <li class="m-nav__item">
                                            <a href="" class="m-nav__link">
                                                <i class="m-nav__link-icon flaticon-share"></i>
                                                <span class="m-nav__link-text">Activity</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__item">
                                            <a href="" class="m-nav__link">
                                                <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                <span class="m-nav__link-text">Messages</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__item">
                                            <a href="" class="m-nav__link">
                                                <i class="m-nav__link-icon flaticon-info"></i>
                                                <span class="m-nav__link-text">FAQ</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__item">
                                            <a href="" class="m-nav__link">
                                                <i class="m-nav__link-icon flaticon-lifebuoy"></i>
                                                <span class="m-nav__link-text">Support</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator m-nav__separator--fit">
                                        </li>
                                        <li class="m-nav__item">
                                            <a href="#" class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">Submit</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>--}}

        </div>
    </div>
    <!-- END: Subheader -->
    <div class="m-content">
        
        <div class="m-portlet m-portlet--mobile">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text">
                            {{__('admin/adminteamcontent.admin_list')}}
                        </h3>
                    </div>
                </div>
                @if(Gate::check('full'))
                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                        <li class="m-portlet__nav-item">
                            <a href="#" class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air"
                               data-toggle="modal" data-target="#m_modal_2">
                                <span>
                                    <i class="la la-plus"></i>
                                    <span>{{__('admin/adminteamcontent.new_admin')}}</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif()
            </div>
            <div class="m-portlet__body">
            <!--begin: Datatable -->
            <div id="m_table_1_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                
                
                <div class="row">
                    <div class="col-sm-12" id="MemStatus">
                        <table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer dtr-inline" id="m_table_1" role="grid" aria-describedby="m_table_1_info" style="width: 1149px;">
                            <thead>
                                <tr role="row">
                                    <th class="sorting_asc" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1" style="width: 193.45px;" aria-sort="ascending" aria-label="Agent: activate to sort column descending">{{__('admin/adminteamcontent.name')}}</th>
                                    <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1" style="width: 212.45px;" aria-label="Email: activate to sort column ascending">{{__('admin/adminteamcontent.email')}}</th>
                                    <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1" style="width: 127.45px;" aria-label="Contact#: activate to sort column ascending">{{__('admin/adminteamcontent.contact')}}</th>
                                    @if(Gate::check('full'))
                                    <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 69.5px;" aria-label="Actions">{{__('admin/adminteamcontent.action')}}</th></tr>
                                    @endif()
                                </thead>

                            <tbody>
                                @if(count($data['admins']) > 0 )
                                    @foreach($data['admins'] as $admin )
                                        <tr role="row" class="odd" >
                                            <td tabindex="0" class="sorting_1">
                                                <div class="m-card-user m-card-user--sm">
                                                    <div class="m-card-user__pic">
                                                        <div class="m-card-user__no-photo m--bg-fill-info">
                                                            <span>{{ $admin->name[0] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="m-card-user__details">
                                                        <span class="m-card-user__name">{{ $admin->name }}</span>
                                                        <a href="" class="m-card-user__email m-link">{{ $admin->name }}</a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><a class="m-link" href="mailto:{{ $admin->email }}">{{ $admin->email }}</a></td>
                                            <td>{{ $admin->phone }}</td>
                                            @if(Gate::check('full'))
                                                <td nowrap="">
                                                    <span class="dropdown">
                                                        <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">
                                                            <i class="la la-ellipsis-h"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right" >
                                                            @if($admin->status == config('db_const.user.status.active.value'))
                                                                <button  class="myid dropdown-item" data-id="{{ $admin->id }}" data-status="{{ config('db_const.user.status.deactive.value') }}" data-token="{{ csrf_token() }}" @click.prevent="MemStatusbtn($event)" >
                                                                    <i class="la la-toggle-on"></i> {{ config('db_const.user.status.active.label') }}
                                                                </button>
                                                                {{--<button class="MemStatusBtn dropdown-item" data-id="{{ $admin->id }}" data-status="{{ config('db_const.user.status.deactive.value') }}" data-token="{{ csrf_token() }}" >
                                                                    <i class="la la-toggle-off"></i> {{ config('db_const.user.status.deactive.label') }}
                                                                </button>--}}
                                                            @elseif ($admin->status == config('db_const.user.status.deactive.value') )
                                                                <button class="myid dropdown-item" data-id="{{ $admin->id }}" data-status="{{ config('db_const.user.status.active.value') }}" data-token="{{ csrf_token() }}" @click.prevent="MemStatusbtn($event)" >
                                                                    <i class="la la-toggle-off"></i>{{ config('db_const.user.status.deactive.label') }}
                                                                </button>
                                                                {{--<button class="MemStatusBtn dropdown-item" data-id="{{ $admin->id }}" data-status="{{ config('db_const.user.status.active.value') }}" data-token="{{ csrf_token() }}" >
                                                                    <i class="la la-toggle-on"></i>{{ config('db_const.user.status.active.label') }}
                                                                </button>--}}
                                                            @endif
                                                            @if(auth()->user()->user_account->account_type == 4)
                                                                    <button class="MemDelBtn dropdown-item" data-id="{{ $admin->id }}" data-token="{{ csrf_token() }}" @click.prevent="MemStatusbtn($event)" >
                                                                        <i class="la la-trash"></i>Delete
                                                                    </button>
                                                            @endif
                                                        </div>
                                                    </span>
                                                    <div id="spi_{{ $admin->id }}"  class="m-loader m-loader--danger" style="width: 30px; display: none; position: absolute;"></div>
                                                    {{--<a href="memberprofile/{{ $admin->id }}" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" title="View">
                                                      <i class="la la-edit"></i>
                                                    </a>--}}
                                                </td>
                                            @endif()
                                        </tr>
                                        @endforeach
                                @else
                                    <tr role="row" class="odd" >
                                        <td colspan="4" style="text-align: center">No any member found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="m_table_1_info" role="status" aria-live="polite">Showing {{ count($data['admins']) }}</div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="m_table_1_paginate">
                                    {{ $data['admins']->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>
    <ul>
      

    @if(Gate::check('full'))
    <!--begin::Modal-->
    <div class="modal fade" id="m_modal_2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{__('admin/adminteamcontent.create_admin')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="memCvr">
                    <!--begin::Form-->
                    <form class="m-form m-form--fit" method="post" id="memForm" >

                        <div class="m-portlet__body">
                            <div class="m-form__section m-form__section--first">
                                <div class="form-group m-form__group" :class="{'has-error' : hasErrors.name}">
                                    <label for="example_input_full_name">Full Name:</label>
                                    <input id="name" name="name" class="form-control m-input" v-model="registerData.name"  >

                                    <span v-if="hasErrors.name" class="invalid-feedback" role="alert">
                                     <strong>@{{errorMessage.name}}</strong>
                                     </span>
                                </div>
                                <div class="form-group m-form__group" :class="{'has-error' : hasErrors.email}">
                                    <label>Email address:</label>
                                    <input class="form-control m-input" type="email" name="email" v-model="registerData.email"  >

                                    <span v-if="hasErrors.email" class="invalid-feedback" role="alert">
                                    <strong>@{{errorMessage.email}}</strong>
                                    </span>
                                </div>
                                <div class="form-group m-form__group"  :class="{'has-error' : hasErrors.password}">
                                    <label>Password:</label>
                                    <input type="password" name="password" class="form-control m-input" v-model="registerData.password"    >

                                    <span v-if="hasErrors.password" class="invalid-feedback" role="alert">
                                    <strong>@{{errorMessage.password}}</strong>
                                    </span>
                                </div>
                                <div class="form-group m-form__group" :class="{'has-error' : hasErrors.phone}">
                                    <label>Contact:</label>
                                    <div class="m-input-icon m-input-icon--left" >
                                        <input class="form-control m-input" type="text" name="phone" v-model="registerData.phone"  >

                                        <span class="m-input-icon__icon m-input-icon__icon--left"><span><i class="la la-phone"></i></span></span>
                                    </div>
                                    <span v-if="hasErrors.phone" class="invalid-feedback" role="alert">
                                    <strong>@{{errorMessage.phone}}</strong>
                                    </span>
                                </div>
                                <div class="m-form__group form-group">
                                    <label class="" for="roles">Roles:</label>
                                    <div class="">
                                        <select id="roles" class="form-control m-bootstrap-select m_selectpicker" multiple data-actions-box="true" name="slctr[]" v-model="registerData.slctr">
                                            @foreach($data['adminRole'] as $r)
                                                <option data-content="<span class='m-badge m-badge--warning m-badge--wide m-badge--rounded'>{{ $r->name }}</span>" value="{{ $r->id }}" >{{ $r->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="m-form__group form-group">
                                    <label class="" for="permissions">Permissions:</label>
                                    <div class="">
                                        <select id="permissions" class="form-control m-bootstrap-select m_selectpicker" multiple data-actions-box="true" name="slctp[]" v-model="registerData.slctp">
                                            @foreach($data['adminPermission'] as $p)
                                                <option data-content="<span class='m-badge m-badge--warning m-badge--wide m-badge--rounded'>{{ $p->name }}</span>" value="{{ $p->id }}" >{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="m-form__seperator m-form__seperator--dashed"></div>
                        </div>
                        <div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit">
                            <div class="m-form__actions m-form__actions--solid">
                                <button type="submit" id="_signup_submit" class="btn btn-success" @click.prevent="registerMem()">Submit</button>
                                <button type="reset" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->
    @endif()


    @endsection





    @section('ajax_script')

            <script type="application/javascript">

                var NewMem = new Vue({
                el: '#memForm',
                data(){
                    return{
                        registerData:{
                            name:'',
                            phone:'',
                            email:'',

                            password:'',
                            slctr:[],
                            slctp:[],
                            company:[],
                        },
                        hasErrors:{
                            name:false,
                            phone:false,
                            email:false,

                            password:false
                        },
                        errorMessage:{
                            name:null,
                            phone:null,
                            email:null,

                            password:null
                        }
                        //passwordMatch:null
                    }
                },
                methods:{

                    registerMem(){
                        let msg = '{{--__('admin/adminteamcontent.register_success')--}}';
                        var _this = this;
                        var vm = this.hasErrors;
                        var _vm = this.errorMessage;
                        console.error(_this.registerData);
                        return false;
                        axios.post('adminstore', _this.registerData)
                            .then(function (response) {
                                //console.log(response);
                                if(response.status == 200){
                                    toastr.success(msg);
                                    window.location.reload();
                                }

                            })
                            .catch(function (error) {
                                var errors = error.response
                                if(errors.status == 422){
                                    if(errors.data){
                                        if(errors.data.errors.name){
                                            let err = errors.data.errors
                                            vm.name = true
                                            _vm.name = Array.isArray(err.name) ? err.name[0]: err.name
                                        }

                                        if(errors.data.errors.email){
                                            let err = errors.data.errors
                                            vm.email = true
                                            _vm.email = Array.isArray(err.email) ? err.email[0]: err.email
                                        }

                                        if(errors.data.errors.password){
                                            let err = errors.data.errors
                                            vm.password = true
                                            _vm.password = Array.isArray(err.password) ? err.password[0]: err.password
                                        }
                                        if(errors.data.errors.phone){
                                            let err = errors.data.errors
                                            vm.phone = true
                                            _vm.phone = Array.isArray(err.phone) ? err.phone[0]: err.phone
                                        }
                                    }
                                }
                            });
                    }



                }

            })

                //=================================
                // Memeber status update code below
                //===============================

                var MemSt = new Vue({
                    el: '#MemStatus',
                        data:{

                            activeval: '{{ config('db_const.user.status.active.value') }}',
                            deactiveval: '{{ config('db_const.user.status.deactive.value') }}',
                            active: '{{ config('db_const.user.status.active.label') }}',
                            deactive:'{{ config('db_const.user.status.deactive.label') }}',

                            txt : '',
                            ttl : '',
                            msg : ''

                        },
                    methods:{
                        MemStatusbtn(event){
                            // console.log(event)
                            // return
                            //var test = event.target.parentElement.parentElement.parentElement.parentElement

                            let _this = this
                            let id = event.target.dataset.id
                            let st = event.target.dataset.status
                            let inrtxt = event.target.innerText
                            let th = inrtxt.trim()


                            if(th == _this.active){
                                txt = _this.deactive
                                ttl = '{{ __('client/team.deactive_confirm.title') }}';
                                msg = '{{ __('client/team.deactive_confirm.msg') }}';
                            }else if(th == _this.deactive){
                                txt = _this.active
                                ttl = '{{ __('client/team.active_confirm.title') }}';
                                msg = '{{ __('client/team.active_confirm.msg') }}';
                            }

                            if(th == _this.active || th ==  _this.deactive ){
                                swal({title:" "+ttl+" "+txt+".", text: " "+msg+".",
                                type:"warning",
                                showCancelButton:!0,
                                confirmButtonText:"Yes, "+txt+"  it!"
                            }).then(function(e){
                                if(e.value == true) {
                                    axios.post('adminstatus/' + id + '/' + st)
                                        .then((response) => {

                                            if (response.data.status == _this.deactiveval ) {
                                                let rttl = '{{ __('client/team.deactive_response.title') }}';
                                                let rmsg = '{{ __('client/team.deactive_response.msg') }}';
                                                event.target.dataset.status = _this.activeval
                                                event.target.innerHTML ='<i class="la la-toggle-off"></i> '+_this.deactive ;
                                                swal(rttl+"!", rmsg, "error")
                                            } else if (response.data.status == _this.activeval) {
                                                let rttl = '{{ __('client/team.active_response.title') }}';
                                                let rmsg = '{{ __('client/team.active_response.msg') }}';
                                                event.target.dataset.status = _this.deactiveval;
                                                event.target.innerHTML ='<i class="la la-toggle-on"></i> '+_this.active ;
                                                swal(rttl+"!", rmsg, "success")
                                            }

                                        },(error) => {
                                            //console.log("Hi I'm Error   ");
                                            // error callback
                                        })

                                }

                                }); //this is swal end ;
                            }else if(th == '{{ __('client/team.delete')}}'){
                                ttl = '{{ __('client/team.delete_confirm.title') }}';
                                msg = '{{ __('client/team.delete_confirm.msg') }}';
                                txt = '{{ __('client/team.delete')}}';
                                swal({title:" "+ttl+" "+txt+".", text: " "+msg+".",
                                    type:"warning",
                                    showCancelButton:!0,
                                    confirmButtonText:"Yes, "+txt+" it!"
                                }).then(function(e){
                                    if(e.value == true){
                                        axios.delete('admindelete/' + id)
                                            .then((response) => {

                                                if(response.status == 200){
                                                    rttl = '{{ __('client/team.delete_response.title') }}';
                                                    rmsg = '{{ __('client/team.delete_response.msg') }}';
                                                    //toastr.success("The user has been deleted!");
                                                    swal(rttl+"!", rmsg, "success")
                                                   event.target.parentElement.parentElement.parentElement.parentElement.style.display = 'none';

                                                }else{
                                                    let er = '{{__('client/team.something_wrong')}}';
                                                    toastr.error();
                                                }

                                            }, (error) => {
                                                let er = '{{__('client/team.something_wrong')}}';
                                                toastr.error();
                                            })
                                    }
                                }) //this is swal end ;
                            } //this else if end
                        }
                    }

                })

                // var id = event.target.attributes['data-id'].nodeValue
                // var st= event.target.attributes['data-status'].nodeValue

                // const test =  document.querySelector(".myid");
                //  const test1 = test.dataset.status
                // var _this = this
                // alert(_this.test1)
                // var vm = this.hasErrors
                // var _vm = this.errorMessage

                // =======================================================
                //  Member Delete request code below
                // ======================================================

            </script>
            @endsection