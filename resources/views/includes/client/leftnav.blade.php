<!-- BEGIN: Left Aside -->
<button class="m-aside-left-close  m-aside-left-close--skin-dark " id="m_aside_left_close_btn"><i
            class="la la-close"></i></button>
<div id="m_aside_left" class="m-grid__item  m-aside-left  m-aside-left--skin-dark ">
    <!-- BEGIN: Aside Menu -->
    <div id="m_ver_menu" class="m-aside-menu  m-aside-menu--skin-dark m-aside-menu--submenu-skin-dark "
         m-menu-vertical="1" m-menu-scrollable="1" m-menu-dropdown-timeout="500" style="position: relative;">
        <ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow ">
            <li class="m-menu__item {{ (\Request::route()->getName() == 'dashboard') ? 'm-menu__item--active' : '' }}" aria-haspopup="true"><a href="{{ route('dashboard') }}"
                                                                                   class="m-menu__link "><i
                            class="m-menu__link-icon flaticon-line-graph"></i><span class="m-menu__link-title">  <span
                                class="m-menu__link-wrap">      <span
                                    class="m-menu__link-text" style="color:#f7f7f7 !important;" >{{ __('client/leftnav.dashboard') }}</span>      <span
                                    class="m-menu__link-badge"><!-- <span class="m-badge m-badge--danger">2</span> --></span>  </span></span></a>
            </li>
            <!--<li class="m-menu__section ">
                                <h4 class="m-menu__section-text">Components</h4>
                                <i class="m-menu__section-icon flaticon-more-v2"></i>
            </li> -->
            @if(Gate::check('properties'))
            <li class="m-menu__item {{ (\Request::route()->getName() == 'properties' || \Request::route()->getName() == 'property_details') ? 'm-menu__item--active' : '' }}" aria-haspopup="true">
                <a href="{{ route('properties') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon la la-home"></i><span></span></i><span
                            class="m-menu__link-text" style="color:#f7f7f7 !important;" data-step="1" data-intro="All synchronized properties will be listed here." data-position='right'>{{ __('client/leftnav.properties') }}</span></a></li>
@endif()

            @if(Gate::check('bookings'))
            <li class="m-menu__item {{ (\Request::route()->getName() == 'bookings' || \Request::route()->getName() == 'booking_details') ? 'm-menu__item--active' : '' }}" aria-haspopup="true">
                <a href="{{ route('bookings') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon flaticon-calendar"></i><span></span></i><span
                            class="m-menu__link-text" style="color:#f7f7f7 !important;" data-step="2" data-intro="All bookings against properties will be listed here. You can also view single booking detail and can proform specific actions against the booking amount." data-position='right'>{{ __('client/leftnav.bookings') }}</span></a>
                        </li>
            @endif()
@if(Gate::check('full client'))

            <li class="m-menu__item  m-menu__item--submenu {{ (\Request::route()->getName() == 'manageteam') ? 'm-menu__item--expanded m-menu__item--open ' : '' }}" aria-haspopup="true"><a href="javascript:;"
                                                                                    class="m-menu__link m-menu__toggle"><i
                            class="m-menu__link-icon la la-group"></i><span
                            class="m-menu__link-text" style="color:#f7f7f7 !important;" data-step="3" data-intro="All subordinates members of the company will be registered and listed here." data-position='right'>{{ __('client/leftnav.team') }}</span><i
                            class="m-menu__ver-arrow la la-angle-right"></i></a>
                <div class="m-menu__submenu "><span class="m-menu__arrow"></span>
                    <ul class="m-menu__subnav">
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'manageteam') ? 'm-menu__item--active' : '' }} " aria-haspopup="true"><a href="{{ route('manageteam') }}"
                                                                          class="m-menu__link "><i
                                        class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span
                                        class="m-menu__link-text" style="color:#f7f7f7 !important;">{{ __('client/leftnav.manage_team') }}</span></a></li>
                    </ul>
                </div>
            </li>
@endif()
            @if(Gate::check('full client') || Gate::check('guestExperience') || Gate::check('preferences') || Gate::check('deleteSetting') || Gate::check('accountSetup'))
            <li class="m-menu__item m-menu__item--submenu  {{ (\Request::route()->getName() == 'pmsintegration' || \Request::route()->getName() == 'settings'  || \Request::route()->getName() == 'generalSettings') ? 'm-menu__item--expanded m-menu__item--open ' : '' }} " aria-haspopup="true"><a href="javascript:;"
                                                                                    class="m-menu__link m-menu__toggle"><i
                            class="m-menu__link-icon flaticon-settings-1  "></i><span
                            class="m-menu__link-text" style="color:#f7f7f7 !important;" data-step="4" data-intro="Global settings related to property management system (PMS) and payment gateways and preferences settings related to communications can be manage here." data-position='right'>{{ __('client/leftnav.settings') }}</span><i
                            class="m-menu__ver-arrow la la-angle-right"></i></a>
                <div class="m-menu__submenu "><span class="m-menu__arrow"></span>
                    <ul class="m-menu__subnav">

                        @if(Gate::check('guestExperience'))
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'generalSettings') ? 'm-menu__item--active' : '' }} " aria-haspopup="true"><a href="{{ route('generalSettings') }}"
                                                                                                                                                           class="m-menu__link "><i
                                        class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span
                                        class="m-menu__link-text" style="color:#f7f7f7 !important;" >General</span></a>
                        </li>
                        @endif
                        @if(Gate::check('preferences'))
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'settings') ? 'm-menu__item--active' : '' }} " aria-haspopup="true"><a href="{{ route('settings') }}"
                                                                          class="m-menu__link "><i
                                        class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span
                                        class="m-menu__link-text" style="color:#f7f7f7 !important;" >Preferences</span></a>
                                    </li>
                        @endif
                        @if(Gate::check('accountSetup'))
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'pmsintegration') ? 'm-menu__item--active' : '' }}" aria-haspopup="true"><a href="{{ route('pmsintegration') }}"
                                                          class="m-menu__link "><i
                        class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span
                        class="m-menu__link-text" style="color:#f7f7f7 !important;" >Global Settings </span></a></li>
                            @endif
                    </ul>
                </div>
            </li>
@endif()




        <!-- <li class="m-menu__item  m-menu__item" aria-haspopup="true"><a href="index.html" class="m-menu__link "><i class="m-menu__link-icon flaticon-user-add "></i>  <span class="m-menu__link-text">{{ __('client/leftnav.add_staff') }}</span>  </a></li> -->


        </ul>
    </div>
    <!-- END: Aside Menu -->
</div>
<!-- END: Left Aside -->
