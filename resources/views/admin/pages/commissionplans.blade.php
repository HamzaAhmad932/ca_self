@extends('layouts.admin')
@section('content')
    <style>
        [v-cloak] {
            display: none;
        }
    </style>

    <!-- BEGIN: Subheader -->
    <div class="m-subheader ">

        <div class="d-flex align-items-center">
            <div class="mr-auto">
                <h3 class="m-subheader__title m-subheader__title--separator">Commission Plans</h3>
                <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                    <li class="m-nav__item m-nav__item--home">
                        <a href="#" class="m-nav__link m-nav__link--icon">
                            <i class="m-nav__link-icon la la-home"></i>
                        </a>
                    </li>
                    <li class="m-nav__separator">-</li>

                    <li class="m-nav__item">

                        <span class="m-nav__link-text"> Commission Plans </span>

                    </li>
                </ul>
            </div>

        </div>
    </div>
    <!-- END: Subheader -->
    @php
    $a = 1;
    $b = 1;
    $c = 1;
    $d = 1;
    @endphp
    <div id="allmodals">
    <div class="m-content">
    <div class="m-portlet m-portlet--tabs">
        <div class="m-portlet__head">
            <div class="m-portlet__head-tools">
                <ul class="nav nav-tabs m-tabs-line m-tabs-line--primary m-tabs-line--2x" role="tablist">
                    <li class="nav-item m-tabs__item">
                        <a class="nav-link m-tabs__link active show" data-toggle="tab" href="#m_tabs_6_1" role="tab" aria-selected="true">
                            <i class="la la-delicious"></i> Volume
                        </a>
                    </li>
                    <li class="nav-item m-tabs__item">
                        <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_tabs_6_2" role="tab" aria-selected="false">
                            <i class="la la-rotate-right"></i> Subscription
                        </a>
                    </li>
                    <li class="nav-item m-tabs__item">
                        <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_tabs_6_3" role="tab" aria-selected="false">
                            <i class="la la-try"></i> Trials
                        </a>
                    </li>
                    <li class="nav-item m-tabs__item">
                        <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_tabs_6_4" role="tab" aria-selected="false">
                            <i class="la la-money"></i> Flat Fee
                        </a>
                    </li>

                </ul>
            </div>
        </div>
        <div class="m-portlet__body">
            <div class="tab-content">
                <div class="tab-pane active show" id="m_tabs_6_1" role="tabpanel">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Plan List
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item">
                                    <a href="#" class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air" data-toggle="modal" data-target="#m_modal_1">
						<span>
							<i class="la la-plus"></i>
							<span>New Plan</span>
						</span>
                                    </a>
                                </li>


                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin: Datatable -->
                        <div id="m_table_1_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="dataTables_length" id="m_table_1_length"><label>Show <select
                                                    name="m_table_1_length" aria-controls="m_table_1"
                                                    class="custom-select custom-select-sm form-control form-control-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> entries</label></div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div id="m_table_1_filter" class="dataTables_filter"><label>Search:<input type="search"
                                                                                                              class="form-control form-control-sm"
                                                                                                              placeholder=""
                                                                                                              aria-controls="m_table_1"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer dtr-inline"
                                           id="m_table_1" role="grid" aria-describedby="m_table_1_info" style="width: 1149px;">
                                        <thead>
                                        <tr role="row">
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                style="width: 32.45px;" aria-label="Type: activate to sort column ascending">
                                                ID
                                            </th>
                                            <th class="sorting_asc" tabindex="0" aria-controls="m_table_1" rowspan="1"
                                                colspan="1" style="width: 153.45px;" aria-sort="ascending"
                                                aria-label="Title: activate to sort column descending">Title
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                style="width: 295.45px;"
                                                aria-label="Summary: activate to sort column ascending">Summary
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                style="width: 197.45px;"
                                                aria-label="Remarks: activate to sort column ascending">Remarks
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                style="width: 197.45px;"
                                                aria-label="Descriptor: activate to sort column ascending"> Descriptor
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                aria-label="Type: activate to sort column ascending">Type
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                aria-label="Status: activate to sort column ascending">Status
                                            </th>

                                            <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 69.5px;"
                                                aria-label="Actions">Actions
                                            </th>
                                        </tr>
                                        </thead>

                                        <tbody>


                                        @foreach($plans as $key => $plan)
                                            @if($plan->plan_type == 2)
                                                <tr role="row" class="odd">
                                                    <td class="sorting_1" tabindex="0">
                                                        <div class="m-card-user m-card-user--sm">

                                                            <div class="m-card-user__details">
                                                                {{ $a++ }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $plan->settings->title }}</td>
                                                    <td><div class="alert alert-primary" role="alert">
                                                            <span>{{ $plan->settings->pr1 }} will be charged @ {{ $plan->settings->upr1 }}%</span></br>
                                                            <span>{{ $plan->settings->pr2 }} will be charged @ {{ $plan->settings->upr2 }}%</span></br>
                                                            <span>{{ $plan->settings->pr3 }} will be charged @ {{ $plan->settings->upr3 }}%</span>

                                                        </div></td>

                                                    <td>{{ $plan->settings->remarks }} </td>
                                                    <td>{{ $plan->settings->descriptor }}</td>
                                                    <td>
                                                        @if($plan->type == 1)
                                                            <div class="m-badge  m-badge--primary m-badge--wide">Custom</div>
                                                        @elseif($plan->type == 0)
                                                            <div class="m-badge  m-badge--success m-badge--wide">Default</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                           <span  class="m-switch m-switch--outline m-switch--icon m-switch--success">
                        <label>
                        <input  :checked="'{{$plan->status == 1}}'" @click="planStatus('{{$plan->id}}', '{{$plan->status}}')" type="checkbox">
                        <span></span>
                        </label>
                        </span>
                                                    </td>
                                                    <td nowrap="">
                        <span class="dropdown">
                            <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">
                              <i class="la la-ellipsis-h"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" data-toggle="modal" @click.prevent="editPlan('{{$plan->id}}', '{{$plan->type}}', '{{ $plan->settings->title }}', '{{ $plan->settings->pr1 }}', '{{ $plan->settings->pr2 }}', '{{ $plan->settings->pr3 }}', '{{ $plan->settings->upr1 }}','{{ $plan->settings->upr2 }}', '{{ $plan->settings->pr3 }}', '{{$plan->settings->remarks}}', '{{$plan->settings->descriptor}}' )" data-target="#m_modal_1"><i class="la la-edit"></i> Edit </a>
                                <a class="dropdown-item" href="#"><i class="la la-leaf"></i> Update Status</a>
                                <a class="dropdown-item" href="#"><i class="la la-print"></i> Generate Report</a>
                            </div>
                        </span>
                                                        <a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" title="View">
                                                            <i class="la la-edit"></i>
                                                        </a></td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="m_table_1_info" role="status" aria-live="polite">Showing 1
                                        to 10 of 50 entries
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" id="m_table_1_paginate">
                                        <ul class="pagination">
                                            <li class="paginate_button page-item previous disabled" id="m_table_1_previous"><a
                                                        href="#" aria-controls="m_table_1" data-dt-idx="0" tabindex="0"
                                                        class="page-link"><i class="la la-angle-left"></i></a></li>
                                            <li class="paginate_button page-item active"><a href="#" aria-controls="m_table_1"
                                                                                            data-dt-idx="1" tabindex="0"
                                                                                            class="page-link">1</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="2" tabindex="0"
                                                                                      class="page-link">2</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="3" tabindex="0"
                                                                                      class="page-link">3</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="4" tabindex="0"
                                                                                      class="page-link">4</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="5" tabindex="0"
                                                                                      class="page-link">5</a></li>
                                            <li class="paginate_button page-item next" id="m_table_1_next"><a href="#"
                                                                                                              aria-controls="m_table_1"
                                                                                                              data-dt-idx="6"
                                                                                                              tabindex="0"
                                                                                                              class="page-link"><i
                                                            class="la la-angle-right"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="tab-pane" id="m_tabs_6_2" role="tabpanel">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                    Subscription Plan List
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item">
                                    <a href="#" class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air" data-toggle="modal" data-target="#m_modal_2">
						<span>
							<i class="la la-plus"></i>
							<span>New Subscription</span>
						</span>
                                    </a>
                                </li>


                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin: Datatable -->
                        <div id="m_table_1_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="dataTables_length" id="m_table_1_length"><label>Show <select
                                                    name="m_table_1_length" aria-controls="m_table_1"
                                                    class="custom-select custom-select-sm form-control form-control-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> entries</label></div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div id="m_table_1_filter" class="dataTables_filter"><label>Search:<input type="search"
                                                                                                              class="form-control form-control-sm"
                                                                                                              placeholder=""
                                                                                                              aria-controls="m_table_1"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer dtr-inline"
                                           id="m_table_1" role="grid" aria-describedby="m_table_1_info" style="width: 1149px;">
                                        <thead>
                                        <tr role="row">
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                style="width: 32.45px;" aria-label="Type: activate to sort column ascending">
                                                ID
                                            </th>
                                            <th class="sorting_asc" tabindex="0" aria-controls="m_table_1" rowspan="1"
                                                colspan="1" style="width: 153.45px;" aria-sort="ascending"
                                                aria-label="Title: activate to sort column descending">Title
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_2" rowspan="1" colspan="1"
                                                style="width: 245.45px;"
                                                aria-label="Summary: activate to sort column ascending">Fee
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_2" rowspan="1" colspan="1"
                                                style="width: 197.45px;"
                                                aria-label="Remarks: activate to sort column ascending">Remarks
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_2" rowspan="1" colspan="1"
                                                style="width: 197.45px;"
                                                aria-label="Statement Description: activate to sort column ascending">Statement Description
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_2" rowspan="1" colspan="1"
                                                aria-label="Status Description: activate to sort column ascending">Status
                                            </th>


                                            <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 69.5px;"
                                                aria-label="Actions">Actions
                                            </th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($plans as $key => $plan)
                                            @if($plan->plan_type == 3)
                                                <tr role="row" class="odd">
                                                    <td class="sorting_1" tabindex="0">
                                                       {{ $b++ }}
                                                    </td>
                                                    <td>{{$plan->settings->title}}</td>
                                                    <td><div class="alert alert-primary" role="alert">
                                                            <span>{{$plan->settings->fee}} Days </span>


                                                        </div></td>

                                                    <td>{{ $plan->settings->remarks }}</td>
                                                    <td>{{ $plan->settings->descriptor }}</td>
                                                    <td><span  class="m-switch m-switch--outline m-switch--icon m-switch--success">
                        <label>
                        <input :checked="'{{$plan->status == 1}}'" @click="planStatus('{{$plan->id}}', '{{$plan->status}}')" type="checkbox">
                        <span></span>
                        </label>
                        </span> </td>
                                                    <td nowrap="">
                        <span class="dropdown">
                            <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">
                              <i class="la la-ellipsis-h"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" data-toggle="modal" @click.prevent="editsubsPlan('{{$plan->id}}', '{{ $plan->settings->title }}', '{{ $plan->settings->fee }}', '{{$plan->settings->remarks}}', '{{$plan->settings->descriptor}}' )" data-target="#m_modal_2"><i class="la la-edit"></i> Edit </a>
                                <a class="dropdown-item" href="#"><i class="la la-leaf"></i> Update Status</a>
                                <a class="dropdown-item" href="#"><i class="la la-print"></i> Generate Report</a>
                            </div>
                        </span>
                                                        <a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" title="View">
                                                            <i class="la la-edit"></i>
                                                        </a></td>
                                                </tr>
                                                @endif

                                        @endforeach



                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="m_table_1_info" role="status" aria-live="polite">Showing 1
                                        to 10 of 50 entries
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" id="m_table_1_paginate">
                                        <ul class="pagination">
                                            <li class="paginate_button page-item previous disabled" id="m_table_1_previous"><a
                                                        href="#" aria-controls="m_table_1" data-dt-idx="0" tabindex="0"
                                                        class="page-link"><i class="la la-angle-left"></i></a></li>
                                            <li class="paginate_button page-item active"><a href="#" aria-controls="m_table_1"
                                                                                            data-dt-idx="1" tabindex="0"
                                                                                            class="page-link">1</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="2" tabindex="0"
                                                                                      class="page-link">2</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="3" tabindex="0"
                                                                                      class="page-link">3</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="4" tabindex="0"
                                                                                      class="page-link">4</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="5" tabindex="0"
                                                                                      class="page-link">5</a></li>
                                            <li class="paginate_button page-item next" id="m_table_1_next"><a href="#"
                                                                                                              aria-controls="m_table_1"
                                                                                                              data-dt-idx="6"
                                                                                                              tabindex="0"
                                                                                                              class="page-link"><i
                                                            class="la la-angle-right"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="m_tabs_6_3" role="tabpanel">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                   Trial Plan List
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item">
                                    <a href="#" class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air" data-toggle="modal" data-target="#m_modal_3">
						<span>
							<i class="la la-plus"></i>
							<span>New Trial Plan</span>
						</span>
                                    </a>
                                </li>


                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin: Datatable -->
                        <div id="m_table_1_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="dataTables_length" id="m_table_1_length"><label>Show <select
                                                    name="m_table_1_length" aria-controls="m_table_1"
                                                    class="custom-select custom-select-sm form-control form-control-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> entries</label></div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div id="m_table_1_filter" class="dataTables_filter"><label>Search:<input type="search"
                                                                                                              class="form-control form-control-sm"
                                                                                                              placeholder=""
                                                                                                              aria-controls="m_table_3"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer dtr-inline"
                                           id="m_table_1" role="grid" aria-describedby="m_table_3_info" style="width: 1149px;">
                                        <thead>
                                        <tr role="row">
                                            <th class="sorting" tabindex="0" aria-controls="m_table_3" rowspan="1" colspan="1"
                                                style="width: 32.45px;" aria-label="Type: activate to sort column ascending">
                                                ID
                                            </th>
                                            <th class="sorting_asc" tabindex="0" aria-controls="m_table_3" rowspan="1"
                                                colspan="1" style="width: 153.45px;" aria-sort="ascending"
                                                aria-label="Title: activate to sort column descending">Title
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_3" rowspan="1" colspan="1"
                                                style="width: 245.45px;"
                                                aria-label="Period: activate to sort column ascending">Trial Period
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_3" rowspan="1" colspan="1"
                                                aria-label="Status: activate to sort column ascending">Status
                                            </th>
                                            <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 69.5px;"
                                                aria-label="Actions">Actions
                                            </th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($plans as $key => $plan)
                                            @if($plan->plan_type == 1)
                                                <tr role="row" class="odd">
                                                    <td class="sorting_1" tabindex="0">
                                                         {{$c++}}
                                                    </td>
                                                    <td>{{$plan->settings->title}}</td>
                                                    <td><div class="alert alert-primary" role="alert">
                                                            <span>{{$plan->settings->period}} Days</span>
                                                        </div></td>
                                            <td><span  class="m-switch m-switch--outline m-switch--icon m-switch--success">
                                                    <label>
                                                    <input  :checked="'{{$plan->status == 1}}'" @click="planStatus('{{$plan->id}}', '{{$plan->status}}')" type="checkbox">
                                                    <span></span>
                                                    </label>
                                                    </span> </td>
                                                    <td nowrap="">
                        <span class="dropdown">
                            <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">
                              <i class="la la-ellipsis-h"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" data-toggle="modal" @click.prevent="editTrialPlan('{{$plan->id}}', '{{ $plan->settings->type }}', '{{ $plan->settings->title }}', '{{ $plan->settings->period }}')" data-target="#m_modal_3"><i class="la la-edit"></i> Edit </a>
                                <a class="dropdown-item" href="#"><i class="la la-leaf"></i> Update Status</a>
                                <a class="dropdown-item" href="#"><i class="la la-print"></i> Generate Report</a>
                            </div>
                        </span>
                                                        <a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" title="View">
                                                            <i class="la la-edit"></i>
                                                        </a></td>
                                                </tr>
                                            @endif



                                         @endforeach



                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="m_table_1_info" role="status" aria-live="polite">Showing 1
                                        to 10 of 50 entries
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" id="m_table_1_paginate">
                                        <ul class="pagination">
                                            <li class="paginate_button page-item previous disabled" id="m_table_1_previous"><a
                                                        href="#" aria-controls="m_table_1" data-dt-idx="0" tabindex="0"
                                                        class="page-link"><i class="la la-angle-left"></i></a></li>
                                            <li class="paginate_button page-item active"><a href="#" aria-controls="m_table_1"
                                                                                            data-dt-idx="1" tabindex="0"
                                                                                            class="page-link">1</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="2" tabindex="0"
                                                                                      class="page-link">2</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="3" tabindex="0"
                                                                                      class="page-link">3</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="4" tabindex="0"
                                                                                      class="page-link">4</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="5" tabindex="0"
                                                                                      class="page-link">5</a></li>
                                            <li class="paginate_button page-item next" id="m_table_1_next"><a href="#"
                                                                                                              aria-controls="m_table_1"
                                                                                                              data-dt-idx="6"
                                                                                                              tabindex="0"
                                                                                                              class="page-link"><i
                                                            class="la la-angle-right"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="m_tabs_6_4" role="tabpanel">
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-caption">
                            <div class="m-portlet__head-title">
                                <h3 class="m-portlet__head-text">
                                   Flat Fee Plan List
                                </h3>
                            </div>
                        </div>
                        <div class="m-portlet__head-tools">
                            <ul class="m-portlet__nav">
                                <li class="m-portlet__nav-item">
                                    <a href="#" class="btn btn-primary m-btn m-btn--pill m-btn--custom m-btn--icon m-btn--air" data-toggle="modal" data-target="#m_modal_4">
						<span>
							<i class="la la-plus"></i>
							<span>New Flat Fee Plan</span>
						</span>
                                    </a>
                                </li>


                            </ul>
                        </div>
                    </div>
                    <div class="m-portlet__body">
                        <!--begin: Datatable -->
                        <div id="m_table_1_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="dataTables_length" id="m_table_1_length"><label>Show <select
                                                    name="m_table_1_length" aria-controls="m_table_1"
                                                    class="custom-select custom-select-sm form-control form-control-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> entries</label></div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div id="m_table_1_filter" class="dataTables_filter"><label>Search:<input type="search"
                                                                                                              class="form-control form-control-sm"
                                                                                                              placeholder=""
                                                                                                              aria-controls="m_table_1"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-striped- table-bordered table-hover table-checkable dataTable no-footer dtr-inline"
                                           id="m_table_1" role="grid" aria-describedby="m_table_1_info" style="width: 1149px;">
                                        <thead>
                                        <tr role="row">
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                style="width: 32.45px;" aria-label="Type: activate to sort column ascending">
                                                ID
                                            </th>
                                            <th class="sorting_asc" tabindex="0" aria-controls="m_table_1" rowspan="1"
                                                colspan="1" style="width: 153.45px;" aria-sort="ascending"
                                                aria-label="Title: activate to sort column descending">Title
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                style="width: 245.45px;"
                                                aria-label="Summary: activate to sort column ascending">Flat Fee Plan
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                style="width: 197.45px;"
                                                aria-label="Remarks: activate to sort column ascending">Remarks
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="m_table_1" rowspan="1" colspan="1"
                                                aria-label="Remarks: activate to sort column ascending">Status
                                            </th>
                                            <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 69.5px;"
                                                aria-label="Actions">Actions
                                            </th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($plans as $key => $plan)

                                                @if($plan->plan_type == 4)
                                                <tr role="row" class="odd">
                                                    <td class="sorting_1" tabindex="0">
                                                       {{$d++}}
                                                    </td>
                                                    <td>{{$plan->settings->title}}</td>
                                                    <td><div class="alert alert-primary" role="alert">
                                                            <span>{{$plan->settings->fee}}   Flat Fee </br>
                                                                 {{$plan->settings->onTrasaction}}  Per Transaction Fee</span>

                                                        </div></td>

                                                    <td>{{$plan->settings->remarks}}</td>
                                                    <td><span  class="m-switch m-switch--outline m-switch--icon m-switch--success">
                                                    <label>
                                                    <input  :checked="'{{$plan->status == 1}}'" @click="planStatus('{{$plan->id}}', '{{$plan->status}}')" type="checkbox">
                                                    <span></span>
                                                    </label>
                                                    </span> </td>


                                                    <td nowrap="">
                        <span class="dropdown">
                            <a href="#" class="btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="dropdown" aria-expanded="true">
                              <i class="la la-ellipsis-h"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" data-toggle="modal" @click.prevent="editFlatfeePlan('{{$plan->id}}', '{{ $plan->settings->type }}', '{{ $plan->settings->title }}', '{{ $plan->settings->fee }}', '{{ $plan->settings->onTrasaction }}', '{{ $plan->settings->remarks }}')" data-target="#m_modal_4"><i class="la la-edit"></i> Edit </a>
                                <a class="dropdown-item" href="#"><i class="la la-leaf"></i> Update Status</a>
                                <a class="dropdown-item" href="#"><i class="la la-print"></i> Generate Report</a>
                            </div>
                        </span>
                                                        <a href="#" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" title="View">
                                                            <i class="la la-edit"></i>
                                                        </a></td>
                                                </tr>
                                                @endif

                                        @endforeach
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="m_table_1_info" role="status" aria-live="polite">Showing 1
                                        to 10 of 50 entries
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate paging_simple_numbers" id="m_table_1_paginate">
                                        <ul class="pagination">
                                            <li class="paginate_button page-item previous disabled" id="m_table_1_previous"><a
                                                        href="#" aria-controls="m_table_1" data-dt-idx="0" tabindex="0"
                                                        class="page-link"><i class="la la-angle-left"></i></a></li>
                                            <li class="paginate_button page-item active"><a href="#" aria-controls="m_table_1"
                                                                                            data-dt-idx="1" tabindex="0"
                                                                                            class="page-link">1</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="2" tabindex="0"
                                                                                      class="page-link">2</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="3" tabindex="0"
                                                                                      class="page-link">3</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="4" tabindex="0"
                                                                                      class="page-link">4</a></li>
                                            <li class="paginate_button page-item "><a href="#" aria-controls="m_table_1"
                                                                                      data-dt-idx="5" tabindex="0"
                                                                                      class="page-link">5</a></li>
                                            <li class="paginate_button page-item next" id="m_table_1_next"><a href="#"
                                                                                                              aria-controls="m_table_1"
                                                                                                              data-dt-idx="6"
                                                                                                              tabindex="0"
                                                                                                              class="page-link"><i
                                                            class="la la-angle-right"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>


    <!--begin::Modal-->
    <div class="modal fade" id="m_modal_1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create Volume Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-row">

                        <div class=" form-group col-md-6">

                            <div class="m-radio-inline">
                                <label class="m-radio">
                                    <input type="radio" v-model="volumePlanData.type" checked value="1"> Custom Plan
                                    <span></span>
                                </label>
                                <label class="m-radio">
                                    <input type="radio" v-model="volumePlanData.type" value="0"> Default
                                    <span></span>
                                </label>
                            </div>

                                <label for="title">Title</label>
                                <input type="text" class="form-control" v-model="volumePlanData.title" placeholder="Plan Title">
                        </div>
                            <div class=" form-group col-md-6">
                                <div class="alert alert-primary" role="alert">
                                    <strong>Summary</br>
                                    <span>$@{{volumePlanData.pr1}} will be charged @ @{{volumePlanData.upr1}}%</span></br>
                                    <span>$@{{volumePlanData.pr2}} will be charged @ @{{volumePlanData.upr2}}%</span></br>
                                    <span>$@{{volumePlanData.pr3}} will be charged @ @{{volumePlanData.upr3}}%</span></strong>

                                </div>


                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">

                                <div class="form-group m-form__group">
                                    <label for="exampleInputEmail1"><strong>Percentage %</strong></label>
                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">USD</span></div>
                                        <input type="number" class="form-control m-input" min="0" v-model="volumePlanData.pr1" placeholder="0.00" aria-describedby="basic-addon1" required>
                                    </div>
                                </div>
                                <div class="form-group m-form__group">

                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">USD</span></div>
                                        <input type="number" class="form-control m-input" min="0" v-model="volumePlanData.pr2" placeholder="0.00" aria-describedby="basic-addon1" required>
                                    </div>
                                </div>
                                <div class="form-group m-form__group">

                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">USD</span></div>
                                        <input type="number" class="form-control m-input" min="0" v-model="volumePlanData.pr3" placeholder="0.00" aria-describedby="basic-addon1" required>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group col-md-6">

                                <div class="form-group m-form__group">
                                    <label for="exampleInputEmail1"><strong>Upper Limit</strong></label>
                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">USD</span></div>
                                        <input type="number" class="form-control m-input" min="0" v-model="volumePlanData.upr1" placeholder="0.00" aria-describedby="basic-addon1" required>
                                    </div>
                                </div>
                                <div class="form-group m-form__group">

                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">USD</span></div>
                                        <input type="number" class="form-control m-input" min="0" v-model="volumePlanData.upr2" placeholder="0.00" aria-describedby="basic-addon1" required>
                                    </div>
                                </div>
                                <div class="form-group m-form__group">

                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">USD</span></div>
                                        <input type="number" class="form-control m-input" min="0" v-model="volumePlanData.upr3" placeholder="0.00" aria-describedby="basic-addon1" required>
                                    </div>
                                </div>

                            </div>
                        </div>
                         <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4"> Volume Charge Remarks</label>
                                <input type="text" class="form-control"  v-model="volumePlanData.remarks" placeholder="Remarks">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputPassword4">Statement Descriptor</label>
                                <input type="text" class="form-control" id="description" v-model="volumePlanData.descriptor" placeholder="Descriptor">
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" @click.prevent="createnew(2)" class="btn btn-success">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->
    <!--begin::Modal-->
    <div class="modal fade" id="m_modal_2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create Subscription Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>


                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4"><strong>Subscription Title</strong></label>
                                <div class="form-group">
                                    <input type="text" class="form-control" v-model="subscriptionPlanData.title" required>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-group m-form__group">
                                    <label for="exampleInputEmail1"><strong>Flat Fee</strong></label>
                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">USD</span></div>
                                        <input type="number" min="0" class="form-control m-input" v-model="subscriptionPlanData.fee" placeholder="0.00" aria-describedby="basic-addon1" required>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4">Volum Charge Remarks</label>
                                <input type="text" class="form-control"  v-model="subscriptionPlanData.remarks" placeholder="Remarks" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputPassword4">Statement Descriptor</label>
                                <input type="text" class="form-control" id="description" v-model="subscriptionPlanData.descriptor" placeholder="Descriptor" required>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button"  @click.prevent="createnew(3)" class="btn btn-success">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->
    <!--begin::Modal-->
    <div class="modal fade" id="m_modal_3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create Trial Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>


                        <div class="form-row">
                            <div class="m-radio-inline">
                                <label class="m-radio">
                                    <input type="radio" v-model="trialPlanData.type" checked value="1"> Custom
                                    <span></span>
                                </label>
                                <label class="m-radio">
                                    <input type="radio" v-model="trialPlanData.type" value="0"> Default
                                    <span></span>
                                </label>
                            </div>
                        </div>
                            <div class="form-row">
                            <div class=" form-group col-md-6">
                                <label for="title"><strong>Title</strong></label>
                                <input type="text" class="form-control" v-model="trialPlanData.title" placeholder="Plan Title">
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-group m-form__group">
                                    <label for="exampleInputEmail1"><strong>Trial Period</strong></label>
                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">Days</span></div>
                                        <input type="number" min="0" class="form-control m-input" v-model="trialPlanData.period" placeholder="00" aria-describedby="basic-addon1">
                                    </div>
                                </div>
                            </div>


                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" @click.prevent="createnew(1)" class="btn btn-success">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->
    <!--begin::Modal-->
    <div class="modal fade" id="m_modal_4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Flat Fee Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-row">
                            <div class=" form-group col-md-6">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" v-model="flatfeePlanData.title" >
                            </div>
                        </div>
                        <div class="form-row">
                            <div class=" form-group col-md-6">
                                <div class="form-group m-form__group">
                                    <label for="fee"><strong>Flat Fee</strong></label>
                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">USD</span></div>
                                        <input type="number" min="0" id="fee" class="form-control m-input" v-model="flatfeePlanData.fee" placeholder="00" aria-describedby="basic-addon1">
                                    </div>
                                </div>

                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-group m-form__group">
                                    <label for="onTrasaction"><strong>Per Transaction Fee</strong></label>
                                    <div class="input-group m-input-group ">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon1">cents</span></div>
                                        <input type="number" min="0" id="onTrasaction" class="form-control m-input" v-model="flatfeePlanData.onTrasaction" placeholder="00" aria-describedby="basic-addon1">
                                    </div>
                                </div>


                            </div>


                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4">Remarks</label>
                                <input type="text" class="form-control" v-model="flatfeePlanData.remarks">
                            </div>

                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success"  @click.prevent="createnew(4)" >Save</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->



