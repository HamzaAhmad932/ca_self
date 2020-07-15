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

                                <span class="m-nav__link-text">User Account List </span>

                        </li>
                    </ul>
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
                    {{--@if(Gate::check('full'))
                    <div class="m-portlet__head-tools">
                        <ul class="m-portlet__nav">
                            <li class="m-portlet__nav-item">
                                <a href="#"
                                   class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air"
                                   data-toggle="modal" data-target="#m_modal_2">
                                <span>
                                    <i class="la la-plus"></i>
                                    <span>{{__('admin/adminteamcontent.new_user_account')}}</span>
                                </span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endif()--}}
                </div>
                <div class="m-portlet__body">
                    <!--begin: Datatable -->
                    <div class="m-section">

                        <div class="m-section__content">
                            <div class="table-responsive">
                                <table class="table table-striped- table-bordered table-hover table-checkable" id="companies_list_table">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Company</th>
                                        <th>Contact</th>
                                        <th>Properties</th>
                                        <th>Amounts</th>
                                        <th>Team Members</th>
                                        <th>Account Status</th>
                                        <th>Setup Status</th>
                                        <th>Created At</th>
                                    </tr>
                                    </thead>


                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>


    </div>
    <!--begin::Modal-->

    <div class="modal fade" id="m_modal_5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="assignto" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Assign</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="cid" name="cid" value="0" @change="getAllBillingPlans()">
                        <div class="form-row">
                            <div class=" form-group col-md-12">
                            <div class="form-group1 m-form__group--inline" v-for="plan in plans">
                                <div class="m-demo" style="padding: 20px">
                                <div class="m-radio-inline" >
                                    <label class="m-checkbox m-checkbox--solid m-checkbox--success">
                                        <input type="checkbox" v-model="plan.alreadySubscribed" >
                                        <b>@{{plan.nickname}}</b> <small>( @{{plan.usage_type}} - @{{plan.id}})</small>
                                        <span></span>
                                    </label>
                                </div>
                                <div class="m-input-group--fixed-small" >
                                    <b> QTY </b> <input type="number" min="1" v-model="plan.quantity"  :disabled="plan.usage_type !== 'licensed'">
                                </div>
                            </div>
                            </div>
                        </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" @click="subscribePlan()">Save</button>
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

        $('#m_modal_5').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var cid = button.data('id'); // Extract info from data-* attributes

            var cname = button.data('cname'); // Extract info from data-* attributes
            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this);
            modal.find('.modal-title').text('Assign to ' + cname);
            modal.find('.modal-body input#cid').val(cid);
        });

        $(function () {
            $('#companies_list_table').DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                dom: 'Bflrtip',
                buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                ajax: "{{ route('companies_data') }}",
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'company', name: 'company'},
                    {data: 'contact', name: 'contact'},
                    {data: 'properties', name: 'properties'},
                    {data: 'amounts', name: 'amounts'},
                    {data: 'total_team_members', name: 'total_team_members'},
                    {data: 'status', name: 'status'},
                    {data: 'integration_status', name: 'integration_status'},
                    {data: 'created_at', name: 'created_at'},


                    // {
                    //     mRender: function (data, type, row) {
                    //         return '<a href="/admin/test' + row.id + '" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" title="View"><i class="la la-eye"></i></a>'
                    //     }
                    // }


                ],
                columnDefs: [
                    @php
                        if(Gate::check('full')) {
                    @endphp
                     {

             targets: -1, title: "Action", orderable: !1, render: function (a, e, t, n) {
                 var dropdownAction = '<span class="dropdown">' +
                     '<a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">' +
                     '<i class="la la-ellipsis-h"></i></a>' +
                     '<div class="dropdown-menu dropdown-menu-right">' +
                     '<a class="dropdown-item" href="/admin/bookings/' + t.id + '"><i class="la la-edit"></i> Bookings </a>' +
                     '<a class="dropdown-item" href="/admin/account_users/' + t.id + '"><i class="la la-user"></i> Users </a>' +
                     '<a class="dropdown-item" href="/admin/properties/' + t.id + '"><i class="la la-link"></i> Properties </a>'+
                     '<a class="dropdown-item" href="javaScript:void(0)" onclick="checkVerification(' + t.id + ')"><i class="la la-user"></i> Verify User </a>';

                 <?php

                    try {
                     if(auth()->user()->user_account->account_type == 4) {
                         ?>
                         dropdownAction += '<a href="/admin/viewCompany/' + t.id + '" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" ><i class="la la-dashboard"></i></a>';
                         <?php
                         }
                 } catch (\Exception | \Symfony\Component\Debug\Exception\FatalThrowableError | \Illuminate\Contracts\Encryption\DecryptException $e) {
                             }
                 ?>

                 return dropdownAction;

                    }
                    }
                    @php
                     }
                    @endphp
                    ]

            });

        });

        function checkVerification(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to be verify this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then((result) => {
                if (result.value) {
                    swal({
                        title: 'Please Wait..!',
                        text: 'Is working..',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        onOpen: () => {
                            swal.showLoading()
                        }
                    });

                    axios({
                        url : "{{route('verifyAPIKey')}}",
                        method : 'POST',
                        data: {id:id},
                    }).then(resp => {
                        swal.hideLoading();
                        if (resp.data.status_code == 200) {
                            Swal.fire(
                                'Verified!',
                                resp.data.message,
                                'success'
                            )
                        } else if (resp.data.status_code == 404) {
                            Swal.fire(
                                'Error!',
                                resp.data.message,
                                'error'
                            )
                        }

                    });


                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                    Swal.fire(
                        'Cancelled',
                        'Your imaginary file is safe :)',
                        'error'
                    )
                }
            })
        }

        var NewPlan = new Vue({
            el: '#assignto',
            data() {
                return {
                    plans : {},
                }
            },
            methods: {
                getAllBillingPlans() {
                    let _this  = this;
                    _this.block = true;
                    let userAccountId = document.querySelector('#cid').value;

                    axios.post('/admin/get-all-billing-plans-with-user-subscribed-plans/', {'userAccountId'  : userAccountId})
                        .then(function (response) {
                            if (response.data.status) {
                                _this.plans = response.data.data;

                            } else {
                                _this.plans = {};
                                toastr.error(response.data.message);
                            }
                        }).catch(function (error) {
                        console.log(error);
                    });
                },
                subscribePlan(){

                },

                // assign_plan() {
                //
                //     var _this = this;
                //      alert(volumePlanData.type)
                //     axios.post('#', _this.registerData)
                //         .then(function (response) {
                //             //console.log(response);
                //             if (response.data.done == 1) {
                //                 toastr.success(msg);
                //                 window.location.reload();
                //             }
                //
                //         })
                //         .catch(function (error) {
                //             var errors = error.response
                //             if (errors.status == 422) {
                //                 if (errors.data) {
                //                     if (errors.data.errors.name) {
                //                         let err = errors.data.errors
                //                         vm.name = true
                //                         _vm.name = Array.isArray(err.name) ? err.name[0] : err.name
                //                     }
                //
                //
                //                 }
                //             }
                //         });
                // }
            },
            watch: {
                userAccountId:function () {
                    this.getAllBillingPlans();
                },
            },
            mounted(){

            }

        })

    </script>

@endsection