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
            color: #486581 !important;
            font-size: 0.8rem;
            margin: 0.25rem 0 0;
            display: block;
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
                            @if(!empty($data['property_logo']))
                                <img src="{{ $data['property_logo'] }}" height="64" alt="">
                            @else
                                <span style="background-color: #334e68 !important; border-radius: 50% !important; color: #fff !important; display: block !important;font-size: 16px !important;font-weight: 600 !important; height: 45px !important;line-height: 45px !important; text-align: center !important;width: 45px !important;">
                                    {{ $data['property_initial'] }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" style="padding: 32px 24px 24px 24px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td style="color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 24px;"><b>Hello {{ $data['guest_full_name'] }}</b></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 24px 0 {{ isset($data['top_paragraph_second_line']) ? '12px':'32px' }} 0; color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 22px;">
                                           {!! $data['top_paragraph'] !!}
{{--                                            {{ $data['top_paragraph'] }}--}}
                                        </td>
                                    </tr>

                                    @if(isset($data['top_paragraph_second_line']) && !empty($data['top_paragraph_second_line']))
                                    <tr>
                                        <td style="padding: 24px 0 32px 0; color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 22px;">
                                            {!! $data['top_paragraph_second_line'] !!}
{{--                                            {{ $data['top_paragraph_second_line'] }}--}}
                                        </td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td style="border-top: 1px solid #D9E2EC; padding: 12px 0 12px 0;"></td>
                                    </tr>

                                    @if(isset($data['details_box_title']) && !empty($data['details_box_title']))
                                    <tr>
                                        <td style="color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 18px;"><b>{{ $data['details_box_title'] }}</b></td>
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

                                    @if(isset($data['booking_details_array']))
                                    <tr>
                                        <td style="padding: 16px 0 5px 0">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tbody>

                                                    @foreach($data['booking_details_array'] as $booking_detail)
                                                    <tr>
                                                        <td style="font-size: 14px; color: #153643; padding: 4px 0 4px 0;min-width: 100px;">
                                                            {{ $booking_detail['label'] }}
                                                        </td>
                                                        <td style="font-size: 14px; color: {{ (isset($booking_detail['value_text_color'])) ? $booking_detail['value_text_color']:'#153643' }}; padding: 4px 0 4px 0;">
                                                            <strong>{{ $booking_detail['value'] }}</strong>
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
                                            @if(isset($data['show_secure_process_text']) && $data['show_secure_process_text'])
                                                <tr>
                                                    <td align="center" style="padding: 0 0 16px 0;color: #153643;font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;font-size: 12px;">
                                                        <img src="https://chargeautomation.com/img/lock.png" height="14" style="display: inline;">
                                                        <p style="display: inline;">This process is safe, secure and easy</p>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endif



                                    @if(isset($data['bottom_info_line']) && !empty($data['bottom_info_line']))
                                    <tr>
                                        <td align="center" style="border-top: 1px solid #D9E2EC; padding: 16px 0 16px 0; color: #153643; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 16px;">
                                            <p>{{ $data['bottom_info_line'] }}</p>
                                        </td>
                                    </tr>
                                    @endif

                                    <!-- show link as a text -->
                                    @if(isset($data['button_url']) && isset($data['button_text']))
                                    <tr>
                                        <td align="center" style="padding: 0 0 16px 0;color: #8f9092;font-size: 12px;font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;">
                                            <p>If you cannot access this link, copy and paste the entire URL into your browser:<br>
                                                <a href="{{ $data['button_url'] }}">{{ $data['button_url'] }}</a>
                                            </p>
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
                                    <td style="color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif; font-size: 14px; text-align: center;">© 2020 — {{ $data['property_name'] }}<br><br>Powered by <a href="https://chargeautomation.com/?utm_source=email&utm_medium=client_email&utm_campaign=poweredbyCA" target="_blank" style="color: #ffffff;"><font color="#ffffff">ChargeAutomation</font></a></td>
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
</html><?php
