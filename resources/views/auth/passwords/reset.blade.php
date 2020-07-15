<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
  @include('includes.common.head')
  <body class="m--skin- m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--fixed m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default">
    <div class="m-grid m-grid--hor m-grid--root m-page">
      <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor m-login m-login--signin m-login--2 m-login-2--skin-1" id="m_login">
        <div>
          <div class="m-grid__item m-grid__item--fluid m-login__wrapper">
            <div class="m-login__container" id="m-login__container">
              <div class="line alert-outer">
                @if(session('alerts'))
                  <div class="alert alert-custom alert-{{session('alerts.cls')}} alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                    {{ session('alerts.message') }}
                  </div>
                @endif
              </div>
              <div class="line m-login__logo">
                <a href="#"><img src="{{asset('images/favicon.png')}}"></a>
              </div>
              <div class="line forms-portion-outer" id="app">
                <div class="m-login__signin">
                  <div class="form-body-outer">
                    <div class="m-login__head">
                      <h3 class="m-login__title" id="Langlogin"> {{ __('main/text.reset_password') }} </h3>
                    </div>
                    <div class="error_wrapper">&nbsp;</div>
                    <form class="m-login__form m-form design-inputs" action="{{ route('password.request') }}" method="POST" role="form">
                      @csrf
                      <input type="hidden" name="token" value="{{ $token }}">
                      <div class="form-group m-form__group">
                        <input type="email" name="email" id="email" class="{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="Email"  value="{{ $email ?? old('email') }}" >
                        <div class="error_wrapper">
                          @if ($errors->has('email'))
                            <span class="invalid-feedback" role="alert">{{ $errors->first('email') }}</span>
                          @endif
                        </div>
                      </div>
                      <div class="form-group m-form__group">
                        <input type="password" name="password" id="password" class="{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="Password" >
                        <div class="error_wrapper">
                          @if ($errors->has('password'))
                            <span class="invalid-feedback" role="alert">{{ $errors->first('password') }}</span>
                          @endif
                        </div>
                      </div>
                      <div class="form-group m-form__group">
                        <input type="password" name="password_confirmation" id="password-confirm" placeholder="Confirm Password"  >
                        <div class="error_wrapper">&nbsp;</div>
                      </div>
                      <div class="m-login__form-action">
                        <button type="submit" id="_signup_submit" class="btn btn-sm btn-green" >
                          {{ __('main/text.reset_password') }}
                        </button>
                      </div>
                    </form>
                  </div>
                  <div class="form-footer-outer">
                    <p>
                      <span class="m-login__account-msg"> Already have an account? </span>
                      <a href="{{ route('login') }}">Login</a>
                      <!-- class="m-link m-link--light m-login__account-link" -->
                    </p>
                    <p>
                      For Charge Automation Version 1, <a href="https://chargeautomation.com/root_app/login.php">Click here</a>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @include('includes.common.common_base_script')
    @include('includes.login_base_script')
    <script src="{{ asset('assets/demo/default/custom/components/base/toastr.js') }}" type="text/javascript"></script>
  </body>
</html>