@extends('layouts.admin')
@section('content')
<!-- BEGIN: Subheader -->
@include('partials.admin.alerts');
<div id="AccountStatus">

<div class="m-subheader ">
    <div class="d-flex align-items-center" >
        <div class="mr-auto">
            <h3 class="m-subheader__title ">{{ __('admin/leftnav.dashboard') }}</h3>
        </div>

        <div>
            <!-- <span class="m-subheader__daterange" id="m_dashboard_daterangepicker">
                <span class="m-subheader__daterange-label">
                    <span class="m-subheader__daterange-title"></span>
                    <span class="m-subheader__daterange-date m--font-brand"></span>
                </span>
                <a href="#" class="btn btn-sm btn-brand m-btn m-btn--icon m-btn--icon-only m-btn--custom m-btn--pill">
                    <i class="la la-angle-down"></i>
                </a>
            </span>
 -->

        


@role('superAdmin')


            <div class="m-widget6__foot">
                <div class="m-widget6__action m--align-right">

                    <a href="#"
                       class="btn m-btn--pill btn-secondary m-btn m-btn--hover-brand m-btn--custom">
                        Integrations
                    </a>
                </div>
            </div>




    

    @if($user_account->status == config('db_const.user.status.active.value'))

    




    <button  class="myid dropdown-item" data-id="{{ $user_account->id }}" data-status="{{ config('db_const.user.status.deactive.value') }}" data-token="{{ csrf_token() }}" @click.prevent="CompanyStatusbtn($event)" >{{ config('db_const.user.status.active.label') }} 

    <span class="m-switch m-switch--lg m-switch--icon">
                                                <label>
    <input type="checkbox" checked="checked" name="">
                                                <span></span>
                                                </label>
                                            </span>


    </button>

    @elseif ($user_account->status == config('db_const.user.status.deactive.value') )

    <button class="myid dropdown-item" data-id="{{ $user_account->id }}" data-status="{{ config('db_const.user.status.active.value') }}" data-token="{{ csrf_token() }}" @click.prevent="CompanyStatusbtn($event)" >{{ config('db_const.user.status.deactive.label') }}  </button>

    @endif


@endrole


        </div>
    </div>
</div>
<!-- END: Subheader -->

<!--Begin::Section-->
<div class="m-content">

    <div class="m-portlet ">
    <div class="m-portlet__body  m-portlet__body--no-padding">
        <div class="row m-row--no-padding m-row--col-separator-xl">
            <div class="col-md-12 col-lg-6 col-xl-3">
                <!--begin::Total Profit-->
                <div class="m-widget24">                     
                    <div class="m-widget24__item">
                        <h4 class="m-widget24__title">
                            Total Booking
                        </h4><br>
                        <span class="m-widget24__desc">
                            Booking
                        </span>
                        <span class="m-widget24__stats m--font-brand">
                           {{ $user_account->bookings_info->count() }} 
                        </span>     
                        <div class="m--space-10"></div>
                        <div class="progress m-progress--sm">
                            <div class="progress-bar m--bg-brand" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                       
                    </div>                    
                </div>
                <!--end::Total Profit-->
            </div>
            <div class="col-md-12 col-lg-6 col-xl-3">
                <!--begin::New Feedbacks-->
                <div class="m-widget24">
                     <div class="m-widget24__item">
                        <h4 class="m-widget24__title">
                            Total Properties
                        </h4><br>
                        <span class="m-widget24__desc">
                            Properties
                        </span>
                        <span class="m-widget24__stats m--font-info">
                          {{ $user_account->properties_info->count() }}
                        </span>     
                        <div class="m--space-10"></div>
                        <div class="progress m-progress--sm">
                            <div class="progress-bar m--bg-info" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                       
                    </div>      
                </div>
                <!--end::New Feedbacks--> 
            </div>
            <div class="col-md-12 col-lg-6 col-xl-3">
                <!--begin::New Orders-->
                <div class="m-widget24">
                    <div class="m-widget24__item">
                        <h4 class="m-widget24__title">
                           Total Transaction
                        </h4><br>
                        <span class="m-widget24__desc">
                            Transaction
                        </span>
                        <span class="m-widget24__stats m--font-danger">
                           
                             {{ $user_account->transactions_init->count() }} 
                        </span>     
                        <div class="m--space-10"></div>
                        <div class="progress m-progress--sm">
                            <div class="progress-bar m--bg-danger" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      
                    </div>      
                </div>
                <!--end::New Orders--> 
            </div>
            <div class="col-md-12 col-lg-6 col-xl-3">
                <!--begin::New Users-->
                <div class="m-widget24">
                     <div class="m-widget24__item">
                        <h4 class="m-widget24__title">
                           Total Booking Source
                        </h4><br>
                        <span class="m-widget24__desc">
                            Booking Source
                        </span>
                        <span class="m-widget24__stats m--font-success">
                            3 
                        </span>     
                        <div class="m--space-10"></div>
                        <div class="progress m-progress--sm">
                            <div class="progress-bar m--bg-success" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      
                    </div>      
                </div>
                <!--end::New Users--> 
            </div>
        </div>
    </div>
