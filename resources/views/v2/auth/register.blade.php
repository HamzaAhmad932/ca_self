@extends('v2.auth.app')
@push('below_css')

@endpush
@section('page_content')
    <register></register>
@endsection
@push('below_script')
    <script>
        //Intercom Code
        let APP_ID = "{{ config('db_const.auth_keys.intercom_app_id') }}";
        window.intercomSettings = {
            app_id: APP_ID,
        };
        (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/' + APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
        function createUserOnIntercom(user_obj, user_account_obj, signup_user_hash) {
            Intercom('update', {
                app_id: APP_ID,
                last_request_at: parseInt((new Date()).getTime()/1000),
                user_id: user_obj.id,
                name: user_obj.name,
                email: user_obj.email,
                phone: user_obj.phone,
                is_administrator: true,
                role:"Administrator",
                booking_fetch_enabled:true,
                pms_when_signing_up: user_account_obj.current_pms,
                user_signed_up_on: user_obj.created_at,
                user_hash: signup_user_hash,

                company: {
                    id: user_account_obj.id,
                    name: user_account_obj.name,
                    administrator_user_id: user_obj.id,
                    administrator_email: user_obj.email,
                    pms_when_signing_up: user_account_obj.current_pms,
                    company_created_at: user_account_obj.created_at
                }
            });
        }
    </script>
@endpush