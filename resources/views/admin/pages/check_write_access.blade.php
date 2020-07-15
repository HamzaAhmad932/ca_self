@extends('layouts.admin')
@section('content')
    <div class="m-grid__item m-grid__item--fluid m-wrapper" id="test_booking">

        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">

            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/leftnav.test_booking')}}</h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item m-nav__item--home">
                            <a href="#" class="m-nav__link m-nav__link--icon">
                                <i class="m-nav__link-icon la la-home"></i>
                            </a>
                        </li>
                        <li class="m-nav__separator">-</li>

                        <li class="m-nav__item">

                            <span class="m-nav__link-text">{{__('admin/leftnav.test_write_access')}}</span>

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
                        <form id="test-booking-form" method="GET" action="/admin/write-access-check" target="_blank">
                            <div class="row m--margin-top-20"
                                 style="border-bottom: 1px solid #ebedf2; margin-bottom: 3rem; padding-bottom: 3rem;">
                                <div class="col-md-5">
                                    <label for="booking_id">Booking Info ID</label>
                                    <input type="text" id="booking_id" name="booking_info_id" class="form-control" required/>
                                </div>
                                <div class="col-md-2">
                                    <label for="booking-submit">&nbsp;</label>
                                    <br>
                                    <button type="submit" id="booking-submit" class="btn btn-primary">Check</button>
                                </div>
                            </div>
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>


@endsection


@section('ajax_script')

@endsection