</div>
<!-- End Here -->
    <!--Begin::Section-->
    <div class="row">
        <div class="col-xl-4">
            <!--begin:: Widgets/Top Products-->
            <div class="m-portlet m-portlet--bordered-semi m-portlet--full-height ">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h3 class="m-portlet__head-text">
                            Booking 
                            </h3>
                        </div>
                    </div>
                    <div class="m-portlet__head-tools">
                        <ul class="m-portlet__nav">
                            <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" m-dropdown-toggle="hover" aria-expanded="true">
                                <a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
                                    All
                                </a>
                                <div class="m-dropdown__wrapper" style="z-index: 101;">
                                    <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 38.5px;"></span>
                                    <div class="m-dropdown__inner">
                                        <div class="m-dropdown__body">
                                            <div class="m-dropdown__content">
                                                <ul class="m-nav">
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
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin::Widget5-->
                    <div class="m-widget4">
                        <div class="m-widget4__chart m-portlet-fit--sides m--margin-top-10 m--margin-top-20" style="height:260px;"><div style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;" class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div></div><div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:200%;height:200%;left:0; top:0"></div></div></div>
                        <canvas id="m_chart_trends_stats" style="display: block; height: 260px; width: 381px;" width="476" height="325" class="chartjs-render-monitor"></canvas>
                    </div>
                    <div class="m-widget4__item">
                        <div class="m-widget4__img m-widget4__img--logo">
                            <img src="assets/app/media/img/client-logos/logo3.png" alt="">
                        </div>
                        <div class="m-widget4__info">
                            <span class="m-widget4__title">
                                Lastest Booking 
                            </span>
                            <br>
                            <span class="m-widget4__sub">
                                last 10 booking
                            </span>
                        </div>
                        <span class="m-widget4__ext">
                            <span class="m-widget4__number m--font-danger">+$17</span>
                        </span>
                    </div>
                    <div class="m-widget4__item">
                        <div class="m-widget4__img m-widget4__img--logo">
                            <img src="assets/app/media/img/client-logos/logo1.png" alt="">
                        </div>
                        <div class="m-widget4__info">
                            <span class="m-widget4__title">
                                Last Month Booking
                            </span>
                            <br>
                            <span class="m-widget4__sub">
                                last Month Booking
                            </span>
                        </div>
                        <span class="m-widget4__ext">
                            <span class="m-widget4__number m--font-danger">+$300</span>
                        </span>
                    </div>
                    <div class="m-widget4__item">
                        <div class="m-widget4__img m-widget4__img--logo">
                            <img src="assets/app/media/img/client-logos/logo2.png" alt="">
                        </div>
                        <div class="m-widget4__info">
                            <span class="m-widget4__title">
                                Last Year Booking
                            </span>
                            <br>
                            <span class="m-widget4__sub">
                                Last Year Booking
                            </span>
                        </div>
                        <span class="m-widget4__ext">
                            <span class="m-widget4__number m--font-danger">+$6700</span>
                        </span>
                    </div>
                </div>
                <!--end::Widget 5-->
                <div class="m-widget6__foot">
                            <div class="m-widget6__action m--align-right">
                                <a href="{{ route('clientbooking', ['id' => $user_account->id]) }}"
                                   class="btn m-btn--pill btn-secondary m-btn m-btn--hover-brand m-btn--custom">
                                    View All
                                </a>
                            </div>
                </div>
            </div>

        </div>

        <!--end:: Widgets/Top Products-->

    </div>
    <div class="col-xl-4">
                <!--begin:: Widgets/Activity-->
                <div class="m-portlet m-portlet--bordered-semi m-portlet--widget-fit m-portlet--full-height m-portlet--skin-light  m-portlet--rounded-force">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text m--font-light">
                                    Properties
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push"
                                    m-dropdown-toggle="hover">
                                    <a href="#"
                                       class="m-portlet__nav-link m-portlet__nav-link--icon m-portlet__nav-link--icon-xl">
                                        <i class="fa fa-genderless m--font-light"></i>
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__section m-nav__section--first">
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
                                                            <a href="#"
                                                               class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">Cancel</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">

                        <div class="m-widget17">

                            <div class="m-widget17__visual m-widget17__visual--chart m-portlet-fit--top m-portlet-fit--sides m--bg-danger">
                                <div class="m-widget17__chart" style="height:320px;">
                                    <div style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;"
                                         class="chartjs-size-monitor">
                                        <div class="chartjs-size-monitor-expand"
                                             style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                                            <div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div>
                                        </div>
                                        <div class="chartjs-size-monitor-shrink"
                                             style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;">
                                            <div style="position:absolute;width:200%;height:200%;left:0; top:0"></div>
                                        </div>
                                    </div>
                                    <canvas id="m_chart_activities" style="display: block; height: 216px; width: 381px;"
                                            width="476" height="270" class="chartjs-render-monitor"></canvas>
                                </div>
                            </div>

                            <div class="m-widget4">
                                <div class="m-widget4__item">
                                    <div class="m-widget4__img m-widget4__img--logo">
                                        <img src="assets/app/media/img/client-logos/logo5.png" alt="">
                                    </div>
                                    <div class="m-widget4__info">
                            <span class="m-widget4__title">
                                Connected
                            </span>
                                        <br>
                                        <span class="m-widget4__sub">
                                All Connected
                            </span>
                                    </div>
                                    <span class="m-widget4__ext">
                            <span class="m-widget4__number m--font-brand">
                                @if($user_account->properties_info)
                                    {{$user_account->properties_info->where('status',1)->count()}}
                                @else
                                    0
                                @endif
                            </span>
                        </span>
                                </div>
                                <div class="m-widget4__item">
                                    <div class="m-widget4__img m-widget4__img--logo">
                                        <img src="assets/app/media/img/client-logos/logo4.png" alt="">
                                    </div>
                                    <div class="m-widget4__info">
                            <span class="m-widget4__title">
                                Not Connected
                            </span>
                                        <br>
                                        <span class="m-widget4__sub">
                                All Not Connected
                            </span>
                                    </div>
                                    <span class="m-widget4__ext">
                            <span class="m-widget4__number m--font-brand">
                                @if($user_account->properties_info)
                                    {{$user_account->properties_info->where('status',2)->count()}}
                                @else
                                    0
                                @endif
                            </span>
                        </span>
                                </div>

                            </div>


                        </div>
                          <div class="m-widget6__foot">
                            <div class="m-widget6__action m--align-right">
                                <a href="{{ route('clientproperty', ['id' => $user_account->id]) }}"
                                   class="btn m-btn--pill btn-secondary m-btn m-btn--hover-brand m-btn--custom">
                                    View All
                                </a>
                            </div>
                </div>

                    </div>
                </div>
                <!--end:: Widgets/Activity-->
            </div>
