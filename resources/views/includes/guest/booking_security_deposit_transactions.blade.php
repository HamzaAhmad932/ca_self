
<table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer"
       id="m_table_1" aria-describedby="m_table_1_info" role="grid">
    <thead>
    <tr role="row">
        <th class="sorting" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Order ID: activate to sort column ascending">
            Sr#
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Order ID: activate to sort column ascending">
            Transaction#
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Order ID: activate to sort column ascending">
            Amount
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Ship City: activate to sort column ascending">
            Due Date
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Country: activate to sort column ascending">
            Type
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Country: activate to sort column ascending">
            Status
        </th>
        <th class="sorting" tabindex="0" aria-controls="m_table_1"
            rowspan="1" colspan="1"
            aria-label="Ship City: activate to sort column ascending" style="text-align: center">
            Action
        </th>

    </tr>
    </thead>
    <tbody>
        @php $sr = 0; @endphp
        {{--  Security Deposit CC Auth Entry  --}}
        @if ($securityAuth  != null)
            <tr role="row" class="odd" data-container="body" data-toggle="m-tooltip" data-placement="top" title="{{ $securityAuth->remarks }}">
                <td>{{++$sr}}</td>
                <td>{{ sprintf("%04s",$securityAuth->id) }}</td>
                <td>{{$currencySymbol}} {{ $securityAuth->hold_amount }}</td>
                <td>{{ Carbon\Carbon::parse($securityAuth->due_date)->timezone($timezone)->format('d F Y h:i a')  }}</td>
                <td>
                        <span class="{{ config('db_const.credit_card_authorizations.transaction_type_button_color.'.$securityAuth->type) }}">
                            {{ ucwords(str_replace('_', ' ', config('db_const.credit_card_authorizations.transaction_type.'.$securityAuth->type) ))}}
                        </span>
                </td>
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
                <td>
                  <span class="dropdown">

                      <a h0ref="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">
                      <i class="la la-ellipsis-h"></i>
                    </a>

                    @if(isset($bookingDetails->creditCardInfo) && ($bookingDetails->creditCardInfo != null) && ($bookingDetails->creditCardInfo != '1'))

                          @if(in_array($securityAuth->status, ['4', '5', '7']))
                              <div class="dropdown-menu dropdown-menu-right">
                                    <a style="cursor: pointer;" class="dropdown-item" @click.prevent="reattempt('{{$securityAuth->id}}')">
                                        Pay now
                                    </a>
                              </div>
                          @endif()

                    @endif()

                    <div class="dropdown-menu dropdown-menu-right" style="display: none;">
                    </div>
                  </span>
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
            <td>{{++$sr}}</td>
            <td>{{ sprintf("%04s",$transactionInit->id)}}</td>
            <td>{{$currencySymbol}} {{$transactionInit->price }}</td>
            <td>{{Carbon\Carbon::parse($transactionInit->due_date)->timezone($timezone)->format('d F Y h:i a') }}</td>
            <td>
                @if(config('db_const.transactions_init.transaction_type.'.$transactionInit->transaction_type) != null)
                    <span class="{{config('db_const.transactions_init.transaction_type_button_color.'.$transactionInit->transaction_type) }}">
                            {{ucwords(str_replace('_', ' ', config('db_const.transactions_init.transaction_type.'.$transactionInit->transaction_type))) }}
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
              {{--  @if(in_array($transactionInit->payment_status, [4, 0]))
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
                        <div class="dropdown-menu dropdown-menu-right" style="display: none;">
                        </div>
                  </span>
            </td>
        </tr>
    @endforeach
    {{--  Security Deposit Manual / Captured Entries  End--}}

        @if($sr == 0)
            <tr>
                <td>No Deposit Remaining.</td>
            </tr>
        @endif

    </tbody>
 </table>