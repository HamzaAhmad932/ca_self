
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script type="application/javascript" defer src="https://js.stripe.com/v3/"></script>

<script>
    jQuery(document).ready(function($) {
        var alterClass = function() {
            var ww = document.body.clientWidth;
            if (ww < 1199) {
                $('#wrapper').removeClass('sidebar-displayed');
            } else if (ww >= 1200) {
                //$('#wrapper').addClass('sidebar-displayed');
            };
        };

        $(window).resize(function(){
            alterClass();
        });
        //Fire it when the page first loads:
        alterClass();
    });

</script>

@if(!empty(auth()->user()) )
    <script>
        //Set your APP_ID
        let APP_ID = "{{ config('db_const.auth_keys.intercom_app_id') }}";
        let user_id = "{{ auth()->user()->id }}";
        let user_name = "{{ auth()->user()->name }}";
        let user_email = "{{ auth()->user()->email }}";
        let user_phone = "{{ auth()->user()->phone }}";
        let current_user_hash = "{{ hash_hmac('sha256', auth()->user()->id, 'zGJ7O7dVhSViWAEwgvz5Yog6WqOPyqAU4yWxKfGM') }}";

        let email_verified = "{{ (auth()->user()->email_verified_at != NULL) ? 'true' : 'false' }}";
        let user_status = "{{ (auth()->user()->status == 1) ? 'true' : 'false' }}";
        let account_type = "{{ (auth()->user()->parent_user_id > 0) ? 'Team Member' : 'Administrator' }}";
        let is_admin = "{{ (auth()->user()->parent_user_id > 0) ? 'false' : 'true' }}";
        let user_created_at = "{{ auth()->user()->created_at }}";

        let user_active_properties = "{{ auth()->user()->user_account->properties_info->where('status', '=', '1')->count() }}";
        let user_deactive_properties = "{{ auth()->user()->user_account->properties_info->where('status', '=', '0')->count() }}";

        let user_account_id = "{{ auth()->user()->user_account->id }}";
        let user_account_name = "{{ auth()->user()->user_account->name }}";
        let user_account_status = "{{ (auth()->user()->user_account->status == 1) ? 'true' : 'false' }}";
        let company_created_at = "{{ auth()->user()->user_account->created_at }}";

        window.intercomSettings = {
            app_id: APP_ID,
            user_id: user_id,
            name: user_name,
            email: user_email,
            phone: user_phone,
            email_verified: (email_verified == 'true'),
            is_administrator: (is_admin == 'true'),
            role: account_type,
            user_is_active: (user_status == 'true'),
            user_signed_up_on: user_created_at,
            connected_properties: user_active_properties,
            disconnected_properties: user_deactive_properties,
            total_properties: (+user_active_properties + +user_deactive_properties),
            user_hash: current_user_hash,
            company: {
                id: user_account_id,
                name: user_account_name,
                account_is_active: (user_account_status == 'true'),
                company_created_at: company_created_at
            },
            avatar: {
                "type": "avatar",
                "image_url" :"{{ config('app.url') . '/storage/uploads/companylogos/' . auth()->user()->user_account->company_logo }}"
            }
        };
        (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/' + APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();

        function updateIntercomData(event_name, update_dataset) {
            update_dataset = update_dataset || {};

            setTimeout(function(){
                switch(event_name)
                {
                    case 'properties_connected':
                        user_active_properties = +user_active_properties + update_dataset.no_of_properties;
                        user_deactive_properties = +user_deactive_properties - update_dataset.no_of_properties;

                        update_dataset = {
                            connected_properties: user_active_properties,
                            disconnected_properties: user_deactive_properties,
                            total_properties: (+user_active_properties + +user_deactive_properties),
                            company: {
                                connected_properties: user_active_properties,
                                disconnected_properties: user_deactive_properties,
                                total_properties: (+user_active_properties + +user_deactive_properties)
                            }
                        }
                        break;

                    case 'properties_disconnected':
                        user_active_properties = +user_active_properties - update_dataset.no_of_properties;
                        user_deactive_properties = +user_deactive_properties + update_dataset.no_of_properties;

                        update_dataset = {
                            connected_properties: user_active_properties,
                            disconnected_properties: user_deactive_properties,
                            total_properties: (+user_active_properties + +user_deactive_properties),
                            company: {
                                connected_properties: user_active_properties,
                                disconnected_properties: user_deactive_properties,
                                total_properties: (+user_active_properties + +user_deactive_properties)
                            }
                        }
                        break;

                    case 'property_connected':
                        user_active_properties = +user_active_properties + 1;
                        user_deactive_properties = +user_deactive_properties - 1;

                        update_dataset = {
                            connected_properties: user_active_properties,
                            disconnected_properties: user_deactive_properties,
                            total_properties: (+user_active_properties + +user_deactive_properties),
                            company: {
                                connected_properties: user_active_properties,
                                disconnected_properties: user_deactive_properties,
                                total_properties: (+user_active_properties + +user_deactive_properties)
                            }
                        }
                        break;

                    case 'property_disconnected':
                        user_active_properties = +user_active_properties - 1;
                        user_deactive_properties = +user_deactive_properties + 1;

                        update_dataset = {
                            connected_properties: user_active_properties,
                            disconnected_properties: user_deactive_properties,
                            total_properties: (+user_active_properties + +user_deactive_properties),
                            company: {
                                connected_properties: user_active_properties,
                                disconnected_properties: user_deactive_properties,
                                total_properties: (+user_active_properties + +user_deactive_properties)
                            }
                        }
                        break;

                    case 'payment_gateway_saved':
                        update_dataset = {
                            payment_gateway_connected: true,
                            company: {
                                payment_gateway_connected: true
                            }

                        }
                        break;

                    case 'pms_connected':
                        var selected_pms = '-';
                        var pms_user_name = '-';
                        if(typeof update_dataset.credentials != "undefined") {
                            pms_user_name = (typeof update_dataset.credentials["username"] != "undefined") ? update_dataset.credentials["username"]:'-';
                        }
                        if(typeof update_dataset.selected_pms != "undefined") {
                            selected_pms = update_dataset.selected_pms;
                        }
                        update_dataset = {
                            pms_connected_status: true,
                            pms_username: pms_user_name,
                            selected_pms: selected_pms,
                            company: {
                                pms_connected_status: true,
                                pms_username: pms_user_name,
                                selected_pms: selected_pms
                            }

                        }
                        break;

                    case 'guest_experience':
                        var guest_experience_enabled = $('.checkbox-toggle.checkbox-choice.float-right input').is(':checked');
                        var guest_experience_email_to_guest = $('.checkbox-toggle.checkbox-choice.float-right input[name="Emails to Guest"]').is(':checked');
                        var guest_experience_collect_email_phone = $('.checkbox-toggle.checkbox-choice.float-right input[name="Collect Email & phone number from the guest"]').is(':checked');
                        var guest_experience_collect_arrival_info = $('.checkbox-toggle.checkbox-choice.float-right input[name="Collect Arrival time & arrival method"]').is(':checked');
                        var guest_experience_collect_id = $('.checkbox-toggle.checkbox-choice.float-right input[name="Collect Passport/ID of guest"]').is(':checked');
                        var guest_experience_collect_credit_card = $('.checkbox-toggle.checkbox-choice.float-right input[name="Collect Credit Card Scan of Guest"]').is(':checked');
                        var guest_experience_collect_selfie = $('.checkbox-toggle.checkbox-choice.float-right input[name="Collect Selfie Picture"]').is(':checked');
                        var guest_experience_offer_upsell = $('.checkbox-toggle.checkbox-choice.float-right input[name="Offer Add-on Upsells"]').is(':checked');
                        var guest_experience_collect_signature = $('.checkbox-toggle.checkbox-choice.float-right input[name="Collect Digital Signature"]').is(':checked');
                        var guest_experience_collect_terms = $('.checkbox-toggle.checkbox-choice.float-right input[name="Collect Acceptance of Terms & Conditions"]').is(':checked');
                        var guest_experience_display_guidebook = $('.checkbox-toggle.checkbox-choice.float-right input[name="Display Guidebook on Guest Portal"]').is(':checked');
                        var guest_experience_allow_chat = $('.checkbox-toggle.checkbox-choice.float-right input[name="Allow guest to send Chat message"]').is(':checked');
                        update_dataset = {
                            guest_experience_enabled: guest_experience_enabled,
                            guest_experience_email_to_guest: guest_experience_email_to_guest,
                            guest_experience_collect_email_phone: guest_experience_collect_email_phone,
                            guest_experience_collect_arrival_info: guest_experience_collect_arrival_info,
                            guest_experience_collect_id: guest_experience_collect_id,
                            guest_experience_collect_credit_card: guest_experience_collect_credit_card,
                            guest_experience_collect_selfie: guest_experience_collect_selfie,
                            guest_experience_offer_upsell: guest_experience_offer_upsell,
                            guest_experience_collect_signature: guest_experience_collect_signature,
                            guest_experience_collect_terms: guest_experience_collect_terms,
                            guest_experience_display_guidebook: guest_experience_display_guidebook,
                            guest_experience_allow_chat: guest_experience_allow_chat,
                            company: {
                                guest_experience_enabled: guest_experience_enabled,
                                guest_experience_email_to_guest: guest_experience_email_to_guest,
                                guest_experience_collect_email_phone: guest_experience_collect_email_phone,
                                guest_experience_collect_arrival_info: guest_experience_collect_arrival_info,
                                guest_experience_collect_id: guest_experience_collect_id,
                                guest_experience_collect_credit_card: guest_experience_collect_credit_card,
                                guest_experience_collect_selfie: guest_experience_collect_selfie,
                                guest_experience_offer_upsell: guest_experience_offer_upsell,
                                guest_experience_collect_signature: guest_experience_collect_signature,
                                guest_experience_collect_terms: guest_experience_collect_terms,
                                guest_experience_display_guidebook: guest_experience_display_guidebook,
                                guest_experience_allow_chat: guest_experience_allow_chat
                            }

                        }
                        break;

                    case 'upsell_listing_page_loaded':
                        update_dataset = {
                            has_published_upsell: $('.checkbox-toggle.checkbox-choice input').is(':checked'),
                            company: {
                                has_published_upsell: $('.checkbox-toggle.checkbox-choice input').is(':checked')
                            }
                        }
                        break;

                    case 'guidebook_listing_page_loaded':
                        update_dataset = {
                            has_published_guidebook: $('.checkbox-toggle.checkbox-choice input').is(':checked'),
                            company: {
                                has_published_guidebook: $('.checkbox-toggle.checkbox-choice input').is(':checked')
                            }
                        }
                        break;

                    case 'terms_listing_page_loaded':
                        update_dataset = {
                            has_terms_added: $('.checkbox-toggle.checkbox-choice input').is(':checked'),
                            company: {
                                has_terms_added: $('.checkbox-toggle.checkbox-choice input').is(':checked')
                            }
                        }
                        break;

                    case 'booking_fetch_changed':
                        update_dataset = {
                            booking_fetch_enabled: $('#bookingSources .checkbox-toggle.checkbox-choice input').is(':checked'),
                            company: {
                                booking_fetch_enabled: $('#bookingSources .checkbox-toggle.checkbox-choice input').is(':checked'),
                            }
                        }
                        break;

                    case 'payment_rules_changed':
                        var booking_com = ($('.main_source_checkbox1 input').is(':checked') && $('.inner_source_checkbox1 input').is(':checked'));
                        var agoda = ($('.main_source_checkbox2 input').is(':checked') && $('.inner_source_checkbox2 input').is(':checked'));
                        var expedia = ($('.main_source_checkbox3 input').is(':checked') && $('.inner_source_checkbox3 input').is(':checked'));
                        var ctrip = ($('.main_source_checkbox4 input').is(':checked') && $('.inner_source_checkbox4 input').is(':checked'));

                        update_dataset = {
                            payment_schedule_enabled: (booking_com || agoda || expedia || ctrip),
                            company: {
                                payment_schedule_enabled: (booking_com || agoda || expedia || ctrip)
                            }
                        }
                        break;
                }

                var basic_info_user = {
                    app_id: APP_ID,
                    user_id: user_id,
                    last_request_at: parseInt((new Date()).getTime()/1000), // this key is to make every ping request UNIQUE-- Suggested by Intercom Support
                    user_hash: current_user_hash,
                    company: {
                        id: user_account_id,
                        name: user_account_name
                    }
                }

                Intercom('update', $.extend(true, {}, basic_info_user, update_dataset));
            }, 1000);
        }
    </script>
@endif

@if (config('app.debug') == false && config('app.url') == 'https://app.chargeautomation.com')
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-124409336-1"></script>
    <script src="{{ asset('v2/js/google_analytics_code.js') }}"></script>
@endif