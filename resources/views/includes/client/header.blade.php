<!-- BEGIN: Header -->
<header id="m_header" class="m-grid__item    m-header " m-minimize-offset="200" m-minimize-mobile-offset="200">
    <div class="m-container m-container--fluid m-container--full-height">
        <div class="m-stack m-stack--ver m-stack--desktop">
            <!-- BEGIN: Brand -->
            <div class="m-stack__item m-brand  m-brand--skin-dark ">
                <div class="m-stack m-stack--ver m-stack--general">
                    <div class="m-stack__item m-stack__item--middle m-brand__logo">
                        <a href="{{ route('dashboard') }}" class="m-brand__logo-wrapper">
                            <img alt="Charge Automation"
                                 src="{{ asset('assets/demo/default/media/img/logo/logo1.png') }}"/>
                        </a>
                    </div>
                    <div class="m-stack__item m-stack__item--middle m-brand__tools">
                        <!-- BEGIN: Left Aside Minimize Toggle -->
                        <a href="javascript:;" id="m_aside_left_minimize_toggle"
                           class="m-brand__icon m-brand__toggler m-brand__toggler--left m--visible-desktop-inline-block  ">
                            <span></span>
                        </a>
                        <!-- END -->
                        <!-- BEGIN: Responsive Aside Left Menu Toggler -->
                        <a href="javascript:;" id="m_aside_left_offcanvas_toggle"
                           class="m-brand__icon m-brand__toggler m-brand__toggler--left m--visible-tablet-and-mobile-inline-block">
                            <span></span>
                        </a>
                        <!-- END -->
                        <!-- BEGIN: Responsive Header Menu Toggler -->
                        {{--<a id="m_aside_header_menu_mobile_toggle" href="javascript:;"
                           class="m-brand__icon m-brand__toggler m--visible-tablet-and-mobile-inline-block">
                            <span></span>
                        </a>--}}
                        <!-- END -->
                        <!-- BEGIN: Topbar Toggler -->
                        <a id="m_aside_header_topbar_mobile_toggle" href="javascript:;"
                           class="m-brand__icon m--visible-tablet-and-mobile-inline-block">
                            <i class="flaticon-more"></i>
                        </a>
                        <!-- BEGIN: Topbar Toggler -->
                    </div>
                </div>
            </div>
            <!-- END: Brand -->
            <div class="m-stack__item m-stack__item--fluid m-header-head" id="m_header_nav" style="position: relative">


                <div style="display:block;float:left;height:26px;overflow:hidden;position:relative;width:100%;">
                    <div class="alert alert-dismissible fade show newDashboardStrip primary-light-color" role="alert">
                        Try Our <strong><a href="{{ route(returnRoute(Request::route()->getName())) }}"> New Dashboard </a></strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>





                <!-- BEGIN: Horizontal Menu -->
                <button class="m-aside-header-menu-mobile-close  m-aside-header-menu-mobile-close--skin-dark "
                        id="m_aside_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
                <div id="m_header_menu"
                     class="m-header-menu m-aside-header-menu-mobile m-aside-header-menu-mobile--offcanvas  m-header-menu--skin-light m-header-menu--submenu-skin-light m-aside-header-menu-mobile--skin-dark m-aside-header-menu-mobile--submenu-skin-dark ">
