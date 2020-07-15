<table id="guest_portal_v2_payment_details">
    <thead>
    <tr>
        {{-- <th>Sr#</th> --}}
        <th>Date</th>
        <th>Amount</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @php $sr = 1; @endphp
    @if ($ccAuth  != null)
        <tr title="{{ $ccAuth->remarks }}">
            {{-- <td>{{$sr++}}</td>--}} @php $sr++; @endphp
            <td>{{   Carbon\Carbon::parse($ccAuth->due_date)->timezone($timezone)->format('d F Y h:i a')  }}</td>
            <td>{{$currencySymbol}}{{ $ccAuth->hold_amount }}</td>
            {{--<td>
                <span class="{{ config('db_const.credit_card_authorizations.transaction_type_button_color.'.$ccAuth->type) }}">
                    {{  ucwords(str_replace('_', ' ',  (($ccAuth->type ==  config('db_const.credit_card_authorizations.type.credit_card_auto_authorize')) ?
                        __('client/credit_card_authorization.credit_card_auto_authorize') :
                        __('client/credit_card_authorization.credit_card_manual_authorize')) )) }}
                </span>
            </td>--}}
            <td>
                <span class="{{ config('db_const.credit_card_authorizations.status_button_color.'.$ccAuth->status) }}" >{{ config('db_const.credit_card_authorizations.status.'.$ccAuth->status) }}</span>
                 @if(in_array($ccAuth->status, [7, 5]))
                     <p style="margin: 0 !important;">
                         <small>
                             {{Carbon\Carbon::parse($ccAuth->next_due_date)->timezone($timezone)->format('d F Y h:i a')}}
                         </small>
                     </p>
                 @endif

            </td>
        </tr>
    @endif
    @foreach($bookingDetails->paymentDetails->transactionInits as $transactionInit)
        @php
        if($transactionInit->payment_status == '')
        $transactionInit->payment_status = 'empty';
        @endphp
        @if( in_array($transactionInit->type, config('db_const.transactions_init.securityDepositTypes')))
            @continue
        @endif
        <tr title="{{$transactionInit->client_remarks}}">
            {{--<td>{{$sr++}}</td>--}} @php $sr++; @endphp
            {{--<td>{{ sprintf("%04s",$transactionInit->id) }}</td>--}}
            <td>{{   Carbon\Carbon::parse($transactionInit->due_date)->timezone($timezone)->format('d F Y h:i a')  }}</td>
            <td>{{$currencySymbol}}{{ $transactionInit->price }}</td>
            {{--<td>
                <span class="{{ config('db_const.transactions_init.transaction_type_button_color.'.$transactionInit->transaction_type) }}">
                    {{ ucwords(str_replace('_', ' ', config('db_const.transactions_init.transaction_type.'.$transactionInit->transaction_type) )) }}
                </span>
            </td>--}}
            <td>
                <span class="{{config('db_const.transactions_init.status_button_color.'.$transactionInit->payment_status)}}">
                        {{config('db_const.transactions_init.payment_status.'.$transactionInit->payment_status)}}
                </span>
                 {{--@if(in_array($transactionInit->payment_status, [4, 0]))
                     <p style="margin: 0 !important;">
                         <small>
                             {{Carbon\Carbon::parse($transactionInit->next_attempt_time)->timezone($timezone)->format('d F Y h:i a')}}
                         </small>
                     </p>
                 @endif--}}
            </td>

        </tr>
    @endforeach
    @if ($sr  == 1)
        <tr>
            <td colspan="3" class="text-center">No records available.</td>
        </tr>
    @endif
    </tbody>
</table>
