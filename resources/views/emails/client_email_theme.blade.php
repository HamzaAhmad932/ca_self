<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Guest Email
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
        }
        table,
        td,
        tr {
            vertical-align: top;
            border-collapse: collapse;
        }
        * {
            line-height: inherit;
        }
        a[x-apple-data-detectors=true] {
            color: inherit !important;
            text-decoration: none !important;
        }
        .addon-item {
            display: block;
            border: 1px solid #D9E2EC;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .addon-item-header {
            display: flex;
            align-items: center;
            background: #F0F4F8;
            padding: 1rem;
            justify-content: flex-start;
            position: relative;
        }
        .addon-item .addon-item-header-content {
            display: flex;
            align-items: center;
            width: 100%;
        }
        .addon-item .addon-item-header-text {
            display: block;
            flex-direction: column;
            justify-content: center;
        }
        .addon-item-header h4 {
            margin:0rem;
            display: block;
        }
        .addon-item-header p {
            display: block;
            color: #486581 !important;
            font-size: 0.8rem;
            margin: 0.25rem 0 0;
        }
        .addon-item .addon-price {
            white-space: nowrap;
            font-size: 0.75rem;
            padding-right: 0rem;
            padding-left: 1rem;
            margin-left: auto;
        }
        .addon-item .addon-price .price-text {
            color: #1EAF24 !important;
            font-size: 1rem;
            text-align: center !important;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;  background: #F0F4F8; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; padding:0; margin:0;">
<div class="wrapper" style="background: #F0F4F8; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 24px;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; margin-left: auto; margin-right: auto;">
        <tbody>
        <tr>
            <td style="padding: 0 0 0 0;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #D9E2EC; border-collapse: collapse; width: 100% !important; max-width:600px !important;">
                    <tbody>
                    <tr>
                        <td align="left" bgcolor="#ffffff" style="padding: 24px 24px 8px 24px; font-size: 28px; font-weight: bold; font-family: Arial, sans-serif;">
                            <a href="https://chargeautomation.com?utm_source=email&amp;utm_medium=client_email&amp;utm_campaign=poweredbyCA" target="_blank">
                                <img src="https://app.chargeautomation.com/images/favicon.png" height="64" alt="Company Name">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" style="padding: 32px 24px 24px 24px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody>

                                @if(isset($data['email_title']) && is_array($data['email_title']))
                                <tr>
                                    <td style="{{ !isset($data['top_paragraph']) ? 'padding-bottom: 32px;':'' }}color: {{ $data['email_title']['text_color'] }}; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 24px;">
                                        <b>{{ $data['email_title']['text'] }}</b>
                                    </td>
                                </tr>
                                @endif

                                @if(isset($data['top_paragraph']) && !empty($data['top_paragraph']))
                                <tr>
                                    <td style="padding: 24px 0 10px 0; color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 22px;">
                                        {!! $data['top_paragraph'] !!}
                                    </td>
                                </tr>
                                @endif

                                @if(isset($data['top_paragraph_second_line']) && !empty($data['top_paragraph_second_line']))
                                    <tr>
                                        <td style="{{ isset($data['top_paragraph_third_line']) ? 'padding: 12px 0 12px 0':'padding: 12px 0 32px 0' }}; color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 22px;">
                                            {!!  $data['top_paragraph_second_line'] !!}
                                        </td>
                                    </tr>
                                @endif

                                @if(isset($data['top_paragraph_third_line']) && !empty($data['top_paragraph_third_line']))
                                    <tr>
                                        <td style="padding: 24px 0 32px 0; color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 22px;">
                                            {!!  $data['top_paragraph_third_line'] !!}
                                        </td>
                                    </tr>
                                @endif

                                @if(isset($data['details_box_title']))
                                <tr>
                                    <td style="border-top: 1px solid #D9E2EC; padding: 12px 0 12px 0;"></td>
                                </tr>
                                @endif

                                @if(isset($data['details_box_title']) && !empty($data['details_box_title']))
                                    <tr>
                                        <td style="color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 18px;"><b>{{ $data['details_box_title'] }}</b></td>
                                    </tr>
                                @endif

                                @if(isset($data['booking_details_array']))
                                    <tr>
                                        <td style="padding: 0 0 5px 20px">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tbody>

                                                @foreach($data['booking_details_array'] as $booking_detail)
                                                    <tr>

                                                        @if(isset($booking_detail['label']) && !empty($booking_detail['label']))
                                                            <td style="font-size: 14px; color: #153643; padding: 4px 0 4px 0;min-width: 100px;">
                                                                {{ $booking_detail['label'] }}
                                                            </td>
                                                        @endif

                                                        @if(isset($booking_detail['value']) && !empty($booking_detail['value']))
                                                            <td style="font-size: 14px; color: {{ (isset($booking_detail['value_text_color'])) ? $booking_detail['value_text_color']:'#153643' }}; padding: 4px 0 4px 0;">
                                                                <strong>{{ $booking_detail['value'] }}</strong>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach

                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                @endif

                                @if(isset($data['upsell_details_array']) && !empty($data['upsell_details_array']))
                                    <tr>
                                        <td style="padding: 16px 0 5px 0">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tbody>
                                                @foreach($data['upsell_details_array'] as $upsell)
                                                    <tr>
                                                        <td style="font-size: 14px; color: #153643; padding: 4px 0 4px 0;min-width: 100px;">
                                                            <div class="addon-item">
                                                                <div class="addon-item-header">
                                                                    <div class="addon-item-header-content">
                                                                        <div class="addon-item-header-text">
                                                                            <h4>{{ $upsell['type'] }}</h4>
                                                                            <p>{{ $upsell['description'] }}</p>
                                                                        </div>
                                                                        <div class="addon-price">
                                                                            <span class="price-text">{{ $upsell['currency_symbol'] }}{{ $upsell['amount'] }} </span>
                                                                            <span>{{ $upsell['per'] }} {{ $upsell['period'] }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                @endif

                                @if(isset($data['button_url']) && isset($data['button_text']))

                                    @if(isset($data['show_button']) && $data['show_button'])
                                        <tr>
                                            <td style="padding:24px 0 16px 0;">
                                                <a href="{{ $data['button_url'] }}" style="background: #0779F0; color: #fff; font-weight: 600; font-size: 16px; display: block; padding: 16px 0 16px 0; text-align: center; text-decoration: none; border-radius: 40px;">{{ $data['button_text'] }}</a>
                                            </td>
                                        </tr>
                                    @elseif(!isset($data['show_button']))
                                        <tr>
                                            <td style="padding:24px 0 16px 0;">
                                                <a href="{{ $data['button_url'] }}" style="background: #0779F0; color: #fff; font-weight: 600; font-size: 16px; display: block; padding: 16px 0 16px 0; text-align: center; text-decoration: none; border-radius: 40px;">{{ $data['button_text'] }}</a>
                                            </td>
                                        </tr>
                                    @endif
                                @endif

                                @if(isset($data['bottom_info_line']) && !empty($data['bottom_info_line']))
                                    <tr>
                                        <td align="center" style="border-top: 1px solid #D9E2EC;padding: 16px 0 16px 0;color: #153643;font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;font-size: 12px;">
                                            <p>{{ $data['bottom_info_line'] }}</p>
                                        </td>
                                    </tr>
                                @endif

                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#102A43" style="padding: 30px 30px 30px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td style="color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 14px; text-align: center;">
                                            support@chargeautomation.com
                                            <br><br>
                                            © 2020 - <a href="https://chargeautomation.com/?utm_source=email&utm_medium=client_email&utm_campaign=poweredbyCA" target="_blank" style="text-decoration:none; color: #ffffff;"><font color="#ffffff">Charge Automation</font></a>
                                            <br><br>
                                            <b>Payment Automation&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Security Deposit&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Upsell&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;GuideBook</b>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
