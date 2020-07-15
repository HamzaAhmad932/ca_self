@extends('layouts.admin')
@section('content')
    <style>
        [v-cloak] {
            display: none;
        }
    </style>
    <style>
        .ermsg {
            display: none;
        }

        .ovrly {
            position: relative;
            width: 50%;
        }

        .image {
            opacity: 1;
            display: block;
            width: 100%;
            height: auto;
            transition: .5s ease;
            backface-visibility: hidden;
        }

        .middle {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100%;
            width: 100%;
            opacity: 0;
            transition: .3s ease;
            background-color: #000000;
            border-radius: 100%;
            text-align: center;
            color: #ffffff;
            font-size: 38px;
        }

        .ovrly:hover .image {
            opacity: 0.3;
        }

        .ovrly:hover .middle {
            opacity: 0.5;
        }

        #propertylogo {
            display: none;
        }
    </style>

    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h3 class="m-subheader__title m-subheader__title--separator">Admin</h3>
                <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                    <li class="m-nav__item m-nav__item--home">
                        <a href="http://127.0.0.1:8000/client/dashboard" class="m-nav__link m-nav__link--icon">
                            <i class="m-nav__link-icon la la-home"></i>
                        </a>
                    </li>

                    <li class="m-nav__separator">-</li>

                    <li class="m-nav__item">

                        <span class="m-nav__link-text">{{__('admin/payment_gateway_settings.page_breadcrumb')}}</span>

                    </li>
                </ul>
            </div>

        </div>
    </div>
    <!-- END: Subheader -->

    <div class="m-content" id="General_Settings">
    <div class="m-portlet m-portlet--bordered m-portlet--unair">

        <div class="row">
            <div class="col col-12">
                <div class="m-portlet__body--no-padding">
                    <!--begin::Portlet-->
                    {{--<div class="m-portlet m-portlet--bordered m-portlet--bordered-semi m-portlet--rounded">--}}
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h3 class="m-portlet__head-text">
                                        {{__('admin/payment_gateway_settings.parent_gateway_heading')}}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="m-portlet__body" style="padding-bottom: 0;">
                            <select onchange="getCredentials(value)" name="parent-gateways-list" class="custom-select form-control" >
                                <option value="0" selected disabled>{{__('admin/payment_gateway_settings.select_gateway')}}</option>
                                @foreach($parentGateways as $gateway)
                                    <option value="{{ $gateway['id'] }}">{{ $gateway['name'] }}</option>
                                @endforeach
                            </select>


                        </div>
                    {{--</div>--}}
                    <!--end::Portlet-->
                </div>
        </div>

    </div>

        <div class="row m-portlet__body">
            <div class="col col-12 text-center">

                <button data-toggle="modal" data-target="#parent_credentials" id="pgmc" disabled class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air">
                    <span>
                        <i class="la la-key"></i>
                        <span>{{__('admin/payment_gateway_settings.manage_credentials')}}</span>
                    </span>
                </button>

                <button onclick="getListOfGatewaysFromParent()" id="pgag" disabled href="#" class="btn btn-accent m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air">
                    <span>
                        <i class="la la-download"></i>
                        <i class="la la-list"></i>
                        <span>{{__('admin/payment_gateway_settings.get_all_gateways')}}</span>
                    </span>
                </button>

            </div>
        </div>

    </div>


