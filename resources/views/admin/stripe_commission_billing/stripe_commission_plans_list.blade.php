@extends('layouts.admin')
@section('content')
    <style>
        [v-cloak] {
            display: none;
        }
        .heading-row {
            background-color: rgb(240, 244, 248);
            padding: 2rem 0rem 2rem 0rem;
            color: #575962;
        }
    </style>
    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">
        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h3 class="m-subheader__title m-subheader__title--separator">Commission Plans</h3>
                <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                    <li class="m-nav__item">
                        <span class="m-nav__link-text"> Commission Plan List </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- END: Subheader -->
    <div class="m-content">
        <div class="m-portlet">
            <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="table-responsive">
                                <table class="table table-hover " id="properties_table">
                                    <thead>
                                    <tr class="heading-row">
                                        <th>Sr#</th>
                                        <th>Name</th>
                                        <th>ID</th>
                                        <th>Product ID</th>
                                        <th>Usage Type</th>
                                        <th>Interval</th>
                                        <th>Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($plans as $key => $plan)
                                        <tr>
                                            <td>{{$key+1}}</td>
                                            <td>{{$plan->nickname}}</td>
                                            <td><small>{{$plan->id}}</small></td>
                                            <td><small>{{$plan->product}}</small></td>
                                            <td>{{$plan->usage_type}}</td>
                                            <td><b>( {{$plan->interval_count}} ) {{$plan->interval }}</b>  </td>
                                            <td>{{strtoupper($plan->currency)}} {{$plan->amount != null ? $plan->amount : $plan->amount_decimal}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
            </div>
        </div>
    </div>

@endsection
@section('ajax_script')
@endsection