<table id="guest_portal_v2_security_deposit_transaction">
    <thead>
    <tr>
        {{--<th>Sr#</th>--}}
        <th>Due Date</th>
        <th>Amount</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @php $sr = 0; @endphp
    {{--  Security Deposit CC Auth Entry  --}}
    @if ($securityAuth  != null)
        <tr role="row" class="odd" data-container="body" data-toggle="m-tooltip" data-placement="top" title="{{ $securityAuth->remarks }}">
            {{--<td>{{++$sr}}</td>--}}@php ++$sr; @endphp
            <td>{{ Carbon\Carbon::parse($securityAuth->due_date)->timezone($timezone)->format('d F Y h:i a')  }}</td>
            <td>{{$currencySymbol}}{{ $securityAuth->hold_amount }}</td>
            <td>
                <span class="{{ config('db_const.credit_card_authorizations.status_button_color.'.$securityAuth->status) }}" >{{ config('db_const.credit_card_authorizations.status.'.$securityAuth->status) }}</span>
                {{--@if(in_array($securityAuth->status, [7, 5]))
                    <p style="margin: 0 !important;">
                        <small>
                            {{Carbon\Carbon::parse($securityAuth->next_due_date)->timezone($timezone)->format('d F Y h:i a')}}
                        </small>
                    </p>
                @endif--}}
            </td>

        </tr>
    @endif
    {{--  Security Deposit CC Auth Entry Ends  --}}
    {{--  Security Deposit Manual / Captured Entries  --}}
    @foreach($bookingDetails->paymentDetails->transactionInits as $transactionInit)
        @if( !in_array($transactionInit->type, config('db_const.transactions_init.securityDepositTypes')) )
            @continue
        @endif

        <tr role="row" class="odd">
            {{--<td>{{++$sr}}</td>--}}@php ++$sr; @endphp
            <td>{{Carbon\Carbon::parse($transactionInit->due_date)->timezone($timezone)->format('d F Y h:i a') }}</td>
            <td>{{$currencySymbol}}{{$transactionInit->price }}</td>
            <td>
                    <span class="{{ config('db_const.transactions_init.status_button_color.'.$transactionInit->payment_status) }}">
                        {{ config('db_const.transactions_init.payment_status.'.$transactionInit->payment_status) }}
                    </span>
                {{--  @if(in_array($transactionInit->payment_status, [4, 0]))
                      <p style="margin: 0 !important;">
                          <small>
                              {{Carbon\Carbon::parse($transactionInit->next_attempt_time)->timezone($timezone)->format('d F Y h:i a')}}
                          </small>
                      </p>
                  @endif--}}
            </td>
        </tr>
    @endforeach
    {{--  Security Deposit Manual / Captured Entries  End--}}


    @if ($sr  == 0)
        <tr>
            <td colspan="3" class="text-center">No Deposit Remaining..</td>
        </tr>
    @endif
    </tbody>
</table>
