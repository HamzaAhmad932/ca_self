<!--begin::Portlet-->
<div class="m-portlet m-portlet--creative m-portlet--first m-portlet--bordered-semi" style="padding-top: 0px !important;">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                                            <span class="m-portlet__head-icon m--hide">
                                                <i class="flaticon-statistics"></i>
                                            </span>
                <h3 class="m-portlet__head-text">
                    Authorization Logs
                </h3>
                <!-- <h2 class="m-portlet__head-label m-portlet__head-label--info">
                    <span>Stuff</span>
                </h2> -->
            </div>
        </div>

    </div>
    <div class="m-portlet__body">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer dtr-inline"
                       id="m_table_1" role="grid" aria-describedby="m_table_1_info">
                    <thead>

                    <tr role="row">
                        <th>Sr#</th>
                        <th>Transaction#</th>
                        <th>Booking Source</th>
                        <th>Payment Gateway</th>
                        <th>Transaction Status</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php $sr = 1; @endphp
                    {{--   Security Deposi Auth Details    --}}
                    @can('viewTransaction')
                    @if ($securityAuth  != null)
                        @foreach($data['bookingDetails']->paymentDetails->securityAuthDetails as $securityAuthDetail)
                            <tr data-container="body" data-toggle="m-tooltip" data-placement="top" data-skin="dark" title="" data-original-title="{{ $securityAuthDetail->error_msg }}">
                                <td>{{$sr++}}</td>
                                <td>{{ sprintf("%04s", $securityAuth->id)}}</td>
                                <th scope="row">{{(($data['bookingInfo']->booking_source != null) ?  $data['bookingInfo']->booking_source->name : '' )}}</th>
                                <th scope="row">
                                    {{ ( isset($PaymentGatewaysArr['allPG'][$securityAuthDetail->payment_gateway_form_id]) ? $PaymentGatewaysArr['allPG'][$securityAuthDetail->payment_gateway_form_id] : '') }}
                                    {{ ($securityAuthDetail->charge_ref_no != null ? '(' .$securityAuthDetail->charge_ref_no.')' : '') }}
{{--                                    <small>{{ ( ($securityAuthDetail->payment_status == 0) ? '('.$securityAuthDetail->error_msg.')'  : '' ) }}</small>--}}
                                </th>
                                <td>
                                    <small>{{ $securityAuthDetail->error_msg }}</small>
                                </td>

                                <td>
                                    <span class="{{config('db_const.credit_card_authorizations.status_button_color.'.$securityAuthDetail->payment_status)}}">
                                        {{config('db_const.credit_card_authorizations.status.'.$securityAuthDetail->payment_status)}}
                                    </span>
                                </td>
                                <td >{{ $symbol.$securityAuthDetail->amount }}</td>
                                <td>{{ Carbon\Carbon::parse($securityAuthDetail->created_at)->timezone($timezone)->format('d F Y h:i a')}}</td>
                            </tr>
                        @endforeach
                    @endif
                    @endcan
                    {{--   Security Deposi Auth Details End   --}}
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<!--end::Portlet-->
<!--begin::Portlet-->
<div class="m-portlet m-portlet--creative m-portlet--first m-portlet--bordered-semi" style="padding-top: 0px !important;">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                                            <span class="m-portlet__head-icon m--hide">
                                                <i class="flaticon-statistics"></i>
                                            </span>
                <h3 class="m-portlet__head-text">
                    Transaction Logs
                </h3>
                <!-- <h2 class="m-portlet__head-label m-portlet__head-label--info">
                    <span>Stuff</span>
                </h2> -->
            </div>
        </div>

    </div>
    <div class="m-portlet__body">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer dtr-inline"
                       id="m_table_1" role="grid" aria-describedby="m_table_1_info">
                    <thead>

                    <tr role="row">
                        <th>Sr#</th>
                        <th>Transaction#</th>
                        <th>Booking Source</th>
                        <th>Payment Gateway</th>
                        <th>Transaction Status</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php $s_l_sr = 1; @endphp

                    {{--   Security Deposit Manual & SD Refund Details  --}}
                    @can('viewTransaction')
                    @foreach($data['bookingDetails']->paymentDetails->transactionInits as $transactionInit)

                        @if( !in_array($transactionInit->type, config('db_const.transactions_init.securityDepositTypes')))
                            @continue
                        @endif

                        @foreach($transactionInit->transactions_detail as $transactionInitDetail)
                            <tr data-container="body" data-toggle="m-tooltip" data-placement="top" data-skin="dark" title="" data-original-title="{{ $transactionInitDetail->error_msg }}">
                                <td>{{ $s_l_sr++ }}</td>
                                <td>{{ sprintf("%04s", $transactionInit->id)}}</td>
                                <th scope="row">{{(($data['bookingInfo']->booking_source != null) ?  $data['bookingInfo']->booking_source->name : '' )}}</th>
                                <th scope="row">
                                    {{ ( isset($PaymentGatewaysArr['allPG'][$transactionInitDetail->payment_gateway_form_id]) ? $PaymentGatewaysArr['allPG'][$transactionInitDetail->payment_gateway_form_id] : '') }}
                                    ({{ $transactionInitDetail->charge_ref_no }})
{{--                                    <small>{{ ( ($transactionInitDetail->payment_status == 0) ? '('.$transactionInitDetail->error_msg.')'  : '' ) }}</small> --}}
                                </th>
                                <td>
                                    <small>{{ $transactionInitDetail->error_msg }}</small>
                                </td>

                                <td>
                                    <span class="{{ (($transactionInitDetail->payment_status == 1) ? 'm-badge  m-badge--accent m-badge--wide' :  'm-badge  m-badge--danger m-badge--wide' ) }}" >
                                         {{ (($transactionInitDetail->payment_status == 1) ? 'Success' :  'Fail') }}
                                    </span>
                                </td>
                                <td >{{ $symbol.$transactionInit->price }}</td>
                                <td>{{ Carbon\Carbon::parse($transactionInitDetail->created_at)->timezone($timezone)->format('d F Y h:i a')}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    @endcan
                    {{-- Security Deposit Manual & SD Refund Details End --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!--end::Portlet-->