<div class="col-xl-4">
    <!--begin:: Widgets/Blog-->
    
<!--begin:: Widgets/Blog-->
<div class="m-portlet m-portlet--head-overlay m-portlet--full-height   m-portlet--rounded-force">
<div class="m-portlet__head m-portlet__head--fit">
<div class="m-portlet__head-caption">
<div class="m-portlet__head-title">
<h3 class="m-portlet__head-text m--font-light">
Booking Source
</h3>
</div>
</div>
<div class="m-portlet__head-tools">
<ul class="m-portlet__nav">
<li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" m-dropdown-toggle="hover">
<a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill m-btn btn-outline-light m-btn--hover-light">
2018
</a>
<div class="m-dropdown__wrapper">
<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
<div class="m-dropdown__inner">
<div class="m-dropdown__body">
<div class="m-dropdown__content">
<ul class="m-nav">
<li class="m-nav__section m-nav__section--first">
<span class="m-nav__section-text">Reports</span>
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
</ul>
</div>
</div>
</div>
</div>
</li>
</ul>
</div>
</div>
<div class="m-portlet__body">
<div class="m-widget28">
<div class="m-widget28__pic m-portlet-fit--sides"></div>
<div class="m-widget28__container">
<!-- begin::Nav pills -->
<ul class="m-widget28__nav-items nav nav-pills nav-fill" role="tablist">
<li class="m-widget28__nav-item nav-item">
<a class="nav-link active" data-toggle="pill" href="#menu11"><span><i class="fa flaticon-pie-chart"></i></span><span>GMI Taxes</span></a>
</li>
<li class="m-widget28__nav-item nav-item">
<a class="nav-link" data-toggle="pill" href="#menu21"><span><i class="fa flaticon-file-1"></i></span><span>IMT Invoice</span></a>
</li>
<li class="m-widget28__nav-item nav-item">
<a class="nav-link" data-toggle="pill" href="#menu31"><span><i class="fa flaticon-clipboard"></i></span><span>Main Notes</span></a>
</li>
</ul>
<!-- end::Nav pills -->
<!-- begin::Tab Content -->
<div class="m-widget28__tab tab-content">
<div id="menu11" class="m-widget28__tab-container tab-pane active">
<div class="m-widget28__tab-items">
<div class="m-widget28__tab-item">
<span>Company Name</span>
<span>SLT Back-end Solutions</span>
</div>
<div class="m-widget28__tab-item">
<span>INE Number</span>
<span>D330-1234562546</span>
</div>
<div class="m-widget28__tab-item">
<span>Total Charges</span>
<span>USD 1,250.000</span>
</div>
<div class="m-widget28__tab-item">
<span>Project Description</span>
<span>Creating Back-end Components</span>
</div>
</div>
</div>
<div id="menu21" class="m-widget28__tab-container tab-pane fade">
<div class="m-widget28__tab-items">
<div class="m-widget28__tab-item">
<span>Project Description</span>
<span>Back-End Web Architecture</span>
</div>
<div class="m-widget28__tab-item">
<span>Total Charges</span>
<span>USD 2,170.000</span>
</div>
<div class="m-widget28__tab-item">
<span>INE Number</span>
<span>D110-1234562546</span>
</div>
<div class="m-widget28__tab-item">
<span>Company Name</span>
<span>SLT Back-end Solutions</span>
</div>
</div>
</div>
<div id="menu31" class="m-widget28__tab-container tab-pane fade">
<div class="m-widget28__tab-items">
<div class="m-widget28__tab-item">
<span>Total Charges</span>
<span>USD 3,450.000</span>
</div>
<div class="m-widget28__tab-item">
<span>Project Description</span>
<span>Creating Back-end Components</span>
</div>
<div class="m-widget28__tab-item">
<span>Company Name</span>
<span>SLT Back-end Solutions</span>
</div>
<div class="m-widget28__tab-item">
<span>INE Number</span>
<span>D510-7431562548</span>
</div>
</div>
</div>
</div>
<!-- end::Tab Content -->
</div>
</div>
</div>
</div>
<!--end:: Widgets/Blog-->

    <!--end:: Widgets/Blog-->
