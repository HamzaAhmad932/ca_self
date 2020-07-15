@extends('layouts.admin')
@section('content')
<div class="m-grid__item m-grid__item--fluid m-wrapper">

    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">

        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/adminteamcontent.user_account')}}</h3>
                <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                    <li class="m-nav__item m-nav__item--home">
                        <a href="#" class="m-nav__link m-nav__link--icon">
                            <i class="m-nav__link-icon la la-home"></i>
                        </a>
                    </li>
                    <li class="m-nav__separator">-</li>
                    <li class="m-nav__item">
                        <a href="" class="m-nav__link">
                            <span class="m-nav__link-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="m-nav__separator">-</li>
                    <li class="m-nav__item">
                        <a href="" class="m-nav__link">
                            <span class="m-nav__link-text">User Account</span>
                        </a>
                    </li>
                    <li class="m-nav__separator">-</li>
                    <li class="m-nav__item">
                        <a href="" class="m-nav__link">
                            <span class="m-nav__link-text">User Account List </span>
                        </a>
                    </li>
                </ul>
            </div>
            <div>
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
            </div>
        </div>
    </div>
    <!-- END: Subheader -->
    <div class="m-content">
        
        <div class="m-portlet m-portlet--mobile">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text">
                            {{__('admin/adminteamcontent.user_account_list')}}
                        </h3>
                    </div>
                </div>

                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                        <li class="m-portlet__nav-item">
                            <a href="#" class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air" data-toggle="modal" data-target="#m_modal_2">
                                <span>
                                    <i class="la la-plus"></i>
                                    <span>{{__('admin/adminteamcontent.new_user_account')}}</span>
                                </span>
                            </a>
                        </li>
                   
                </ul>
            </div>
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
                                    <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1" style="width: 212.45px;" aria-label="Email: activate to sort column ascending">{{__('admin/adminteamcontent.total_transaction')}}</th>
                                    <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1" style="width: 127.45px;" aria-label="Contact#: activate to sort column ascending">{{__('admin/adminteamcontent.commission_earned')}}</th>
                                   
                                    <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1" style="width: 127.45px;" aria-label="Contact#: activate to sort column ascending">{{__('admin/adminteamcontent.account_status')}}</th>
                                    <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 69.5px;" aria-label="Actions">{{__('admin/adminteamcontent.action')}}</th></tr>

                                </thead>
                                
                                <tbody>

                             
                                @if(count($data) > 0)

                                    @for ($i = 0; $i < count($data); $i++)
                                    <tr role="row" class="odd" >
                                        <td tabindex="0" class="sorting_1">
                                            <div class="m-card-user m-card-user--sm">
                                                <div class="m-card-user__pic">
                                                    <div class="m-card-user__no-photo m--bg-fill-info"><span>{{ $data[$i]['user_account'][0] }}</span></div>
                                                </div>
                                                <div class="m-card-user__details">
                                                    <span class="m-card-user__name">{{ $data[$i]['user_account'] }}</span>
                                                    <a href="#" class="m-card-user__email m-link"> 
                                                   <span class="m-widget24__stats m--font-primary" style="font-weight:600 " > {{ $data[$i]['user_account'] }}
                                                   </span> </a>
                                                </div>
                                            </div></td>
                                            <td><a class="m-link" href="#">
                                            <span class="m-widget24__stats m--font-success" style="font-weight:600 " >
                                                {{ '15,00,25$'}}
                                            </span></a></td>
                                            
                                            <td><span
                                            class="m-widget24__stats m--font-focus" style="font-weight:600 "

                                                >{{ '35,000$' }}</span></td>
                                              
                                             
                                               <td>

@if($data[$i]['status'] == config('db_const.user.status.active.value'))

<span class="m-widget24__stats m--font-success" style="font-weight:600 "> Active  </span>

    @elseif ($data[$i]['status'] == config('db_const.user.status.deactive.value') )
    <span class="m-widget24__stats m--font-danger" style="font-weight:600 "> Deactive  </span>
@else
<span class="m-widget24__stats m--font-success" style="font-weight:600 "> Active  </span>

    @endif









                                              </td>
                                           
                                            <td nowrap=""><span class="dropdown">
                                              
                                                <div class="dropdown-menu dropdown-menu-right" >

                                        



 



  





 <button class="MemDelBtn dropdown-item" data-id="{{ $data[$i]['id']}}" data-token="{{ csrf_token() }}" @click.prevent="MemStatusbtn($event)" ><i class="la la-trash"></i>Delete</button>
                                             </div>
                                            </span>
                                                <div id="spi_{{ $data[$i]['id'] }}"  class="m-loader m-loader--danger" style="width: 30px; display: none; position: absolute;"></div>
                                            <a href="/admin/user-accounts/{{ $data[$i]['id'] }}" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" title="View">
                                                <i class="la la-eye"></i>
                                            </a></td>
                                        </tr>
                                        @endfor
                                @else
                                <p>No any member found</p>
                                @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="m_table_1_info" role="status" aria-live="polite">Showing {{ count($data) }}</div>
                            </div>
                          
                        </div>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>



    </div>
</div>


    <!--begin::Modal-->
    <div class="modal fade" id="m_modal_2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{__('admin/adminteamcontent.create_user_account')}}</h5>
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

<div id="apps">
    <ul>
        <li v-for="post in userData.data" :key="post.id">@{{ post.name }}</li>
    </ul>

    <pagination :data="userData" @pagination-change-page="getResults"></pagination>
</div>
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
                            username:'',
                            password:'',
                            slctr:[],
                            slctp:[],
                            company:[],
                        },
                        hasErrors:{
                            name:false,
                            phone:false,
                            email:false,
                            username:false,
                            password:false
                        },
                        errorMessage:{
                            name:null,
                            phone:null,
                            email:null,
                            username:null,
                            password:null
                        }
                        //passwordMatch:null
                    }
                },
                methods:{
                    registerMem(){
                        let msg = '{{__('admin/adminteamcontent.register_success')}}';
                        var _this = this;
                        var vm = this.hasErrors;
                        var _vm = this.errorMessage;
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
                                        if(errors.data.errors.username){
                                            let err = errors.data.errors
                                            vm.username = true
                                            _vm.username = Array.isArray(err.username) ? err.username[0]: err.username
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





                var MemStss = new Vue({
                    el: '#apps',

                    data() {
                        return {
                            // Our data object that holds the Laravel paginator data
                            userData: {},
                        }
                    },

                    mounted() {
                        // Fetch initial results


                        this.getResults();

                    },

                    methods: {
                        // Our method to GET results from a Laravel endpoint
                        getResults(page = 1) {
                            axios.get('/admin/pagi/?page=' + page)
                                .then(response => {
                                    this.userData = response.data;

                                });
                        }
                    }

                })






            </script>
            @endsection