@extends('layouts.admin')
@section('content')

    <div class="m-grid__item m-grid__item--fluid m-wrapper">

        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">

            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/leftnav.report_pms_request_count')}}</h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item m-nav__item--home">
                            <a href="#" class="m-nav__link m-nav__link--icon">
                                <i class="m-nav__link-icon la la-home"></i>
                            </a>
                        </li>
                        <li class="m-nav__separator">-</li>

                        <li class="m-nav__item">

                            <span class="m-nav__link-text">{{__('admin/leftnav.report_pms_request_count')}}</span>

                        </li>
                    </ul>
                </div>

            </div>
        </div>
        <!-- END: Subheader -->

        <div class="m-content" style="padding-bottom: 0; margin-bottom: 0;">
            <div class="m-portlet">
                <div class="m-portlet__body">

                    <div class="m-section__content">

                        <form action="" method="post">
                            {{ csrf_field() }}

                            <div class="row">
                                <div class="col-md-4"><a href="#" class="btn btn-default">Clear</a></div>
                                <div class="col-md-4 text-center h3">Filters</div>
                                <div class="col-md-4 text-right"><a href="#" class="btn btn-default">Done</a></div>
                            </div> <!-- Row End -->

                            <div class="row">

                            </div>

                        </form>

                    </div>

                </div>
            </div>
        </div>


    </div>

@endsection


@section('ajax_script')

    <script>
        $('document').ready(function () {});
    </script>

@endsection