</div>
</div>
<!--End::Section-->

<!--End::Section-->


<!--Begin::Section-->
        <div class="row">
            <div class="col-xl-6 col-lg-12">
                <!--Begin::Portlet-->
                <div class="m-portlet  m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Recent Activities
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push"
                                    m-dropdown-toggle="hover" aria-expanded="true">
                                    <a href="#"
                                       class="m-portlet__nav-link m-portlet__nav-link--icon m-portlet__nav-link--icon-xl m-dropdown__toggle">
                                        <i class="la la-ellipsis-h m--font-brand"></i>
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
                                                        <li class="m-nav__section m-nav__section--first">
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
                                                            <a href="#"
                                                               class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">Cancel</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="m-scrollable m-scroller ps ps--active-y" data-scrollable="true" data-height="380"
                             data-mobile-height="300" style="height: 380px; overflow: hidden;">
                            <!--Begin::Timeline 2 -->
                            <div class="m-timeline-2">
                                <div class="m-timeline-2__items  m--padding-top-5 m--padding-bottom-30">

                                    @foreach($user_account as $activity)
                                        <div class="m-timeline-2__item m--margin-top-30">
                                            <span class="m-timeline-2__item-time">17:00</span>
                                            <div class="m-timeline-2__item-cricle">
                                                <i class="fa fa-genderless m--font-info"></i>
                                            </div>
                                            <div class="m-timeline-2__item-text m--padding-top-5">
                                                Placed a new order in <a href="#"
                                                                         class="m-link m-link--brand m--font-bolder">SIGNATURE
                                                    MOBILE</a> marketplace.
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                            <!--End::Timeline 2 -->
                            <div class="ps__rail-x" style="left: 0px; bottom: -15px;">
                                <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                            </div>
                            <div class="ps__rail-y" style="top: 15px; height: 380px; right: 4px;">
                                <div class="ps__thumb-y" tabindex="0" style="top: 10px; height: 270px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--End::Portlet-->
            </div>
            <div class="col-xl-6">
                <!--begin:: Widgets/Support Tickets -->
                <div class="m-portlet m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Support Tickets
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push"
                                    m-dropdown-toggle="hover" aria-expanded="true">
                                    <a href="#"
                                       class="m-portlet__nav-link m-portlet__nav-link--icon m-portlet__nav-link--icon-xl m-dropdown__toggle">
                                        <i class="la la-ellipsis-h m--font-brand"></i>
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav">
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
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <div class="m-widget3">
                            @foreach($user_account as $ticket )
                                <div class="m-widget3__item">
                                    <div class="m-widget3__header">
                                        <div class="m-widget3__user-img">
                                            <img class="m-widget3__img" src="assets/app/media/img/users/user1.jpg"
                                                 alt="">
                                        </div>
                                        <div class="m-widget3__info">
                        <span class="m-widget3__username">
                            Melania Trump
                        </span>
                                            <br>
                                            <span class="m-widget3__time">
                            2 day ago
                        </span>
                                        </div>
                                        <span class="m-widget3__status m--font-info">
                        Pending
                    </span>
                                    </div>
                                    <div class="m-widget3__body">
                                        <p class="m-widget3__text">
                                            Lorem ipsum dolor sit amet,consectetuer edipiscing elit,sed diam nonummy
                                            nibh
                                            euismod tinciduntut laoreet doloremagna aliquam erat volutpat.
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!--end:: Widgets/Support Tickets -->
            </div>
        </div>
        <!--End::Section-->

        <!--Begin::Section-->
        <div class="row">
            <div class="col-xl-4">
                <!--begin:: Widgets/Audit Log-->
                <div class="m-portlet m-portlet--full-height ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Latest Issues
                                </h3>
                            </div>
                        </div>
                    




                    </div>


                <i class="fa fa-genderless m--font-info"> </i>
                        
                                        
