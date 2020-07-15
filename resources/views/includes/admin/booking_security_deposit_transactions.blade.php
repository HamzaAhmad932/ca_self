<table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer"
       id="m_table_1" aria-describedby="m_table_1_info" role="grid">
    <thead>
    <tr role="row">
        <th class="" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Order ID: activate to sort column ascending">
            Sr#
        </th>
        <th class="" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Order ID: activate to sort column ascending">
            Transaction#
        </th>
        <th class="" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Order ID: activate to sort column ascending">
            Amount
        </th>
        <th class="" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Ship City: activate to sort column ascending">
            Due Date
        </th>
        <th class="" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Country: activate to sort column ascending">
            Type
        </th>
        <th class="" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Country: activate to sort column ascending">
            Status
        </th>

    </tr>
    </thead>

    <tbody>
    @php $sr = 1; @endphp
    {{--  Security Deposit CC Auth Entry  --}}
    @if ($securityAuth  != null)
        <tr role="row" class="odd" data-container="body" data-toggle="m-tooltip" data-placement="top" title="{{ $securityAuth->remarks }}">
            <td>{{$sr++}}</td>
            <td>{{ sprintf("%04s",$securityAuth->id) }}</td>
            <td>{{ $symbol.$securityAuth->hold_amount }}</td>
            <td>{{ Carbon\Carbon::parse($securityAuth->due_date)->timezone($timezone)->format('d F Y h:i a')  }}</td>
            <td>
                <span class="{{ config('db_const.credit_card_authorizations.transaction_type_button_color.'.$securityAuth->type) }}">
                    {{ ucwords(str_replace('_', ' ', config('db_const.credit_card_authorizations.transaction_type.'.$securityAuth->type))) }}
                </span>
            </td>
            <td>
                <span class="{{ config('db_const.credit_card_authorizations.status_button_color.'.$securityAuth->status) }}" >{{ config('db_const.credit_card_authorizations.status.'.$securityAuth->status) }}</span>
                @if(in_array($securityAuth->status, [7, 5]))
                <p style="margin: 0 !important;">
                <small>
                    {{Carbon\Carbon::parse($securityAuth->next_due_date)->timezone($timezone)->format('d F Y h:i a')}}
                </small>
                </p>
                @endif
            </td>
        </tr>
    @endif
    {{--  Security Deposit CC Auth Entry Ends  --}}

    {{--  Security Deposit Manual / Captured Entries  --}}
    @foreach($data['bookingDetails']->paymentDetails->transactionInits as $transactionInit)
        @if( !in_array($transactionInit->type, config('db_const.transactions_init.securityDepositTypes')))
            @continue
        @endif

        <tr role="row" class="odd">
            <td>{{$sr++}}</td>
            <td>{{ sprintf("%04s",$transactionInit->id)}}</td>
            <td>{{$symbol.$transactionInit->price }}</td>
            <td>{{Carbon\Carbon::parse($transactionInit->due_date)->timezone($timezone)->format('d F Y h:i a') }}</td>
            <td>
                @if(config('db_const.transactions_init.transaction_type.'.$transactionInit->transaction_type) != null)
                    <span class="{{config('db_const.transactions_init.transaction_type_button_color.'.$transactionInit->transaction_type) }}">
                        {{ucwords(str_replace('_', ' ', config('db_const.transactions_init.transaction_type.'.$transactionInit->transaction_type)))}}
                    </span>
                @else
                    <span class="m-badge  m-badge--info m-badge--wide">
                        Charge for Damage
                    </span>
                @endif
            </td>
            <td>
                <span class="{{ config('db_const.transactions_init.status_button_color.'.$transactionInit->payment_status) }}">
                    {{ config('db_const.transactions_init.payment_status.'.$transactionInit->payment_status) }}
                </span>
                @if(in_array($transactionInit->payment_status, [4, 0]))
                <p style="margin: 0 !important;">
                <small>
                    {{Carbon\Carbon::parse($transactionInit->next_attempt_time)->timezone($timezone)->format('d F Y h:i a')}}
                </small>
                </p>
                @endif
            </td>
        </tr>
    @endforeach
    {{--  Security Deposit Manual / Captured Entries  End--}}
    </tbody>

</table>