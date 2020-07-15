<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
    @include('includes.common.head')

    @stack('b_css')

    <!-- begin::Body -->
    <body  class="m-page--fluid m--skin- m-content--skin-light2 m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--fixed m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default m-scroll-top--shown">

        <!-- begin:: Page -->
        <div class="m-grid m-grid--hor m-grid--root m-page">
            
            @include('includes.client.header')

            <!-- begin::Body -->
            <div class="m-content m-grid__item m-grid__item--fluid m-grid m-grid--ver-desktop m-grid--desktop m-body">

                @include('includes.client.leftnav')

                <div class="m-grid__item m-grid__item--fluid m-wrapper">
                    @if(auth()->user()->email_verified_at == null)
                        <div class="col-4">
                            <div class="alert alert-info" role="alert">
                                <strong>Please</strong> Verify your email first.
                            </div>
                        </div>
                    @else
                        @include('partials.client.alerts')
                    @yield('content')

                    @endif


                </div>
                  </div>

                @include('includes.common.footer')

                </div>
                <!-- end:: Page -->
                @include('includes.client.rightbar')

                <!-- begin::Scroll Top -->
                <div id="m_scroll_top" class="m-scroll-top">
                    <i class="la la-arrow-up"></i>
                </div>
                <input type="hidden" name="last_notify_id" id="last_id" value="0">
                <input type="hidden" name="pre_readed" id="pre_readed" value="0">

                <!-- end::Scroll Top -->            <!-- begin::Quick Nav -->
                <!-- <ul class="m-nav-sticky" style="margin-top: 30px;">
                    <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Purchase" data-placement="left">
                        <a href="https://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" target="_blank"><i class="la la-cart-arrow-down"></i></a>
                    </li>
                    <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Documentation" data-placement="left">
                        <a href="https://keenthemes.com/metronic/documentation.html" target="_blank"><i class="la la-code-fork"></i></a>
                    </li>
                    <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Support" data-placement="left">
                        <a href="https://keenthemes.com/forums/forum/support/metronic5/" target="_blank"><i class="la la-life-ring"></i></a>
                    </li>
                </ul> -->
                <!-- begin::Quick Nav -->
                <!-- baseScript -->
                @include('includes.common.common_base_script')
                @include('includes.common.dashboard_script')
                @yield('ajax_script')

    {{--  <script type="application/javascript">

        /*Vue.component('notifications', {

        props: ['todo'],
        template:`
        <div class="m-list-timeline__item">
            <span class="m-list-timeline__badge -m-list-timeline__badge--state-success"></span>
            <span class="m-list-timeline__text">@{{todo.data.userData}}</span>
            <span class="m-list-timeline__time">@{{todo.created_at | moment("ddd, hA") }}</span>
        </div>`
        })

        var app9 = new Vue({
        el: '#app-9',
        data: {
            actv: ' m-badge--danger',
        newNotificationList: @json(auth()->user()->unreadNotifications)
        },

        mounted(){

        console.log(@json(auth()->user()->unreadNotifications));

        }
        })*/ 
   </script>--}}


   <script type="text/javascript">

    var tzZZZ = moment.tz.guess();
    $(function (){
      getMsgs();
    });
    
    setInterval(function(){ 
      getMsgs();
    }, 60000);

    function getMsgs () {
      var id = $("#last_id").val();
      var pre_readed = parseInt($("#pre_readed").val());
      if(pre_readed == 0 ){
        latestReadedMsgs();
        //return;
      }

       $.ajaxSetup({ headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  } });
       $.ajax({

           type:'POST',
           url:'@php echo route('communicationNotifyAlerts'); @endphp',
           dataType:'json',
           data:{'id':id},

            success:function(data){
                var updateLast = true;
                if(data.status == true){
                    if(data.count > 0){
                        $("#notificationCounts").show();
                        $("#notificationCounts").html(data.count);

                        $("#notificationCounts2").html(data.count);
                    }

                    $.each(data.msgs, function (key, msg) {
                        if(updateLast){
                            $("#last_id").val(msg.id);
                            updateLast = false;
                        }
                        msg.created_at = moment(msg.created_at).tz(tzZZZ);

                        $('#newMsg').prepend('<div class="m-list-timeline__item"  onclick="moveToCommunication('+msg.booking_info_id+')" style="cursor:pointer" >'+
                            '<span class="m-list-timeline__badge -m-list-timeline__badge--state-success"></span>'+
                            '<span class="m-list-timeline__text" style="color:rgb(54, 163, 247)" > New msg from  '+msg.guest_title+' '+msg.guest_name+' Booking#'+msg.pms_booking_id+'</span>'+
                            '<span class="m-list-timeline__time">'+msg.created_at.format('DD MMMM YYYY hh:mm a')+'</span>'+
                        '</div>');
                    });
                }                
            }
        });
     }

    function latestReadedMsgs() {
      
     $.ajaxSetup({ headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  } });
     $.ajax({

         type:'POST',
         url:'@php echo route('latestReadedMsgs'); @endphp',
         dataType:'json',
         data:{},

          success:function(data){
          
            $.each(data.msgs, function (key, msg) {
              msg.created_at = moment(msg.created_at).tz(tzZZZ);

              $('#newMsg').prepend('<div class="m-list-timeline__item"  onclick="moveToCommunication('+msg.booking_info_id+')" style="cursor:pointer" >'+
                  '<span class="m-list-timeline__badge -m-list-timeline__badge--state-success"></span>'+
                  '<span class="m-list-timeline__text" style="color:rgb(54, 163, 247)" > New msg from  '+msg.guest_title+' '+msg.guest_name+' Booking#'+msg.pms_booking_id+'</span>'+
                  '<span class="m-list-timeline__time">'+msg.created_at.format('DD MMMM YYYY hh:mm a')+'</span>'+
              '</div>');
            });
           $("#pre_readed").val('1');
          }
      });
    }


    function  readed() {
      
      var id = $("#last_id").val();

       $.ajaxSetup({ headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  } });

       $.ajax({

           type:'POST',
           url:'@php echo route('communicationNotifyAlertsReaded'); @endphp',
           dataType:'json',
           data:{'id':id},

          success:function(data){
             
             $("#notificationCounts").html(''); 
             $("#notificationCounts").hide();
              // $("#notificationCounts").html(0);   
              // $("#notificationCounts2").html('');    
            }
        });    
    }
    function moveToCommunication(bk_id) {
         window.location.href ='/client/booking_details/'+bk_id+'#m_tabs_7_4';
    }

    @if(config('app.url') == 'https://app.chargeautomation.com')
    //Set your APP_ID
    let APP_ID = "gz9kbn9t";
    let v2_user_id = "{{ auth()->user()->id }}";
    let v2_user_name = "{{ auth()->user()->name }}";
    let v2_user_email = "{{ auth()->user()->email }}";
    let v2_user_phone = "{{ auth()->user()->phone }}";

    let v2_email_verified = "{{ (auth()->user()->email_verified_at != NULL) ? 'Verified' : 'Unverified' }}";
    let v2_account_status = "{{ (auth()->user()->status == 1) ? 'Active' : 'Deactivate' }}";
    let v2_account_type = "{{ (auth()->user()->parent_user_id > 0) ? 'Team Member' : 'Main Account' }}";

    let v2_user_active_properties = "{{ auth()->user()->user_account->properties_info->where('status', '=', '1')->count() }}";
    let v2_user_deactive_properties = "{{ auth()->user()->user_account->properties_info->where('status', '=', '2')->count() }}";

    let v2_user_account_id = "{{ auth()->user()->user_account->id }}";
    let v2_user_account = "{{ auth()->user()->user_account->name }}";
    let v2_user_created_at = "{{ auth()->user()->created_at }}";
    let v2_user_hash = "{{ hash_hmac('sha256', auth()->user()->id, 'zGJ7O7dVhSViWAEwgvz5Yog6WqOPyqAU4yWxKfGM') }}";



    window.intercomSettings = {
        app_id: APP_ID,
        user_id: v2_user_id,
        name: v2_user_name,
        email: v2_user_email,
        phone: v2_user_phone,

        email_verified: v2_email_verified,
        account_status: v2_account_status,
        account_type: v2_account_type,

        connected_Properties: v2_user_active_properties,
        non_Connected_Properties: v2_user_deactive_properties,

        user_account_id: v2_user_account_id,
        user_account: v2_user_account,
        created_at: v2_user_created_at,
        user_hash: v2_user_hash,

        company: v2_user_account,
        horizontal_padding: 80,
        avatar: {
            "type": "avatar",
            "image_url" :"{{ config('app.url') . '/storage/uploads/companylogos/' . auth()->user()->user_account->company_logo }}"
        }
    };
    (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/' + APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
@endif


</script>

</body>
    <!-- end::Body -->
</html>