@extends('layouts.admin')
@section('content')
	<!-- BEGIN: Subheader -->
	<div class="m-subheader ">

		<div class="d-flex align-items-center">
			<div class="mr-auto">
				<h3 class="m-subheader__title m-subheader__title--separator">User's Team Member</h3>
				<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
					<li class="m-nav__item m-nav__item--home">
						<a href="/admin/dashboard" class="m-nav__link m-nav__link--icon">
							<i class="m-nav__link-icon la la-home"></i>
						</a>
					</li>
					<li class="m-nav__separator">-</li>

					<li class="m-nav__item">
						<a href="/admin/user-accounts" class="m-nav__link m-nav__link--icon">
							<span class="m-nav__link-text">User Account List </span>
						</a>
					</li>
					<li class="m-nav__separator">-</li>
					<li class="m-nav__item">
						<a href="{{ route('account_users', $data['user_account_id']) }}" class="m-nav__link m-nav__link--icon">
							<span class="m-nav__link-text">Team Members Listing</span>
						</a>
					</li>
					<li class="m-nav__separator">-</li>
					<li class="m-nav__item">
						<span class="m-nav__link-text">Team Member Profile </span>
					</li>
				</ul>
			</div>

		</div>
	</div>
	<!-- END: Subheader -->


    <div class="m-content">
		<div class="m-portlet m-portlet--full-height m-portlet--tabs  ">
					<div class="m-portlet__head">
						<div class="m-portlet__head-tools">
							<ul class="nav nav-tabs m-tabs m-tabs-line   m-tabs-line--left m-tabs-line--primary"
								role="tablist">

								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link active show" data-toggle="tab"
									   href="#m_user_profile_tab_1" role="tab" aria-selected="false">
										Team Member Details
									</a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link" data-toggle="tab"
									   href="#m_user_profile_tab_2" role="tab" aria-selected="false">
										History
									</a>
								</li>

							</ul>
						</div>

					</div>
					<div class="tab-content">
						<div class="tab-pane active show" id="m_user_profile_tab_1" >
							<div class="m-widget13">
				                <div class="m-widget13__item">
								<span class="m-widget13__desc m--align-right">
								Name
								</span>
				                    <span class="m-widget13__text m-widget13__text-bolder">
								{{$data->name}}
								</span>
				                </div>
				                <div class="m-widget13__item">
								<span class="m-widget13__desc m--align-right">
								Email
								</span>
				                    <span class="m-widget13__text m-widget13__text-bolder">
								{{$data->email}}
								</span>
				                </div>
				                <div class="m-widget13__item">
								<span class="m-widget13__desc m--align-right">
								Contact
								</span>
				                    <span class="m-widget13__text m-widget13__text-bolder">
								{{$data->phone}}
								</span>
				                </div>
				                <div class="m-widget13__item">
								<span class="m-widget13__desc m--align-right">
								Address
								</span>
				                    <span class="m-widget13__text m-widget13__text-bolder">
								{{$data->address}}
								</span>
				                </div>
				                <div class="m-widget13__item">
								<span class="m-widget13__desc m--align-right">
								City
								</span>
				                    <span class="m-widget13__text">
								{{$data->city}}
								</span>
				                </div>
				                <div class="m-widget13__item">
								<span class="m-widget13__desc m--align-right">
								State
								</span>
				                    <span class="m-widget13__text m-widget13__number-bolder m--font-brand">
								{{$data->state}}
								</span>
				                </div>
				                <div class="m-widget13__item">
								<span class="m-widget13__desc m--align-right">
								Country
								</span>
				                    <span class="m-widget13__text m-widget13__number-bolder m--font-brand">
								{{$data->country}}
								</span>
				                </div>


				            </div>
						</div>


						<div class="tab-pane" id="m_user_profile_tab_2" >
					        <div class="table-responsive col-md-12">
                   				<table class="table table-striped- table-bordered table-hover table-checkable" id="companies_logs_table">
                                    <thead>
	                                    <tr>
	                                        <th>Field</th>
											<th>Action</th>
											<th>Date & Time</th>
											<th>Old Value</th>
											<th>New Value</th>
	                                    </tr>
                                    </thead>
										
								</table>
                            </div>
						</div>
					</div>
		</div>
    </div>




@endsection


@section('ajax_script')
	<script src="{{asset('assets/vendors/custom/datatables/datatables.bundle.js')}}" type="text/javascript"></script>
	<script type="application/javascript">

        $(function () {
            $('#companies_logs_table').DataTable({
                processing: true,
                pageLength: 5,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: "{{ route('team_member_audit_logs', ['id' => $data['id']]) }}",
                columns: [
                    {data: 'field', name: 'field'},
                    {data: 'event', name: 'action'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'old_value', name: 'old_value'},
                    {data: 'new_value', name: 'new_value'}
                ]
            });
        });
	</script>
@endsection