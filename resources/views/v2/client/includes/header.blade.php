
{{--<div style="display:block;float:left;height:26px;overflow:hidden;position:relative;width:100%;">--}}
{{--  <div class="alert fade show newDashboardStrip primary-light-color" role="alert">--}}
{{--    Switch to <strong><a href="{{ route(returnRoute(Request::route()->getName())) }}"> Legacy Dashboard </a> </strong>--}}
{{--  </div>--}}
{{--</div>--}}

<header>

  <div class="main-nav">
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
      <a class="navbar-brand" href="{{ route('v2dashboard') }}">
        <img src="{{ asset('images/favicon.png') }}" alt="ChargeAutomation" width="90"/>
      </a>
      <a class="navbar-btn navbar-toggler" role="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
      </a>
      <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav ml-auto">
          <a class="nav-item nav-link {{ (\Request::route()->getName() == 'v2dashboard') ? 'active' : '' }}" href="{{ route('v2dashboard')}}">
            <i class="fas fa-chart-pie"> </i> Dashboard <span class="sr-only">(current)</span>
          </a>

          @if(Gate::check('bookings'))
            <a class="nav-item nav-link {{ (\Request::route()->getName() == 'v2bookings' || \Request::route()->getName() =='v2booking_details' || \Request::route()->getName() =='bookingDetailPage') ? 'active' : '' }}" href="{{ route('v2bookings') }}">
              <i class="fas fa-calendar-check"> </i> Bookings
            </a>
          @endif
          @if(Gate::check('properties'))
            <a class="nav-item nav-link {{ (\Request::route()->getName() == 'v2properties'  ? 'active' : '') }}" href="{{ route('v2properties') }}">
              <i class="fas fa-home"></i> Properties
            </a>
          @endif


            <div class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle {{--caret-none--}}
                {{ (\Request::route()->getName() == 'upsells' || \Request::route()->getName() == 'upsellAdd' || \Request::route()->getName() == 'upsellOrders' ) ? 'active' : '' }}"

                   href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cart-arrow-down"> </i> Upsell
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown" style="margin-top:12px;">
                    <a class="dropdown-item {{ \Request::route()->getName() == 'upsells'  ? 'active_inner_link' : '' }}" href="{{ route('upsells') }}">Manage Upsell</a>
                    <a class="dropdown-item {{ \Request::route()->getName() == 'upsellOrders'  ? 'active_inner_link' : '' }}" href="{{ route('upsellOrders') }}">View Orders</a>
                </div>
            </div>

          @if(Gate::check('full client') || Gate::check('guestExperience') || Gate::check('preferences') || Gate::check('deleteSetting') || Gate::check('accountSetup'))
            <div class="nav-item dropdown">
              <a id="navbarDropdown" class="nav-link dropdown-toggle {{--caret-none--}} {{ (\Request::route()->getName() == 'v2pmsintegration' || \Request::route()->getName() == 'viewPMS_SetupStep1' || \Request::route()->getName() == 'viewPMS_SetupStep2' || \Request::route()->getName() == 'viewPMS_SetupStep3' || \Request::route()->getName() == 'viewPMS_SetupStep4' || \Request::route()->getName() == 'viewPMS_SetupStep5' || \Request::route()->getName() == 'v2generalSettings' || \Request::route()->getName() == 'v2settings' || \Request::route()->getName() == 'v2email_settings' || \Request::route()->getName() == 'tac' || \Request::route()->getName() == 'tacAdd' || \Request::route()->getName() == 'guideBooks' || \Request::route()->getName() == 'guideBooksAdd' || \Request::route()->getName() == 'getGuideBookTypes' || \Request::route()->getName() == 'guideBooksAddType' ) ? 'active' : '' }}" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-cog"> </i> Settings
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown" style="margin-top:12px;">
                  @if(Gate::check('accountSetup'))
                      <a class="dropdown-item {{ (\Request::route()->getName() == 'v2pmsintegration'  || \Request::route()->getName() == 'viewPMS_SetupStep1' || \Request::route()->getName() == 'viewPMS_SetupStep2' || \Request::route()->getName() == 'viewPMS_SetupStep3' || \Request::route()->getName() == 'viewPMS_SetupStep4' || \Request::route()->getName() == 'viewPMS_SetupStep5' ) ? 'active_inner_link' : '' }}" href="{{ route('v2pmsintegration') }}">Account Setup</a>
                      {{--<a class="dropdown-item {{ \Request::route()->getName() == 'bookingChannelSetting'  ? 'active_inner_link' : '' }}" href="{{ route('bookingChannelSetting') }}">Booking Sources</a>--}}
                  @endif
                  @if(Gate::check('guestExperience'))
                          <a class="dropdown-item {{ (\Request::route()->getName() == 'v2generalSettings') ? 'active_inner_link' : '' }}" href="{{ route('v2generalSettings') }}">Online Check-in</a>
                  @endif
                  {{-- GUIDE BOOK --}}
                  <a class="dropdown-item {{ (\Request::route()->getName() == 'guideBooks' || \Request::route()->getName() == 'guideBooksAdd' || \Request::route()->getName() == 'getGuideBookTypes' || \Request::route()->getName() == 'guideBooksAddType' ) ? 'active_inner_link' : '' }}" href="{{ route('guideBooks') }}">Guidebook</a>
                  @if(Gate::check('preferences'))
                      <div class="parent-div">
                          <a class="dropdown-item main-item {{ (\Request::route()->getName() == 'v2settings') ? 'active_inner_link' : '' }}" href="{{ route('v2settings') }}">
                              Preferences
                          </a>
                          <ul class="chield-links-group">
                              <li>
                                  <a class="dropdown-item @if(\Request::route()->getName() == 'v2settings' && last(request()->segments()) == 'preferences' ) {{ 'active_inner_link' }} @endif" href="{{ route('v2settings', ['page'=>'preferences']) }}" >PMS Modification</a>
                                  <a class="dropdown-item @if(\Request::route()->getName() == 'v2settings' && last(request()->segments()) == 'notifications' ) {{ 'active_inner_link' }} @endif" href="{{ route('v2settings', ['page'=>'notifications']) }}" >Notifications</a>
                                  <a class="dropdown-item @if(\Request::route()->getName() == 'v2settings' && last(request()->segments()) == 'manageEmails' ) {{ 'active_inner_link' }} @endif" href="{{ route('v2settings', ['page'=>'manageEmails']) }}" >Manage Emails</a>
                                  <a class="dropdown-item @if(\Request::route()->getName() == 'v2settings' && last(request()->segments()) == 'bookingSources' ) {{ 'active_inner_link' }} @endif" href="{{ route('v2settings', ['page'=>'bookingSources']) }}" >Booking Sources</a>
                              </li>
                          </ul>
                      </div>
                  @endif
                  {{-- TAC --}}
                  <a class="dropdown-item {{ (\Request::route()->getName() == 'tac' || \Request::route()->getName() == 'tacAdd' ) ? 'active_inner_link' : '' }}" href="{{ route('tac') }}">Manage Terms </a>
                  @if(Gate::check('full client'))
                          {{--<a class="dropdown-item {{ (\Request::route()->getName() == 'v2email_settings') ? 'active_inner_link' : '' }}" href="{{ route('v2email_settings') }}">Manage Emails </a>--}}
                  @endif
              </div>
            </div>
          @endif
        </div>
      </div>
    </nav>
  </div>

  @if(Gate::check('full client'))
    <edit-full-client></edit-full-client>
  @else
    <edit-team-member></edit-team-member>
  @endif
