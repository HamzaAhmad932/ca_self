<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('v2.guest.includes.head_file_new')
    @stack('below_css')
</head>
<body>
<div class="guest-portal new-design-20200405" id="app">
    <!--Guest Portal-->
    <div class="gp-page">

        @if(!empty($header))
        <guest-chat-panel calling_id="chat_panel_right" v-if="{{$header['is_chat_active']}}"></guest-chat-panel>
        @endif

        @yield('page_content')

    </div>

    @if(!empty($header['is_chat_active']))
        <pre-checkin-chat-button
                :is_chat_active="true"
                :booking_id="{{$header['booking']->id}}">
        </pre-checkin-chat-button>
    @endif
</div>

@include('v2.guest.includes.footer_script')
@stack('below_script')
</body>
</html>

<style>
    .gp-box-steps {
        display: block;
        float: left;
        overflow: hidden;
        width: 100%;
    }

    gp-box-steps {
        border: 1px solid #fff;
        /*background: #F0F4F8;*/
        padding: 1rem;
        border-radius: 4px 4px 0 0;
    }


    a.gp-step {
        overflow: visible !important;
        background: #D9E2EC;
        border-radius: 4px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        flex: 1 1 0;
        color: #102A43;
        position: relative;
    }

    a:not([href]):not([tabindex]) {
        color: inherit;
        text-decoration: none;
    }

    .gp-step.active:after {
        display: block;
        content: '';
        position: absolute;
        left: 50%;
        margin-left: -12px;
        width: 0;
        height: 0;
        border-left: 12px solid transparent;
        border-right: 12px solid transparent;
        border-bottom: 12px solid #fff;
        bottom: -16px;
    }

    a.gp-step.active {
        background: #B6E0FE;
        color: #04478d;
    }

    .gp-step.active[data-v-0c9f5752]::after {
        display: block;
        content: '';
        position: absolute;
        left: 50%;
        margin-left: -12px;
        width: 0;
        height: 0;
        border-left: 12px solid transparent;
        border-right: 12px solid transparent;
        border-bottom: 12px solid #fff;
        bottom: -16px;
    }

    .gp-box .gp-box-steps {
        /*display: -webkit-box;*/
        display: flex;
        margin: 0px;
        padding: 1rem !important;
    }

    .gp-box-steps {
        border: 1px solid #fff;
        /*background: #F0F4F8;*/
        padding: 1rem;
        border-radius: 4px 4px 0 0;
    }
</style>