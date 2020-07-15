@extends('layouts.admin')
@section('content')

    <div class="m-grid__item m-grid__item--fluid m-wrapper">

        <!-- BEGIN: Subheader -->
{{--        <div class="m-subheader ">--}}

{{--            <div class="d-flex align-items-center">--}}
{{--                <div class="mr-auto">--}}
{{--                    <h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/leftnav.report_exception_log')}}</h3>--}}
{{--                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">--}}
{{--                        <li class="m-nav__item m-nav__item--home">--}}
{{--                            <a href="#" class="m-nav__link m-nav__link--icon">--}}
{{--                                <i class="m-nav__link-icon la la-home"></i>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="m-nav__separator">-</li>--}}

{{--                        <li class="m-nav__item">--}}

{{--                            <span class="m-nav__link-text">{{__('admin/leftnav.report_exception_log')}}</span>--}}

{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </div>--}}

{{--            </div>--}}
{{--        </div>--}}
        <!-- END: Subheader -->

        <div class="m-content" style="padding-bottom: 0; margin-bottom: 0;">
            <div class="m-portlet">
                <div class="m-portlet__body">

                    <div class="m-section__content">

                        <form action="" method="get">
                            {{ csrf_field() }}

                            <div class="row">

                                <div class="col col-xl-2 col-lg-2 col-md-2 col-sm-12">
                                    <label for="input-user-id">Filter By User ID</label>
                                    <input id="input-user-id" type="number" placeholder="User ID" min="1" minlength="1" name="user_id" class="form-control">
                                </div>

                                <div class="col col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                    <label for="input-user-account-id">Filter By User Account ID</label>
                                    <input id="input-user-account-id" type="number" placeholder="User Account ID" min="1" minlength="1" name="user_account_id" class="form-control">
                                </div>

                                <div class="col col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                    <label for="input-booking-info-id">Filter By Booking Info ID</label>
                                    <input id="input-booking-info-id" type="number" placeholder="Booking Info ID" min="1" minlength="1" name="booking_info_id" class="form-control">
                                </div>

                                <div class="col col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                    <label for="input-user-id">Filter By PMS Booking ID</label>
                                    <input id="input-pms-booking-id" type="number" placeholder="PMS Booking ID" min="1" minlength="1" name="pms_booking_id" class="form-control">
                                </div>

                                <div class="col col-xl-1 col-lg-1 col-md-1 col-sm-3">
                                    <label>&nbsp;</label><br>
                                    <button type="submit" class="btn btn-sm btn-success form-control"><span class="fa fa-search"></span></button>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col col-xl-12 col-lg-12 col-md-12 col-sm-12">

                                    <style>
                                        .m-timeline-2 .m-timeline-2__items .m-timeline-2__item .m-timeline-2__item-cricle {
                                            left: 12rem;
                                        }
                                        .m-timeline-2::before{
                                            left: 13.9rem;
                                        }
                                        .m-timeline-2 .m-timeline-2__items .m-timeline-2__item .m-timeline-2__item-text {
                                            padding-left: 14rem;
                                        }

                                        .m-timeline-2 .m-timeline-2__items .m-timeline-2__item .m-timeline-2__item-time {
                                            font-size: 1rem;
                                        }
                                    </style>


                                    <div class="m-timeline-2 m--margin-top-20">
                                        <div id="exception-logs" class="m-timeline-2__items  m--padding-top-25 m--padding-bottom-30">

                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row" id="row-load-more" style="display: none;">
                                <div class="col-lg-12 col-md-12 text-lg-center text-md-center">
                                    <a href="#" class="btn btn-outline-warning" onclick="loadMore()">Load More</a>
                                </div>
                            </div>

                        </form>

                    </div>

                </div>
            </div>
        </div>


    </div>

    <div class="modal fade" id="stack-trace" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="stack-trace-title">New message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-header" id="exception-meta-info"></div>
                <div class="modal-body"><pre id="exception-stack"></pre></div>

            </div>
        </div>
    </div>

@endsection


@section('ajax_script')

    <script>

        var filterData = {user_id: 0, user_account_id: 0, booking_info_id: 0, pms_booking_id: 0, limit:10, start: 0};

        $('document').ready(function () {

            $("#input-user-id").on('input', function() {
                let query = $(this).val();

                if(query.length === 0 ) {
                    filterData.user_id = 0;
                } else {
                    filterData.user_id = query;
                }

                fetchExceptions();

            });

            $("#input-user-account-id").on('input', function() {
                let query = $(this).val();

                if(query.length === 0 ) {
                    filterData.user_account_id = 0;
                } else {
                    filterData.user_account_id = query;
                }

                fetchExceptions();

            });

            $("#input-booking-info-id").on('input', function() {
                let query = $(this).val();

                if(query.length === 0 ) {
                    filterData.booking_info_id = 0;
                } else {
                    filterData.booking_info_id = query;
                }

                fetchExceptions();

            });

            $("#input-pms-booking-id").on('input', function() {
                let query = $(this).val();

                if(query.length === 0 ) {
                    filterData.pms_booking_id = 0;
                } else {
                    filterData.pms_booking_id = query;
                }

                fetchExceptions();

            });


            fetchExceptions();

        });

        function loadMore() {
            filterData.start += 10;
            fetchExceptions();
        }

        function loadStackTrace(id) {

            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            $.ajax({
                type: 'POST',
                data: {id:id},
                url: '{{ route('report-exception-log-fetch-id') }}',
                success: function (data) {

                    $("#stack-trace-title").empty().html(data.exception.message);
                    $("#exception-stack").empty().html(data.exception.stack_trace);

                    var meta = '<div><i title="File Path/Name" class="fa fa-file-code m--font-accent"></i> '+data.exception.file+' ' +
                        '<i title="Exception Line Number" class="fa fa-list-ol m--font-accent"></i> '+data.exception.line+'</div><br>';

                    meta += data.exception.meta_data;

                    $("#exception-meta-info").empty().html(meta);
                    $("#stack-trace").modal().show();
                },
                error: function (data) {

                },
                beforeSend: function(){

                },
                complete: function () {

                }
            });

        }

        function fetchExceptions() {
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            $.ajax({
                type: 'POST',
                data: filterData,
                url: '{{ route('report-exception-log-fetch') }}',
                success: function (data) {
                    var html = '';
                    var length = data.exceptions.length;
                    for(var i = 0; i < length; i++) {
                        html += '<a href="#" onclick="loadStackTrace('+data.exceptions[i].id+')"><div class="m-timeline-2__item"><span class="m-timeline-2__item-time">'+data.exceptions[i].date+'</span>' +
                            '<div class="m-timeline-2__item-cricle"><i class="fa fa-genderless m--font-danger"></i></div>' +
                            '<div class="m-timeline-2__item-text  m--padding-top-5">'+data.exceptions[i].message+'' +
                            '<div><i class="fa fa-file-code m--font-accent"></i> '+data.exceptions[i].file+'</div>' +
                            '<div><i class="fa fa-list-ol m--font-accent"></i> '+data.exceptions[i].line+'</div>' +
                            '</div></div></a>';
                    }
                    $('#exception-logs').append(html);
                    $('#row-load-more').show('slow');
                },
                error: function (data) {

                },
                beforeSend: function(){

                },
                complete: function () {

                }
            });
        }

    </script>

@endsection