</header>
@if(Auth::user()->user_account->status != 1 || empty(auth()->user()->user_account->integration_completed_on) )
  <div id="showCardUpdateAlert">
    <div class="container">
      <div class="row">
          @if( Request::route()->getName() != 'v2pmsintegration'
          && Request::route()->getName() != 'viewPMS_SetupStep1'
          && Request::route()->getName() != 'viewPMS_SetupStep2'
          && Request::route()->getName() != 'viewPMS_SetupStep3'
          && Request::route()->getName() != 'viewPMS_SetupStep4'
          && Request::route()->getName() != 'viewPMS_SetupStep5')
              @if( auth()->user()->hasRole(config('db_const.user.roles.ROLE_ADMINISTRATOR')) )
                  <div class="col-12 alert alert-msg color10 text-center" role="alert">
                      <strong> <i class="fas fa-exclamation-triangle"></i> </strong>
                      @if( empty(auth()->user()->user_account->integration_completed_on) )
                          {{__('attention_message.client.pms_integration_incomplete_other_pages')}}
                      @else
                          {{__('attention_message.client.account_deactive')}}
                      @endif
                      <a href="{{route('v2pmsintegration')}}" class="btn btn-primary btn-sm m-btn m-btn--pill m-btn--wide alert_link">Re-Activate My Account</a>
                  </div>
              @else
                  <div class="col-12 alert alert-msg color10 text-center" role="alert">
                      <strong> <i class="fas fa-exclamation-triangle"></i> </strong>
                      @if( empty(auth()->user()->user_account->integration_completed_on) )
                          {{__('attention_message.client.pms_integration_incomplete_other_pages')}}
                      @else
                          {{__('attention_message.client.account_deactive')}}
                      @endif
                  </div>
              @endif
          @else
              <div class="col-12 alert alert-msg color10 text-center" role="alert">
                  <strong> <i class="fas fa-exclamation-triangle"></i> </strong>
                  @if( empty(auth()->user()->user_account->integration_completed_on) && empty(auth()->user()->user_account->pms))
                      {{__('attention_message.client.pms_integration_incomplete_pms_integration_page')}}
                  @else
                      {{__('attention_message.client.account_deactive')}}
                  @endif
              </div>
          @endif
      </div>
    </div>
  </div>
@endif