@if (count($user_account->error_logs) > 0)
    {{$user_account->error_logs->first()->message }}

@else
    {{ 'All Integration Is Ok' }}
@endif


                   
                </div>
                <!--end:: Widgets/Audit Log-->
            </div>
            <div class="col-xl-8">
                <div class="m-portlet m-portlet--mobile ">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Latest Bookings List
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item">
                                    <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push"
                                         m-dropdown-toggle="hover" aria-expanded="true">
                                        <a href="#"
                                           class="m-portlet__nav-link btn btn-lg btn-secondary  m-btn m-btn--icon m-btn--icon-only m-btn--pill  m-dropdown__toggle">
                                            <i class="la la-ellipsis-h m--font-brand"></i>
                                        </a>
                                        <div class="m-dropdown__wrapper">
                                            <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                            <div class="m-dropdown__inner">
                                                <div class="m-dropdown__body">
                                                    <div class="m-dropdown__content">
                                                        <ul class="m-nav">
                                                            <li class="m-nav__section m-nav__section--first">
                                                                <span class="m-nav__section-text">Quick Actions</span>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-share"></i>
                                                                    <span class="m-nav__link-text">Create Post</span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-chat-1"></i>
                                                                    <span class="m-nav__link-text">Send Messages</span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-multimedia-2"></i>
                                                                    <span class="m-nav__link-text">Upload File</span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__section">
                                                                <span class="m-nav__section-text">Useful Links</span>
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
                                                            <li class="m-nav__separator m-nav__separator--fit m--hide">
                                                            </li>
                                                            <li class="m-nav__item m--hide">
                                                                <a href="#"
                                                                   class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">Submit</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin: table -->
                        <div class="m-section">

                            <div class="m-section__content">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Guest Name</th>
                                            <th>Property Name</th>
                                            <th>Booking Date</th>
                                            <th>CheckIn Date</th>
                                            <th>CheckOut Date</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $i = 1
                                        @endphp
                                        @foreach($user_account->bookings_info->sortByDesc('booking_time')->slice(0,5) as $booking )
                                            <tr>
                                                <th scope="row">{{$i++ }}</th>
                                                <td>{{$booking->guest_email}}</td>
                                                <td>{{$booking->property_id}}</td>
                                                <td>{{$booking->booking_time}}</td>
                                                <td>{{$booking->check_in_date}}</td>
                                                <td>{{$booking->check_out_date}}</td>
                                                <td>Action</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--end: table -->
                    </div>
                </div>
            </div>

        </div>