<!--                     <ul class="m-menu__nav  m-menu__nav--submenu-arrow ">
                        <li class="m-menu__item  m-menu__item--submenu m-menu__item--rel" m-menu-submenu-toggle="click"
                            m-menu-link-redirect="1" aria-haspopup="true"><a href="javascript:;"
                                                                             class="m-menu__link m-menu__toggle"><i
                                        class="m-menu__link-icon flaticon-home"></i><span class="m-menu__link-text">Home</span> </a>

                        </li>
                        <li class="m-menu__item  m-menu__item--submenu m-menu__item--rel" m-menu-submenu-toggle="click"
                            m-menu-link-redirect="1" aria-haspopup="true"><a href="javascript:;"
                                                                             class="m-menu__link m-menu__toggle"><i
                                        class="m-menu__link-icon flaticon-line-graph"></i><span
                                        class="m-menu__link-text">About us</span> </a>

                        </li>
                        <li class="m-menu__item  m-menu__item--submenu m-menu__item--rel" m-menu-submenu-toggle="click"
                            m-menu-link-redirect="1" aria-haspopup="true"><a href="javascript:;"
                                                                             class="m-menu__link m-menu__toggle"><i
                                        class="m-menu__link-icon flaticon-paper-plane"></i><span
                                        class="m-menu__link-title">  <span class="m-menu__link-wrap">      <span
                                                class="m-menu__link-text">Support</span> </span></span>  </a>

                        </li>
                    </ul> -->
                </div>
                <!-- END: Horizontal Menu -->
                <!-- BEGIN: Topbar -->

                <div id="m_header_topbar" class="m-topbar  m-stack m-stack--ver m-stack--general m-stack--fluid">


                    <div class="m-stack__item m-topbar__nav-wrapper">

                        <ul class="m-topbar__nav m-nav m-nav--inline">
                            {{--<!--<li class="m-nav__item m-topbar__notifications m-topbar__notifications--img m-dropdown m-dropdown--large m-dropdown--header-bg-fill m-dropdown--arrow m-dropdown--align-right  m-dropdown--mobile-full-width">
                                <a class="SwitchToBTN" href="{{ route('v2dashboard') }}">Try Our New Dashboard</a>
                            </li>-->--}}


                            <li class="m-nav__item m-topbar__notifications m-topbar__notifications--img m-dropdown m-dropdown--large m-dropdown--header-bg-fill m-dropdown--arrow m-dropdown--align-right  m-dropdown--mobile-full-width"
                                m-dropdown-toggle="click" m-dropdown-persistent="1" id="app-9">
                                {{-- 
                                    <a href="#" class="m-nav__link m-dropdown__toggle" id="m_topbar_notification_icon">
                                    @if(Auth::user()->unreadNotifications->count() == 0)
                                        <span class="m-nav__link-badge m-badge m-badge--dot m-badge--dot-small"></span>
                                    @else
                                    <span class="m-nav__link-badge m-badge m-badge--dot m-badge--dot-small m-badge--danger"></span>
                                    @endif
                                    <span class="m-nav__link-icon"><i class="flaticon-music-2"></i></span>
                                </a> --}}
                                
                                   <a href="#" class="m-nav__link m-dropdown__toggle" id="m_topbar_notification_icon" onclick="readed()">
                                    
                                    <span class="m-nav__link-icon"><i class="flaticon-music-2"></i></span>
                                    <span class="m-badge m-badge--danger" id="notificationCounts" style="margin-left: -8px;margin-top:5px ;display: none !important"></span>
                                </a> 

                                <div class="m-dropdown__wrapper" >
                                    <span class="m-dropdown__arrow m-dropdown__arrow--right"></span>
                                    <div class="m-dropdown__inner">
                                        <div class="m-dropdown__header m--align-center"
                                             style="background: url({{asset('assets/app/media/img/misc/notification_bg.jpg')}}); background-size: cover;">
                                            {{-- <span class="m-dropdown__header-title">{{ Auth::user()->unreadNotifications->count() }}</span> --}}
                                            <span class="m-dropdown__header-title"  id="notificationCounts2" ></span>                                            
                                            <span class="m-dropdown__header-subtitle">New Notifications</span>
                                        </div>
                                        <div class="m-dropdown__body">
                                            <div class="m-dropdown__content"  >

                                                <div class="tab-content">
                                                    <div class="tab-pane active" id="topbar_notifications_notifications" role="tabpanel">
                                                        <div class="m-scrollable" data-scrollable="true" data-height="250" data-mobile-height="200">
                                                            <div class="m-list-timeline m-list-timeline--skin-light">
                                                                <div class="m-list-timeline__items">
                                                                

                                                     
                                                        <div class="m-dropdown__body">              
                <div class="m-dropdown__content">
                    <ul class="nav nav-tabs m-tabs m-tabs-line m-tabs-line--brand" role="tablist">
                        <li class="nav-item m-tabs__item">
                            <a class="nav-link m-tabs__link active show" data-toggle="tab" href="#topbar_notifications_notifications" role="tab" aria-selected="true">
                            Alerts
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active show" id="topbar_notifications_notifications" role="tabpanel">
                            <div class="m-scrollable m-scroller ps ps--active-y" data-scrollable="true" data-height="250" data-mobile-height="200" style="height: 250px; overflow: hidden;">
                                <div class="m-list-timeline m-list-timeline--skin-light">
                                    <div class="m-list-timeline__items" id="newMsg"><!-- 
                                        <div class="m-list-timeline__item">
                                            <span class="m-list-timeline__badge -m-list-timeline__badge--state-success"></span>
                                            <span class="m-list-timeline__text">12 new users registered</span>
                                            <span class="m-list-timeline__time">Just now</span>
                                        </div>
                                        <div class="m-list-timeline__item">
                                            <span class="m-list-timeline__badge"></span>
                                            <span class="m-list-timeline__text">Production server up</span>
                                            <span class="m-list-timeline__time">5 hrs</span>
                                        </div>
                                     --></div>
                                </div>
                            <div class="ps__rail-x" style="left: 0px; bottom: -15px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 15px; right: 4px; height: 250px;"><div class="ps__thumb-y" tabindex="0" style="top: 13px; height: 220px;"></div></div></div>
                        </div>
                      
                    </div>
                </div>
            </div>
                            
                      

                                                                  {{--  <!-- <notifications
                                                                            v-for="item in newNotificationList"
                                                                            v-bind:todo="item"></notifications> -->
                                                                --}}
                                                               
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div><a href="/client/notifications">See Allss</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            @php
                                $company_logo = checkImageExists( auth()->user()->user_account->company_logo, auth()->user()->user_account->name, config('db_const.logos_directory.company.value') );
                            @endphp
                            <li class="m-nav__item m-topbar__user-profile m-topbar__user-profile--img  m-dropdown m-dropdown--medium m-dropdown--arrow m-dropdown--header-bg-fill m-dropdown--align-right m-dropdown--mobile-full-width m-dropdown--skin-light"
                                m-dropdown-toggle="click">
                                <a href="#" class="m-nav__link m-dropdown__toggle">
                                <span class="m-topbar__userpic">
                                    @if($company_logo['company_initial'] == '')
                                        <img src="/storage/uploads/companylogos/{{ $company_logo['company_image'] }}" class="m--img-rounded m--marginless" style="height: 41px; width: 41px;" alt=""/>
                                    @else
                                        <span class="m--img-rounded m--marginless" style="height: 41px; width: 41px; padding: 1rem; border:1px solid #9699a2; font-weight: bolder; color: #9699a2;">{{ $company_logo['company_initial'] }}</span>
                                    @endif
                                </span>
                                    <span class="m-topbar__username m--hide">{{ 'name' }}</span>
                                </a>
                                <div class="m-dropdown__wrapper">
                                    <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                    <div class="m-dropdown__inner">
                                        <div class="m-dropdown__header m--align-center"
                                             style="background: url({{asset('assets/app/media/img/misc/user_profile_bg.jpg')}}); background-size: cover;">
                                            <div class="m-card-user m-card-user--skin-dark">
                                                <div class="m-card-user__pic">
                                                    @if($company_logo['company_initial'] == '')
                                                        <img src="/storage/uploads/companylogos/{{ $company_logo['company_image'] }}" style="height: 70px; width: 70px;"
                                                             class="m--img-rounded m--marginless" alt="{{ auth()->user()->user_account->name}}"/>
                                                    @else
                                                        <span class="m--img-rounded m--marginless" style="height: 70px; width: 70px; padding: 1rem; border:1px solid #FFFFFF; font-weight: bolder; color: #FFFFFF;">{{ $company_logo['company_initial'] }}</span>
                                                    @endif
                                                    <!--
                                                    <span class="m-type m-type--lg m--bg-danger"><span class="m--font-light">S<span><span>
                                                    -->
                                                </div>
                                                <div class="m-card-user__details">
                                                    <span class="m-card-user__name m--font-weight-500"> {{auth()->user()->user_account->name}}</span>
                                                    <a href="" class="m-card-user__email m--font-weight-300 m-link">{{auth()->user()->email}}</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-dropdown__body">
                                            <div class="m-dropdown__content">
                                                <ul class="m-nav m-nav--skin-light">
                                                    <li class="m-nav__section m--hide">
                                                        <span class="m-nav__section-text">Section</span>
                                                    </li>
                                                    <li class="m-nav__item">
                                                        <a href="{{ route('profile') }}" class="m-nav__link">
                                                            <i class="m-nav__link-icon flaticon-profile-1"></i>
                                                            <span class="m-nav__link-title">
            <span class="m-nav__link-wrap">
                <span class="m-nav__link-text">My Profile</span>
            </span>
        </span>
                                                        </a>
                                                    </li>

                                                    <li class="m-nav__separator m-nav__separator--fit">
                                                    </li>
                                                    <li class="m-nav__item">
                                                        <a class="btn m-btn--pill    btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder"
                                                           href="{{ route('logout') }}"
                                                           onclick="event.preventDefault();
                                                            document.getElementById('logout-form').submit();">
                                                            {{ __('Logout') }}
                                                        </a>
                                                        <form id="logout-form" action="{{ route('logout') }}"
                                                              method="POST" style="display: none;">
                                                            @csrf
                                                        </form>

                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li id="m_quick_sidebar_toggle" class="m-nav__item">

                            </li>
                        </ul>
                    </div>
                </div>
                <!-- END: Topbar -->
            </div>
        </div>
    </div>

</header>

<!-- END: Header