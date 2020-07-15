<!-- BEGIN: Left Aside -->
<button class="m-aside-left-close  m-aside-left-close--skin-dark " id="m_aside_left_close_btn"><i
            class="la la-close"></i></button>
<div id="m_aside_left" class="m-grid__item  m-aside-left  m-aside-left--skin-dark ">
    <!-- BEGIN: Aside Menu -->
    <div id="m_ver_menu" class="m-aside-menu  m-aside-menu--skin-dark m-aside-menu--submenu-skin-dark "
         m-menu-vertical="1" m-menu-scrollable="1" m-menu-dropdown-timeout="500" style="position: relative;">
        <ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow ">
            <li class="m-menu__item {{ (\Request::route()->getName() == 'adminListing') ? 'm-menu__item--active' : '' }}" aria-haspopup="true">
                <a href="{{ route('adminListing') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon flaticon-users"></i>
                    <span></span>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.admin_list') }}</span>
                </a>
            </li>

            <li class="m-menu__item  m-menu__item {{ (\Request::route()->getName() == 'adminUserAccounts') ? 'm-menu__item--active' : '' }}" aria-haspopup="true">
                <a href="{{ route('adminUserAccounts') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon flaticon-users"></i>
                    <span></span>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.user_account_list') }}</span>
                </a>
            </li>
            
            <li class="m-menu__item {{ (\Request::route()->getName() == 'admin.properties') ? 'm-menu__item--active' : '' }}" aria-haspopup="true">
                <a href="{{ route('admin.properties') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon la la-home"></i>
                    <span></span>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.all_properties') }}</span>
                </a>
            </li>
            <li class="m-menu__item {{ (\Request::route()->getName() == 'emails') ? 'm-menu__item--active' : '' }}" aria-haspopup="true">
                <a href="{{ route('emails') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon la la-envelope "></i>
                    <span></span>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.emails') }}</span>
                </a>
            </li>

            <li class="m-menu__item  m-menu__item {{ (\Request::route()->getName() == 'clientsbookings') ? 'm-menu__item--active' : '' }}" aria-haspopup="true">
                <a href="{{ route('clientsbookings') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon flaticon-calendar"></i>
                    <span></span>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.all_bookings') }}</span>
                </a>
            </li>

            <li class="m-menu__item  m-menu__item {{ (\Request::route()->getName() == 'adminTermAndConditionList') ? 'm-menu__item--active' : '' }}" aria-haspopup="true">
                <a href="{{ route('adminTermAndConditionList') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon flaticon-calendar"></i>
                    <span></span>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.terms_and_conditions') }}</span>
                </a>
            </li>

            <li class="m-menu__item  m-menu__item--submenu{{ (\Request::route()->getName() == 'adminGuideBooks' || \Request::route()->getName() == 'adminGuideBookTypes') ? 'm-menu__item--expanded m-menu__item--open' : '' }}" aria-haspopup="true">

                <a href="javascript:void(0)" class="m-menu__link m-menu__toggle">
                    <i class="m-menu__link-icon flaticon-avatar "></i>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.guide_books') }}</span>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>

                <div class="m-menu__submenu ">
                    <span class="m-menu__arrow"></span>
                    <ul class="m-menu__subnav">

                        <li class="m-menu__item {{ (\Request::route()->getName() == 'adminGuideBooks') ? 'm-menu__item--active' : '' }} " aria-haspopup="true">
                            <a href="{{ route('adminGuideBooks') }}" class="m-menu__link ">
                                <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                    <span></span>
                                </i>
                                <span class="m-menu__link-text">{{ __('admin/leftnav.guide_book_list') }}</span>
                            </a>
                        </li>
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'adminGuideBookTypes') ? 'm-menu__item--active' : '' }} " aria-haspopup="true">
                            <a href="{{ route('adminGuideBookTypes') }}" class="m-menu__link ">
                                <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                    <span></span>
                                </i>
                                <span class="m-menu__link-text">{{ __('admin/leftnav.guide_book_type_list') }}</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="m-menu__item  m-menu__item--submenu{{ (\Request::route()->getName() == 'adminUpsells' || \Request::route()->getName() == 'adminUpsellTypes' || \Request::route()->getName() == 'adminUpsellOrders') ? 'm-menu__item--expanded m-menu__item--open' : '' }}" aria-haspopup="true">

                <a href="javascript:void(0)" class="m-menu__link m-menu__toggle">
                    <i class="m-menu__link-icon flaticon-avatar "></i>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.upsells') }}</span>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>

                <div class="m-menu__submenu ">
                    <span class="m-menu__arrow"></span>
                    <ul class="m-menu__subnav">

                        <li class="m-menu__item {{ (\Request::route()->getName() == 'adminUpsells') ? 'm-menu__item--active' : '' }} " aria-haspopup="true">
                            <a href="{{ route('adminUpsells') }}" class="m-menu__link ">
                                <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                    <span></span>
                                </i>
                                <span class="m-menu__link-text">{{ __('admin/leftnav.upsell_list') }}</span>
                            </a>
                        </li>
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'adminUpsellTypes') ? 'm-menu__item--active' : '' }} " aria-haspopup="true">
                            <a href="{{ route('adminUpsellTypes') }}" class="m-menu__link ">
                                <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                    <span></span>
                                </i>
                                <span class="m-menu__link-text">{{ __('admin/leftnav.upsell_type') }}</span>
                            </a>
                        </li>
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'adminUpsellOrders') ? 'm-menu__item--active' : '' }} " aria-haspopup="true">
                            <a href="{{ route('adminUpsellOrders') }}" class="m-menu__link ">
                                <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                    <span></span>
                                </i>
                                <span class="m-menu__link-text">{{ __('admin/leftnav.upsell_order') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="m-menu__item  m-menu__item--submenu{{ ((\Request::route()->getName() == 'stripeCommissionPlansList') || (\Request::route()->getName() == 'assignPlans'))
             ? 'm-menu__item--expanded m-menu__item--open' : '' }}" aria-haspopup="true">

                <a href="javascript:;" class="m-menu__link m-menu__toggle">
                    <i class="m-menu__link-icon flaticon-avatar "></i>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.commissionBilling') }}</span>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
                <div class="m-menu__submenu ">
                    <span class="m-menu__arrow"></span>
                    <ul class="m-menu__subnav">
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'stripeCommissionPlansList') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                            <a href="{{ route('stripeCommissionPlansList') }}" class="m-menu__link ">
                                <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                    <span></span>
                                </i>
                                <span class="m-menu__link-text">{{ __('admin/leftnav.stripeCommissionPlansList') }}</span>
                            </a>
                        </li>
                        <li class="m-menu__item {{ (\Request::route()->getName() == 'assignPlans') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                            <a href="{{ route('assignPlans') }}" class="m-menu__link ">
                                <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                    <span></span>
                                </i>
                                <span class="m-menu__link-text">{{ __('admin/leftnav.assignPlans') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="m-menu__item  m-menu__item--submenu{{ (\Request::route()->getName() == 'paymentgateways') ? 'm-menu__item--expanded m-menu__item--open' : '' }}" aria-haspopup="true">

                <a href="javascript:;" class="m-menu__link m-menu__toggle">
                    <i class="m-menu__link-icon flaticon-avatar "></i>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.settings') }}</span>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>

                <div class="m-menu__submenu ">
                    <span class="m-menu__arrow"></span>
                    <ul class="m-menu__subnav">

                        <li class="m-menu__item {{ (\Request::route()->getName() == 'paymentgateways') ? 'm-menu__item--active' : '' }} " aria-haspopup="true">
                            <a href="{{ route('paymentgateways') }}" class="m-menu__link ">
                                <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                                    <span></span>
                                </i>
                                <span class="m-menu__link-text">{{ __('admin/leftnav.paymentgateways') }}</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>


            <li class="m-menu__section ">
                <h4 class="m-menu__section-text">Dev & Testing</h4>
                <i class="m-menu__section-icon flaticon-more-v2"></i>
            </li>

            @if(config('app.env') != 'production' || config('app.debug') == true)
            <li class="m-menu__item {{ (\Request::route()->getName() == 'create-test-bookings') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                <a href="{{ route('create-test-bookings') }}" class="m-menu__link ">
                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                        <span></span>
                    </i>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.bookings_menu_create_test_bookings') }}</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->user_account->account_type == 4)
                <li class="m-menu__item {{ (\Request::route()->getName() == 'report-exception-log') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                    <a href="{{ route('testUser') }}" class="m-menu__link ">
                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                            <span></span>
                        </i>
                        <span class="m-menu__link-text">{{ __('admin/leftnav.test_user') }}</span>
                    </a>
                </li>
                <li class="m-menu__item {{ (\Request::route()->getName() == 'report-exception-log') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                    <a href="{{ route('testProperty') }}" class="m-menu__link ">
                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                            <span></span>
                        </i>
                        <span class="m-menu__link-text">{{ __('admin/leftnav.test_property') }}</span>
                    </a>
                </li>
                <li class="m-menu__item {{ (\Request::route()->getName() == 'report-exception-log') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                    <a href="{{ route('testBooking') }}" class="m-menu__link ">
                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                            <span></span>
                        </i>
                        <span class="m-menu__link-text">{{ __('admin/leftnav.test_booking') }}</span>
                    </a>
                </li>

                <li class="m-menu__item {{ (\Request::route()->getName() == 'testWriteAccess') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                    <a href="{{ route('testWriteAccess') }}" class="m-menu__link ">
                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                            <span></span>
                        </i>
                        <span class="m-menu__link-text">{{ __('admin/leftnav.test_write_access') }}</span>
                    </a>
                </li>

                <li class="m-menu__item {{ (\Request::route()->getName() == 'report-exception-log') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                    <a href="{{ route('testCreditCard') }}" class="m-menu__link ">
                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                            <span></span>
                        </i>
                        <span class="m-menu__link-text">{{ __('admin/leftnav.test_credit_card') }}</span>
                    </a>
                </li>
                <li class="m-menu__item {{ (\Request::route()->getName() == 'adminAudits') ? 'm-menu__item--active' : '' }} " aria-haspopup="true">
                    <a href="{{ route('adminAudits') }}" class="m-menu__link ">
                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                            <span></span>
                        </i>
                        <span class="m-menu__link-text">{{ __('admin/leftnav.audit') }}</span>
                    </a>
                </li>

                <li class="m-menu__item {{ (\Request::route()->getName() == 'report-exception-log') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                    <a href="{{ route('report-exception-log') }}" class="m-menu__link ">
                        <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                            <span></span>
                        </i>
                        <span class="m-menu__link-text">{{ __('admin/leftnav.report_exception_log') }}</span>
                    </a>
                </li>
            @endif

            <li class="m-menu__item {{ (\Request::route()->getName() == 'report-pms-request-count') ? 'm-menu__item--active' : '' }}"  aria-haspopup="true">
                <a href="{{ route('report-pms-request-count') }}" class="m-menu__link ">
                    <i class="m-menu__link-bullet m-menu__link-bullet--dot">
                        <span></span>
                    </i>
                    <span class="m-menu__link-text">{{ __('admin/leftnav.report_pms_request_count') }}</span>
                </a>
            </li>
            
        </ul>
    </div>
    <!-- END: Aside Menu -->
</div>
<!-- END: Left Aside -->