</div>
</div>
@endsection




    @section('ajax_script')

            <script type="application/javascript">

            
         
                   

                //=================================
                // Memeber status update code below
                //===============================

                var MemSt = new Vue({
                    el: '#AccountStatus',
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
                        CompanyStatusbtn(event){
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
                                    axios.post('/admin/accountstatus/' + id + '/' + st)
                                        .then((response) => {

                                            if (response.data.status == _this.deactiveval ) {
                                                let rttl = '{{ __('client/team.deactive_response.title') }}';
                                                let rmsg = '{{ __('client/team.deactive_response.msg') }}';
                                                event.target.dataset.status = _this.activeval
                                                event.target.innerHTML ='<span class="m-switch m-switch--lg m-switch--icon"><label><input type="checkbox"name=""><span></span></label></span>'+_this.deactive ;
                                                swal(rttl+"!", rmsg, "error")
                                            } else if (response.data.status == _this.activeval) {
                                                let rttl = '{{ __('client/team.active_response.title') }}';
                                                let rmsg = '{{ __('client/team.active_response.msg') }}';
                                                event.target.dataset.status = _this.deactiveval;
                                                event.target.innerHTML ='<span class="m-switch m-switch--lg m-switch--icon"><label><input type="checkbox" checked="checked" name=""><span></span></label></span>'+_this.active ;
                                                swal(rttl+"!", rmsg, "success")
                                            }

                                        },(error) => {
                                            //console.log("Hi I'm Error   ");
                                            // error callback
                                        })

                                }

                                }); //this is swal end ;
                            } //this else if end
                        }
                    }

                })

              
            </script>
            @endsection