</div>
@endsection
@section('ajax_script')
    <script src="{{asset('assets/vendors/custom/datatables/datatables.bundle.js')}}" type="text/javascript"></script>

 <script type="application/javascript">

        var NewPlan = new Vue({
            el: '#allmodals',
            data() {
                return {

                    id:0,
                    volumePlanData: {
                        plan:2,
                        type:1,
                        title:'',
                        pr1: 0,
                        pr2: 0,
                        pr3: 0,
                        upr1:0,
                        upr2:0,
                        upr3:0,
                        remarks: '',
                        descriptor: ''
                    },
                    subscriptionPlanData: {
                        plan:3,
                        type:1,
                        title:'',
                        fee: 0,
                        remarks: '',
                        descriptor: ''
                    },
                    trialPlanData: {
                        plan:1,
                        type: 1,
                        title:'',
                        period: 0,
                    },
                    flatfeePlanData: {
                        plan:4,
                        type:1,
                        title:'',
                        fee: 0,
                        onTrasaction: 0,
                        remarks: '',

                    },
                    msg:'Saved Successfully'
                }
            },
            methods: {
                planStatus(id,sts){
                    let status = '';
                    if(sts == 1){
                        status = 0
                    }else if (sts == 0){
                        status = 1
                    }
                    axios.post('/admin/planstatus/' + id + '/' + status)
                                .then((response) => {

                                    if (response.data.done == 1 ){
                                        toastr.success(msg);

                                    } else if (response.data.done == 2 ) {
                                        alert('Somthing Wrong!')


                                    }

                                },(error) => {
                                    //console.log("Hi I'm Error   ");
                                    // error callback
                                })




                },
                createnew(plan){


                    var _this = this;
                    var vm = this.hasErrors;
                    var _vm = this.errorMessage;
                    var data = '';
                    if(plan == 1){
                        data = _this.trialPlanData;
                    }else if (plan == 2){
                        data = _this.volumePlanData;
                    }else if (plan == 3){
                        data = _this.subscriptionPlanData;
                    }else if (plan == 4){
                        data = _this.flatfeePlanData;
                    }
                    var route = ''
                    if(_this.id == 0){
                        route = '/admin/createnewplan'
                    }else if(_this.id != 0){
                        route ='/admin/updateplan/'+_this.id
                    }

                    axios.post(route, data)
                        .then(function (response) {
                            //console.log(response);
                            if (response.data.done == 1) {
                                toastr.success(_this.msg);
                                window.location.reload();

                            }else {
                                alert('Somthing Wrong!')
                            }

                        })
                        .catch(function (error) {
                            var errors = error.response
                            if (errors.status == 422) {
                                if (errors.data) {
                                    if (errors.data.errors.name) {
                                        let err = errors.data.errors
                                        vm.name = true
                                        _vm.name = Array.isArray(err.name) ? err.name[0] : err.name
                                    }


                                }
                            }
                        });
                },
                editvolumePlan(id,type,title,pr1,pr2,pr3,upr1,upr2,upr3,remarks,descriptor){

                   var th = this
                    th.id=id
                    th.volumePlanData.type=type
                    th.volumePlanData.title=title
                    th.volumePlanData.pr1= pr1
                    th.volumePlanData.pr2= pr2
                    th.volumePlanData.pr3= pr3
                    th.volumePlanData.upr1=upr1
                    th.volumePlanData.upr2=upr2
                    th.volumePlanData.upr3=upr3
                    th.volumePlanData.remarks= remarks
                    th.volumePlanData.descriptor= descriptor
                },
                editsubsPlan(id,title,fee,remarks,descriptor){
                    var th = this
                    th.id=id
                        th.subscriptionPlanData.title = title
                        th.subscriptionPlanData.fee = fee
                        th.subscriptionPlanData.remarks = remarks
                        th.subscriptionPlanData.descriptor = descriptor
                },
                editTrialPlan(id,type,title,period){
                    var th = this
                    th.id=id

                            th.trialPlanData.type = type
                            th.trialPlanData.title = title
                            th.trialPlanData.period = period

                },
                editFlatfeePlan(id,type,title,fee,onTrasaction,remarks){
                    var th = this
                    th.id=id

                    th.flatfeePlanData.type = type
                        th.flatfeePlanData.title = title
                        th.flatfeePlanData.fee = fee
                        th.flatfeePlanData.onTrasaction = onTrasaction
                        th.flatfeePlanData.remarks = remarks

                },
            }

        })

        //=================================
        // Memeber status update code below
        //===============================


 </script>






@endsection