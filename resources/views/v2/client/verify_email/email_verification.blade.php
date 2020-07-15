<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
  @include('includes.common.head')
  <body style="background:#D9E2EC" class="email_verification m--skin- m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--fixed m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default">
    <article class="affiliates-header">
      <div class="content">
        <div class="container">
          <div class="wrap">
            <div class="fix-9-12">
              <div style="margin: 0 0 20px 0;text-align: center;"><a href="https://chargeautomation.com"><img src="{{asset('images/favicon.png')}}"></a></div>
              <div class="white-box fromTop align-center" id="app">
                <h3>Confirm Your Email</h3>
                <h2>Hello {{  ucwords($name) }}</h2>
                <p style="opacity:0.8 !important;">
                  Please check your inbox for confirmation email. Click the link in the email to confirm your email address.
                </p>
                <p style="opacity:0.8 !important;">
                  After you confirm click Continue.
                </p>
                <div style="margin-top:43px;text-align:center;">
                  <a href="{{ route('v2dashboard') }}" class="btn btn-sm btn-green" id="ContinueBtn"
                  >
                    Continue
                  </a>
                  <button type="button" class="btn btn-sm btn-default" id="ResendEmailBtn" autocomplete="off"
                    data-toggle="button" aria-pressed="false"
                    @click.prevent="resendEmail">
                    Resend Email
                  </button>
                  <p v-html="waitmsg" style="font-size:10px;color:#999;margin-top:7px">&nbsp;</p>
                  <hr>
                  <div>
                    <p style="opacity:0.8 !important;">
                      <a href="#"  @click="resetSession('login')" class="EmVerLink"> &#8617; Back to Login Page</a>
                    </p>

                    <p style="margin:0px 0px 3px 0px">
                      <span class="m-login__account-msg"> Powered by </span>
                      <a href="https://chargeautomation.com/" class="EmVerLink"> ChargeAutomation </a> •
                      <a href="https://chargeautomation.com/contact-us" class="EmVerLink" target="_blank">Contact Us</a>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </article>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-124409336-1"></script>
    <script src="{{ asset('v2/js/google_analytics_code.js') }}"></script>
    <script src="{{ asset('assets/vendors/base/vendors.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/demo/default/base/scripts.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/demo/default/custom/components/base/sweetalert2.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.21/vue.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.min.js" type="text/javascript"></script>
    <script>
      var Email = new Vue({
        el: '#app',
        data() {
          return {
            id: '{{$id}}',
            waitmsg: '&nbsp;',
          }
        },
        methods: {
          resendEmail: function () {
            let _this = this;
            mApp.block("#app", {
              type: "loader",
              state: "success",
              message: "Please wait..."
            });
            axios({
              url: '/ResendEmail/' + this.id,
              method: 'GET',
              dataType: 'JSON',
              headers: {
                'application': 'application/json',
                'content-type': 'application/json'
              },
            })
            .then(function(resp) {
              if (resp.data.status == true) {
                toastr.success(resp.data.msg);
                mApp.unblock("#app");
                var e = document.getElementById("ResendEmailBtn");
                e.disabled = true;
                e.classList.add("not_send");
                var seconds = 16;
                var countdown = setInterval(function() {
                  seconds--;
                  _this.waitmsg='Button will activate after ' + seconds + ' seconds';
                  if (seconds <= 0) clearInterval(countdown);
                }, 1000);
                setTimeout(function() {
                  e.disabled = false;
                  e.classList.remove("not_send");
                  clearInterval(countdown);
                  _this.waitmsg='&nbsp;';
                }, 16000);
              } else {
                toastr.error(resp.data.msg);
                mApp.unblock("#app");
                if (resp.data.msg.indexOf('Already Verified') > -1)
                  window.location.href = '/login#';
              }
            })
            .catch(function(error){
              console.log(error);
              toastr.error('Fail to Send Email.');
              mApp.unblock("#show_loader");
            });
          },

          resetSession: function (requestPage) {
            mApp.block("#app", {type: "loader", state: "success", message: "Please wait..."});
            axios({
              url: '/reset-session',
              method: 'POST',
              dataType: 'JSON',
              headers: {
                'application': 'application/json',
                'content-type': 'application/json'
              },
            }).then(function(resp) {
                    window.location.href = '/login#'+requestPage
                    })
                    .catch(function(error){
                      console.log(error);
                      toastr.error('Fail to Redirect.');
                      mApp.unblock("#app");
                    });
          },

        },
        mounted() {}
      });
    </script>
  </body>
</html>