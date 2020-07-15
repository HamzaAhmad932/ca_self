

<table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer dtr-inline"
       id="m_table_1" role="grid" aria-describedby="m_table_1_info"
       style="width: 974px;">
    <thead>
    <tr role="row">
        <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1"
            colspan="1"
            aria-label="Country: activate to sort column ascending">Sr#
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1"
            colspan="1"
            aria-label="Country: activate to sort column ascending">Transaction#
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1"
            colspan="1"
            aria-label="Country: activate to sort column ascending">Amount
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1"
            colspan="1"
            aria-label="Ship Address: activate to sort column ascending">Due
            Date
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1"
            colspan="1"
            aria-label="Ship Address: activate to sort column ascending">Type
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1"
            colspan="1"
            aria-label="Ship Date: activate to sort column ascending">Status
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1" colspan="2"
            aria-label="Status: activate to sort column ascending"
            style="text-align: center">Action
        </th>
    </tr>
    </thead>
    <tbody>

    {{--  Credit Card Auth Entry  --}}
    @php $sr = 1; @endphp
    @if ($ccAuth  != null)
        <tr role="row" class="odd" data-container="body" data-toggle="m-tooltip" data-placement="top" title="{{ $ccAuth->remarks }}">
            <td>{{$sr++}}</td>
            <td>{{ sprintf("%04s",$ccAuth->id) }}</td>
            <td>{{$currencySymbol}} {{ $ccAuth->hold_amount }}</td>
            <td>{{   Carbon\Carbon::parse($ccAuth->due_date)->timezone($timezone)->format('d F Y h:i a')  }}</td>


            <td>
                <span class="{{ config('db_const.credit_card_authorizations.transaction_type_button_color.'.$ccAuth->type) }}">
                    {{  ucwords(str_replace('_', ' ',  (($ccAuth->type ==  config('db_const.credit_card_authorizations.type.credit_card_auto_authorize')) ?
                        __('client/credit_card_authorization.credit_card_auto_authorize') :
                        __('client/credit_card_authorization.credit_card_manual_authorize')) )) }}
                </span>

            </td>



            <td>
                <span class="{{ config('db_const.credit_card_authorizations.status_button_color.'.$ccAuth->status) }}" >{{ config('db_const.credit_card_authorizations.status.'.$ccAuth->status) }}</span>
               {{-- @if(in_array($ccAuth->status, [7, 5]))
                    <p style="margin: 0 !important;">
                        <small>
                            {{Carbon\Carbon::parse($ccAuth->next_due_date)->timezone($timezone)->format('d F Y h:i a')}}
                        </small>
                    </p>
                @endif--}}

            </td>

            <td>
               <span class="dropdown">
                    <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">
                        <i class="la la-ellipsis-h"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" style="display: none;">
                    </div>
                </span>
            </td>
        </tr>
    @endif
    {{--  Credit Card Auth Entry Ends  --}}


    {{-- TransactionInits and Refund Entries --}}
    @foreach($bookingDetails->paymentDetails->transactionInits as $transactionInit)
        @php
            if($transactionInit->payment_status == '')
                $transactionInit->payment_status = 'empty';
        @endphp
        @if( in_array($transactionInit->type, config('db_const.transactions_init.securityDepositTypes')))
            @continue
        @endif
        <tr role="row" class="odd" data-container="body" data-toggle="m-tooltip" data-placement="top" title="{{$transactionInit->client_remarks}}">
            <td>{{$sr++}}</td>
            <td>{{ sprintf("%04s",$transactionInit->id) }}</td>
            <td>{{$currencySymbol}} {{ $transactionInit->price }}</td>
            <td>{{   Carbon\Carbon::parse($transactionInit->due_date)->timezone($timezone)->format('d F Y h:i a')  }}</td>
            <td>
                <span class="{{ config('db_const.transactions_init.transaction_type_button_color.'.$transactionInit->transaction_type) }}">
                    {{ ucwords(str_replace('_', ' ', config('db_const.transactions_init.transaction_type.'.$transactionInit->transaction_type) )) }}
                </span>
            </td>
            <td>
                <span class="{{config('db_const.transactions_init.status_button_color.'.$transactionInit->payment_status)}}">
                        {{config('db_const.transactions_init.payment_status.'.$transactionInit->payment_status)}}
                </span>
               {{-- @if(in_array($transactionInit->payment_status, [4, 0]))
                    <p style="margin: 0 !important;">
                        <small>
                            {{Carbon\Carbon::parse($transactionInit->next_attempt_time)->timezone($timezone)->format('d F Y h:i a')}}
                        </small>
                    </p>
                @endif--}}
            </td>
            <td>

            <span class="dropdown">
                <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">
                    <i class="la la-ellipsis-h"></i>
                </a>
                    @if(($transactionInit->payment_status== '4') || ($transactionInit->payment_status == '0'))
                    <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" @click.prevent="manuallyPayBookingAmount('{{$transactionInit->price}}', '{{ encrypt(['booking_info_id' => $bookingDetails->bookingInfo->id, 'transaction_init_id' => $transactionInit->id, 'amount-type' => 0]) }}', '{{$currencyCode}}')" style="cursor: pointer;">
                    Pay Now
                    </a>
                </div>
                @endif()
                <div class="dropdown-menu dropdown-menu-right" style="display: none;"></div>
            </span>

            </td>
        </tr>
    @endforeach
    {{-- TransactionInits and Refund Entries End --}}
    </tbody>
</table>
