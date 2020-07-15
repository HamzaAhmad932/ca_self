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
                    {{--   Credit Card Auth Details    --}}
                    @can('viewTransaction')
                    @if ($ccAuth  != null)
                        @foreach($data['bookingDetails']->paymentDetails->ccAuthDetails as $authDetails)
                            <tr data-container="body" data-toggle="m-tooltip" data-placement="top" data-skin="dark" title="" data-original-title="{{ $authDetails->error_msg }}">
                                <td>{{$sr++}}</td>
                                <td>{{ sprintf("%04s", $ccAuth->id)}}</td>
                                <th scope="row">{{(($data['bookingInfo']->booking_source != null) ?  $data['bookingInfo']->booking_source->name : '' )}}</th>
                                <th scope="row">
                                    {{ ( isset($PaymentGatewaysArr['allPG'][$authDetails->payment_gateway_form_id]) ? $PaymentGatewaysArr['allPG'][$authDetails->payment_gateway_form_id] : '') }}
                                    {{ ($authDetails->charge_ref_no != null ? '('.$authDetails->charge_ref_no.')' : '' )}}
{{--                                    <small>{{ ( ($authDetails->payment_status == 0) ? '('.$authDetails->error_msg.')'  : '' ) }}</small>--}}
                                </th>
                             <td>
                                 <small> {{ $authDetails->error_msg }} </small>
                             </td>
                                <td>
                                    <span class="{{config('db_const.credit_card_authorizations.status_button_color.'.$authDetails->payment_status)}}">
                                        {{config('db_const.credit_card_authorizations.status.'.$authDetails->payment_status)}}
                                    </span>
                                </td>
                                <td >{{ $symbol.$authDetails->amount }}</td>
                                <td>{{ Carbon\Carbon::parse($authDetails->created_at)->timezone($timezone)->format('d F Y h:i a')}}</td>
                            </tr>
                    @endforeach
                    @endif
                    @endcan
                    {{--   Credit Card Auth Details End   --}}

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
                    @php $sr = 1; @endphp
                        {{--   TransactionInit & Refund Details  --}}
                    @can('viewTransaction')
                    @foreach($data['bookingDetails']->paymentDetails->transactionInits as $transactionInit)

                        @if( in_array($transactionInit->type, config('db_const.transactions_init.securityDepositTypes')))
                            @continue
                        @endif

                        @foreach($transactionInit->transactions_detail as $transactionInitDetail)
                            <tr data-container="body" data-toggle="m-tooltip" data-placement="top" data-skin="dark" title="" data-original-title="{{ $transactionInitDetail->error_msg }}">
                                <td>{{ $sr++ }}</td>
                                <td>{{ sprintf("%04s", $transactionInit->id)}}</td>
                                <th scope="row">{{(($data['bookingInfo']->booking_source != null) ?  $data['bookingInfo']->booking_source->name : '' )}}</th>
                                <th scope="row">
                                    {{ (isset($PaymentGatewaysArr['allPG'][$transactionInitDetail->payment_gateway_form_id]) ? $PaymentGatewaysArr['allPG'][$transactionInitDetail->payment_gateway_form_id] : '') }}
                                    {{ ($transactionInitDetail->charge_ref_no != null ? '('.$transactionInitDetail->charge_ref_no.')' : '' )}}
{{--                                    <small>{{ ( ($transactionInitDetail->payment_status == 0) ? '('.$transactionInitDetail->error_msg.')'  : '' ) }}</small>--}}
                                </th>
                                <td>
                                <small>{{ $transactionInitDetail->error_msg }}</small>
                                <td>
                                    <span class="{{config('db_const.transactions_init.status_button_color.'.$transactionInitDetail->payment_status)}}">
                                        {{config('db_const.transactions_init.payment_status.'.$transactionInitDetail->payment_status)}}
                                    </span>
                                </td>
                                <td >{{ $symbol . ($transactionInitDetail->amount == null ? $transactionInit->price : $transactionInitDetail->amount) }}</td>
                                <td>{{ Carbon\Carbon::parse($transactionInitDetail->created_at)->timezone($timezone)->format('d F Y h:i a')}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    @endcan
                    {{--   TransactionInit & Refund Details End --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!--end::Portlet-->