</div>

    <div class="m-content">
        <div class="m-portlet m-portlet--mobile ">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text">
                            Payment Gateways List
                        </h3>
                    </div>
                </div>
                {{--<div class="m-portlet__head-tools">--}}
                    {{--<ul class="m-portlet__nav">--}}
                        {{--<li class="m-portlet__nav-item">--}}
                            {{--<a href="#"--}}
                               {{--class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air"--}}
                               {{--data-toggle="modal" data-target="#m_modal_2">--}}
                                {{--<span>--}}
                                    {{--<i class="la la-plus"></i>--}}
                                    {{--<span>Payment Gateway</span>--}}
                                {{--</span>--}}
                            {{--</a>--}}
                        {{--</li>--}}

                    {{--</ul>--}}
                {{--</div>--}}
            </div>
            <div class="m-portlet__body" >
                <!--begin: table -->
                <div class="m-section">

                    <div class="m-section__content">
                        <div class="table-responsive">
                            <table class="table table-striped- table-bordered table-hover table-checkable" id="pg_forms_table">
                                <thead><tr><th>ID</th>
                                    {{--<th>logo</th>--}}
                                    <th>Name</th><th>status</th></tr></thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end: table -->
            </div>
        </div>
    </div>

    <!--begin::Modal-->
    <div class="modal fade" id="m_modal_2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Add New Payment Gateway Form</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="memCvr">
                    <!--begin::Form-->
                    <form class="m-form m-form--fit" method="post" id="pgForm">

                        <div class="m-portlet__body">
                            <div class="m-form__section m-form__section--first">
                                <div class="form-group m-form__group" :class="{'has-error' : hasErrors.name}">
                                    <label for="example_input_full_name">Name</label>
                                    <input id="name" name="name" class="form-control m-input"
                                           v-model="registerData.name">

                                    <span v-if="hasErrors.name" class="invalid-feedback" role="alert">
                                     <strong>@{{errorMessage.name}}</strong>
                                     </span>
                                </div>
                                <div class="form-group m-form__group" :class="{'has-error' : hasErrors.backend_name}">
                                    <label>Backend Name</label>
                                    <input class="form-control m-input" type="email" name="email"
                                           v-model="registerData.backend_name">

                                    <span v-if="hasErrors.backend_name" class="invalid-feedback" role="alert">
                                    <strong>@{{errorMessage.backend_name}}</strong>
                                    </span>
                                </div>

                                <div class="form-group m-form__group" :class="{'has-error' : hasErrors.status}">
                                    <label>Status</label>
                                    <input type="text" name="status" class="form-control m-input"
                                           v-model="registerData.status">

                                    <span v-if="hasErrors.status" class="invalid-feedback" role="alert">
                                    <strong>@{{errorMessage.status}}</strong>
                                    </span>
                                </div>
                                <div class="form-group m-form__group" :class="{'has-error' : hasErrors.payment_gateway_parent_id}">
                                    <label>Payment Gateway Parent Id</label>
                                    <input type="text" name="payment_gateway_parent_id" class="form-control m-input"
                                           v-model="registerData.payment_gateway_parent_id">

                                    <span v-if="hasErrors.payment_gateway_parent_id" class="invalid-feedback" role="alert">
                                    <strong>@{{errorMessage.payment_gateway_parent_id}}</strong>
                                    </span>
                                </div>
                                <div class="form-group m-form__group" :class="{'has-error' : hasErrors.gateway_form}">
                                    <label>Gateway Form</label>
                                    <div class="m-input-icon m-input-icon--left">
                                        <textarea class="form-control m-input" type="text" name="gateway_form"
                                                  v-model="registerData.gateway_form"></textarea>
                                    </div>
                                    <span v-if="hasErrors.gateway_form" class="invalid-feedback" role="alert">
                                    <strong>@{{errorMessage.gateway_form}}</strong>
                                    </span>
                                </div>


                            </div>
                            <div class="m-form__seperator m-form__seperator--dashed"></div>
                        </div>
                        <div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit">
                            <div class="m-form__actions m-form__actions--solid">
                                <button type="submit" id="_signup_submit" class="btn btn-success"
                                        @click.prevent="registerMem()">{{__('client/team.submit')}}</button>
                                <button type="reset" class="btn btn-secondary" data-dismiss="modal"
                                        aria-label="Close">{{__('client/team.cancel')}}</button>
                            </div>
                        </div>
                    </form>
                    <!--end::Form-->

                </div>

            </div>
        </div>
    </div>
    <!--end::Modal-->


    <!--begin::Modal Manage Parent Gateway credentials -->
    <div class="modal fade" id="parent_credentials" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{__('admin/payment_gateway_settings.manage_credentials')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="memCvr">
                    <!--begin::Form-->
                    <form class="m-form m-form--fit" id="parentCredentialsForm">
                        <div class="m-portlet__body">
                            <div class="m-form__section m-form__section--first">
                                <div id="credentials_fields"></div>
                            </div>
                            <div class="m-form__seperator m-form__seperator--dashed"></div>
                        </div>
                        <div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit">
                            <div class="m-form__actions m-form__actions--solid">
                                <button type="submit" id="_signup_submit" class="btn btn-success">Submit</button>
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


    <!--begin::Modal list Parent's Gateways  -->
    <div class="modal fade" id="parent_gateway_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="parent-gateway-modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="memCvr">
                    <!--begin::Datatabel-->
                    <div class="table-responsive">
                        <table class="table table-striped- table-bordered table-hover table-checkable" id="parent_gateway_datatable">
                            <thead><tr><th>Name</th><th>Action</th></tr></thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <!--end::Datatable-->
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->

@endsection
@section('ajax_script')

    <!--begin::Page Vendors Scripts -->
    <script src="{{asset('assets/vendors/custom/datatables/datatables.bundle.js')}}" type="text/javascript"></script>
    <!--end::Page Vendors Scripts -->
    <script>

        $(function () {
            var table = $('#pg_forms_table').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                dom: '<"row"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4"B><"col-sm-12 col-md-4"f>>rtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                ajax: '/admin/pgforms',
                columns: [

                    {data: 'id', name: 'id'},
                    // {data: 'logo', name: 'logo'},
                    {data: 'name', name: 'name'},
                    {data: 'status', name: 'status'}


                ],
                columnDefs: [ {
                    targets: -1, title: "Status", orderable: !1, render: function (a, t, e, n) {
                        return '<span class="m-switch m-switch--outline m-switch--icon m-switch--success">' +
                            '<label>' +
                            '<input type="checkbox"  '+(parseInt(e.status) == 1 ? "checked" : "")+'    id="st' + e.id + '"  ' +
                            'onchange="activate_deactivate(this,' + e.id + ')" ' +
                            'value="'+ e.status +'" >' +
                            '<span></span></label></span>'
                    }


                }]

            });


        });

        var selectedGatewayId = false;
        function getCredentials(value) {
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            $.ajax({
                type:'GET',
                url: '/admin/getParentCredentials',
                data:{'id':value},
                success:function(data) {

                    if(data !== false) {

                        selectedGatewayId = value;

                        var row = '';

                        data.credentials.forEach(function(element) {

                            row += '<div class="form-group m-form__group">';
                            row += '<label>'+ element.label +'</label>';
                            row += '<input class="form-control m-input" type="text" value="'+ element.value +'" name="'+ element.key +'">';
                            row += '</div>';

                        });

                        $("#credentials_fields").empty().html(row);

                    }

                },
                complete: function() {
                    $("#pgmc").attr('disabled', false);
                    $("#pgag").attr('disabled', false);
                    $("#pgag_added_on_parent").attr('disabled', false);
                    closeWaitingLoader();
                },
                beforeSend: function() {
                    openWaitingLoader();
                }
            });
        }

        $("#parentCredentialsForm").submit(function(event) {

            event.preventDefault();
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            $.ajax({
                type: 'POST',
                url: '/admin/setParentCredentials',
                data: {'id': selectedGatewayId, 'data': objectifyForm($("#parentCredentialsForm").serializeArray())},
                success: function (data) {
                    console.log(data);
                    if(data !== 0) {
                       // window.location.reload();
                        window.location.href = '/admin/paymentgateways';
                    }
                },
                complete: function () {

                }

            });

        });

        function objectifyForm(formArray) {//serialize data function

            var returnArray = {};
            for (var i = 0; i < formArray.length; i++){
                returnArray[formArray[i]['name']] = formArray[i]['value'];
            }
            return returnArray;
        }

        function getListOfGatewaysFromParent() {

            let getAllGateways = "{{__('admin/payment_gateway_settings.get_all_gateways')}}";

            $("#parent-gateway-modal-title").html(getAllGateways);

            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            $.ajax({
                type:'GET',
                url: '/admin/addGatewaysFromParent',
                data:{'id':selectedGatewayId},
                success: function(data) {
                    closeWaitingLoader();

                    if(data !== 'false') {

                        if ($.fn.DataTable.isDataTable("#parent_gateway_datatable")) {
                            $('#parent_gateway_datatable').DataTable().clear().destroy();
                        }

                        $('#parent_gateway_datatable').DataTable({
                            lengthMenu: [[5, 25, 50, -1], [5, 25, 50, "All"]],
                            data: data,
                            columns: [
                                {data: 'name'},
                                {data: 'name'}
                            ],
                            columnDefs: [ {
                                targets: -1, title: "Action", orderable: !1, render: function (a, t, e, n) {
                                    return '<button onclick="addUpdateGatewayFromParent(\'' + e.name + '\')" class="btn btn-primary btn-sm">' +
                                        '<span class="la la-plus"></span> Add / Update</button>';
                                }


                            }]
                        });

                        $("#parent_gateway_modal").modal('toggle');

                    } else {
                        toastr.error("Try again, Something went wrong!");
                    }
                },
                error: function(data) {
                    closeWaitingLoader();
                    toastr.error("Try again, Something went wrong!!!");
                },
                complete: function() {
                    closeWaitingLoader();
                },
                beforeSend: function() {
                    openWaitingLoader();
                }
            });



        }

        function addUpdateGatewayFromParent(name) {
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            $.ajax({
                type:'POST',
                url: '/admin/addUpdateGatewayFromParent',
                dataType:'json',
                data:{'name':name, 'id': selectedGatewayId},
                success:function(data) {

                    closeWaitingLoader();
                    if(data.success === 1) {
                        toastr.success(data.message);

                        setTimeout(function(){
                            window.location.href = '/admin/paymentgateways';
                        }, 2000);
                    }
                    else
                        toastr.error(data.message);

                },
                error: function(data) {
                    closeWaitingLoader();
                    toastr.error("Try again, Something went wrong!!!");
                },
                complete: function() {
                    closeWaitingLoader();
                },
                beforeSend: function() {
                    openWaitingLoader();
                    $("#parent_gateway_modal").modal('toggle');
                }
            });
        }


        //=========================================================================

        function activate_deactivate(element,pg_id){

            var status = element.checked;
            var msg = '';
            if (status == false) {
                msg = 'Processing of this property will stop';

            } else if (status == true) {
                msg = 'Processing of this property will re-start';
            }

            swal({
                title: "" + msg,
                type: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, do it!"

            }).then(function (e) {

                if (e.value == true) {
                    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});

                    $.ajax({
                        type:'POST',
                        url: '/admin/update_pg_status',
                        dataType:'json',
                        data:{'id':pg_id,'status':status},
                        success:function(data) {

                            console.log(data);
                            var cnl = pg_id
                            if(data.status==false)
                            {
                                document.querySelector('#st' + cnl).checked = false;
                                toastr.error(data.msg);

                            } else if( data.status==true ){
                                document.querySelector('#st' + cnl).checked = true;
                                toastr.success(data.msg);

                            }
                        }
                    });

                } else {

                    let cnl = pg_id
                    let box = document.querySelector('#st' + cnl).checked;
                    if (box == true) {
                        document.querySelector('#st' + cnl).checked = false;
                    } else if (box == false) {
                        document.querySelector('#st' + cnl).checked = true;
                    }
                }
            }); //this is swal end ;


        }


    </script>

    <script>

        //General_Settings
        var P_Gateway = new Vue({
            el: '#',
            data() {
                return {
                    id: ' ',
                    file: ' ',
                    logoimg: '/storage/uploads/ }}',

                }
            },

            mounted() {
            },

            methods: {

                logoBtn() {

                    this.file = this.$refs.file.files[0];
                    var _this = this;
                    //  var _vm = this.errorMessage;
                    var id = _this.id;
                    var smthng = '{{ __('admin/company_profile.somthing_wrong') }}'

                    //var test = _this.logoData.files;
                    //alert(_this.logoData.img)
                    let formData = new FormData();
                    formData.append('file', this.file);

                    axios.post('/admin/payment_gateway_logo/' + id, formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then(function (response) {
                            if (response.data.done == 0) {
                                toastr.error(smthng);

                            } else {
                                let flnm = response.data.done;

                                _this.logoimg = '/storage/uploads/payment_gateway_logo/' + flnm;

                            }


                        }).catch(function (error) {
                        var errors = error.response
                        if (errors.status == 422) {
                            if (errors.data) {


                            }
                        }
                    });
                },
          },


        });
    </script>


    <script type="application/javascript">

        var NewPG = new Vue({
            el: '#pgForm',
            data() {
                return {
                    registerData: {
                        name: '',
                        backend_name: '',
                        payment_gateway_parent_id:'',
                        gateway_form: '',

                    },
                    hasErrors: {
                        name: false,
                        backend_name: false,
                        payment_gateway_parent_id: false,
                        gateway_form: false

                    },
                    errorMessage: {
                        name: null,
                        backend_name: null,
                        payment_gateway_parent_id: null,
                        gateway_form: null

                    }
                    //passwordMatch:null
                }
            },
            methods: {
                registerMem() {

                    var _this = this;
                    var vm = this.hasErrors;
                    var _vm = this.errorMessage;
                    axios.post('/admin/newpgform', _this.registerData)
                        .then(function (response) {
                            //console.log(response);
                            if (response.data.done == 1) {
                                toastr.success(msg);
                                window.location.reload();
                            }

                        })
                        .catch(function (error) {
                            var errors = error.response
                            if (errors.status == 422) {
                                if (errors.data) {
                                    if (errors.data.errors.name) {
                                        let err = errors.data.errors
                                        vm.name = true
                                        _vm.name = Array.isArray(err.name) ? err.name[0] : err.name
                                    }

                                    if (errors.data.errors.backend_name) {
                                        let err = errors.data.errors
                                        vm.backend_name = true
                                        _vm.backend_name = Array.isArray(err.backend_name) ? err.backend_name[0] : err.backend_name
                                    }

                                    if (errors.data.errors.payment_gateway_parent_id) {
                                        let err = errors.data.payment_gateway_parent_id
                                        vm.payment_gateway_parent_id = true
                                        _vm.payment_gateway_parent_id = Array.isArray(err.payment_gateway_parent_id) ? err.payment_gateway_parent_id[0] : err.payment_gateway_parent_id
                                    }
                                    if (errors.data.errors.gateway_form) {
                                        let err = errors.data.errors
                                        vm.gateway_form = true
                                        _vm.gateway_form = Array.isArray(err.gateway_form) ? err.gateway_form[0] : err.gateway_form
                                    }
                                }
                            }
                        });
                }
            }

        });

        //=================================
        // Memeber status update code below
        //===============================

        function openWaitingLoader(){

            mApp.block("body", {
                overlayColor: "#000000",
                type: "loader",
                state: "success",
                message: "Please wait..."
            });

        }

        function closeWaitingLoader(){
            mApp.unblock("body");
        }

    </script>

@endsection