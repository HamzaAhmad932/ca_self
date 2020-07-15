@extends('layouts.admin')
@section('content')
	<style>
		.ermsg{
			display: none;
		}
		.ovrly {
			position: relative;
			width: 50%;
		}

		.image {
			opacity: 1;
			display: block;
			width: 100%;
			height: auto;
			transition: .5s ease;
			backface-visibility: hidden;
		}

		.middle {
			position: absolute;  dd('sdfdsf');
			top: 0;
			bottom: 0;
			left: 0;
			right: 0;
			height: 100%;
			width: 100%;
			opacity: 0;
			transition: .3s ease;
			background-color: #000000;
			border-radius: 100%;
			text-align: center;
			color: #ffffff;
			font-size: 38px;
		}

		.ovrly:hover .image {
			opacity: 0.3;
		}

		.ovrly:hover .middle {
			opacity: 0.5;
		}

		#CompanyLogo {
			display: none;
		}
	</style>
	<!-- BEGIN: Subheader -->
	<div class="m-subheader ">

		<div class="d-flex align-items-center">
			<div class="mr-auto">
				<h3 class="m-subheader__title m-subheader__title--separator">{{__('admin/adminteamcontent.user_account')}}</h3>
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

						<span class="m-nav__link-text">User Account Profile </span>

					</li>
				</ul>
			</div>

		</div>
	</div>
	<!-- END: Subheader -->


	<div class="m-content">
		<div class="row">
			<div class="col-xl-3 col-lg-4">
				<div class="m-portlet m-portlet--full-height  ">
					<div class="m-portlet__body">
						<div class="m-card-profile">
							<div class="m-card-profile__title m--hide">
								Company Profile
							</div>
							<div class="m-card-profile__pic" id="logoCvr">
								<div class="m-card-profile__pic-wrapper ovrly">
									<div class="m-loader m-loader--brand" id="loader" style="width: 30px; display: none; "></div>
									<img :src="logoimg" alt="Company Logo" id="Displaylogo" class="image">

									<div class="middle">

										<input type="file" name="file" ref="file" id="CompanyLogo"  @change="logoBtn()" class="inputfile inputfile-4"/>
										<label for="CompanyLogo"><i class="la la-camera-retro" style="cursor: pointer;"></i></label>

									</div>



								</div>
							</div>
							<div class="m-card-profile__details">



								<span class="m-card-profile__name"> </span>
								<a href="" class="m-card-profile__email m-link"></a>
							</div>
						</div>

					</div>
				</div>
			</div>
			<div class="col-xl-9 col-lg-8">
				<div class="m-portlet m-portlet--full-height m-portlet--tabs  ">
					<div class="m-portlet__head">
						<div class="m-portlet__head-tools">
							<ul class="nav nav-tabs m-tabs m-tabs-line   m-tabs-line--left m-tabs-line--primary"
								role="tablist">

								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link active show" data-toggle="tab"
									   href="#m_user_profile_tab_1" role="tab" aria-selected="false">
										User Profile
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
							<div id="ProfileUpdate">
								<form  class="m-form m-form--fit m-form--label-align-right" method='post'>
									@csrf

									<div class="m-portlet__body">
	                                    <div class="row">
	                                        <div class="col-xl-10">
	                                            <div class="m-form__section m-form__section--first">
	                                                <div class="m-form__heading">
	                                                    <h3 class="m-form__heading-title">{{ __('client/company_profile.client_details') }}</h3>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.name') }}</label>
	                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.name}">
	                                                        <input type="text" name="name" class="form-control m-input"
	                                                               v-model="updateData.name"  autocomplete="off">
	                                                        <span v-if="hasErrors.name" class="invalid-feedback ermsg" role="alert">
	                                                         <strong>@{{errorMessage.name}}</strong>
	                                                         </span>
	                                                    </div>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.email') }}</label>
	                                                    <div class="col-xl-9 col-lg-9">
	                                                        <input type="email" name="email" class="form-control m-input"
	                                                               value="{{ $data['client']->email }}" disabled>
	                                                    </div>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.phone') }}</label>
	                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.phone}">
	                                                        <div class="input-group">
	                                                            <div class="input-group-prepend"><span
	                                                                        class="input-group-text"><i
	                                                                            class="la la-phone"></i></span></div>
	                                                            <input type="text" name="phone" class="form-control m-input" v-model="updateData.phone" >
	                                                            <span v-if="hasErrors.phone" class="invalid-feedback ermsg" role="alert">
	                                                            <strong>@{{errorMessage.phone}}</strong>
	                                                            </span>
	                                                        </div>

	                                                    </div>
	                                                </div>
	                                            </div>
	                                            <div class="m-separator m-separator--dashed m-separator--lg"></div>
	                                            <div class="m-form__section">
	                                                <div class="m-form__heading">
	                                                    <h3 class="m-form__heading-title">
	                                                        Mailing Address
	                                                        <i data-toggle="m-tooltip" data-width="auto"
	                                                           class="m-form__heading-help-icon flaticon-info"
	                                                           title="Address for the client"></i>
	                                                    </h3>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.address') }}</label>
	                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.address}">
	                                                        <input type="text" name="address" class="form-control m-input" v-model="updateData.address">
	                                                        <span v-if="hasErrors.address" class="invalid-feedback ermsg" role="alert">
	                                                            <strong>@{{errorMessage.address}}</strong>
	                                                            </span>

	                                                    </div>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.address2') }}</label>
	                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.address2}">
	                                                        <input type="text" name="address2" class="form-control m-input" v-model="updateData.address2">
	                                                        <span v-if="hasErrors.address2" class="invalid-feedback ermsg" role="alert">
	                                                            <strong>@{{errorMessage.address2}}</strong>
	                                                            </span>
	                                                    </div>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.city') }}</label>
	                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.city}">
	                                                        <input type="text" name="city" class="form-control m-input" v-model="updateData.city">
	                                                        <span v-if="hasErrors.city" class="invalid-feedback ermsg" role="alert">
	                                                            <strong>@{{errorMessage.city}}</strong>
	                                                            </span>
	                                                    </div>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.state') }}</label>
	                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.state}">
	                                                        <input type="text" name="state" class="form-control m-input" v-model="updateData.state">
	                                                        <span v-if="hasErrors.state" class="invalid-feedback ermsg" role="alert">
	                                                            <strong>@{{errorMessage.state}}</strong>
	                                                            </span>
	                                                    </div>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.country') }}</label>
	                                                    <div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.country}">
	                                                        <input type="text" name="country" class="form-control m-input" v-model="updateData.country">
	                                                        <span v-if="hasErrors.country" class="invalid-feedback ermsg" role="alert">
	                                                            <strong>@{{errorMessage.country}}</strong>
	                                                            </span>
	                                                    </div>
	                                                </div>
	                                                <div class="form-group m-form__group row">
	                                                    <label class="col-xl-3 col-lg-3 form-control-label">{{ __('client/company_profile.website') }}</label>
	                                                    <div class="col-lg-9" :class="{'has-error' : hasErrors.website}">
	                                                        <input type="url" name="website" class="form-control m-input" v-model="updateData.website">
	                                                        <span v-if="hasErrors.website" class="invalid-feedback ermsg" role="alert">
	                                                            <strong>@{{errorMessage.website}}</strong>
	                                                            </span>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        </div>
	                                    </div>
	                                </div>


	                                <div class="m-portlet__foot m-portlet__foot--fit">
	                                    <div class="m-form__actions">
	                                        <div class="row">
	                                            <div class="col-2">
	                                            </div>
	                                            <div class="col-7">
	                                                <button type="submit"
	                                                        class="btn btn-accent m-btn m-btn--air m-btn--custom"
	                                                        @click.prevent="ClientUpdateBtn()">{{ __('client/company_profile.save') }}</button>
	                                                <a href="/client/manageteam" class="btn btn-secondary m-btn m-btn--air
	                                                m-btn--custom">{{ __('client/company_profile.cancel') }}</a>
	                                            </div>
	                                        </div>
	                                    </div>
	                                </div>
								</form>
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
		</div>
	</div>
	<!--begin::Modal-->
	<div class="modal fade" id="m_modal_5" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">{{__('client/company_profile.new_password')}}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div id="newpass">
					<form name="passwordForm" menthod="post" >
						<div class="modal-body" >

							<div :class="{'has-error' : hasErrors.password}">
								<label class="form-control-label">{{__('client/company_profile.new_password')}}</label>
								<input type="password" name="password" class="form-control m-input" v-model="passData.password"  >
								<span class="m-form__help">{{__('client/company_profile.password_help')}}</span><br>
								<span v-if="hasErrors.password" class="invalid-feedback" role="alert">
                       <strong>@{{errorMessage.password}}</strong>
                          </span>
							</div>

						</div>
						<div class="modal-footer">
							<button type="reset" id="mdlcls" class="btn btn-secondary" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-info" @click.prevent="ClientPassBtn()" >Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!--end::Modal-->
@endsection


@section('ajax_script')
	<script src="{{asset('assets/vendors/custom/datatables/datatables.bundle.js')}}" type="text/javascript"></script>
	<script type="application/javascript">

        var ClientProf = new Vue({
            el: '#ProfileUpdate',
            data() {
                return {
                    updateData: {
                        id: '{{ $data['client']->id }}',
                        name: '{{$data['client']->name}}',
                        phone: '{{$data['client']->phone}}',
                        address: '{{$data['client']->address}}',
                        address2: '{{$data['client']->address2}}',
                        city: '{{$data['client']->city}}',
                        state: '{{$data['client']->state}}',
                        country: '{{$data['client']->country}}',
                        website: '{{$data['client']->website}}'
                    },
                    hasErrors: {
                        name: false,
                        phone: false,
                        address: false,
                        address2: false,
                        city: false,
                        state: false,
                        country: false,
                        website: false
                    },
                    errorMessage: {
                        name: null,
                        phone: null,
                        address: null,
                        address2: null,
                        city: null,
                        state: null,
                        country: null,
                        website: null
                    }
                    //passwordMatch:null
                }
            },
            methods: {
                ClientUpdateBtn() {
                    var _this = this;
                    var vm = this.hasErrors;
                    var _vm = this.errorMessage;
                    var id = _this.updateData.id;
                    axios.post('/admin/clientprofileupdate/' + id, _this.updateData)
                        .then(function (response) {
                            //console.log(response);
                            if (response.data.done == 1) {
                                toastr.success('Profile Updated Successfully');

                            } else {
                                toastr.error("Somthing wrong.");

                            }


                        }).catch(function (error) {

                            var errors = error.response.data.errors;

                        if (error.response.status == 422) {

                            var  resultArray = Object.keys(errors).map(function(key) {
                                return [Number(key), errors[key][0]];
                            });
                            toastr.error(resultArray[0][1]);
                        }
                    });
                }
            }

        });
        //===========================================================================================================

        var CompLogo = new Vue({
            el: '#logoCvr',
            data() {
                return {

                    id: '{{ $data['company']->id }}',
                    file: '{{ $data['company']->company_logo }}',
                    logoimg: '/storage/uploads/companylogos/{{ $data['company']->company_logo }}'

                }
            },
            methods: {
                logoBtn() {

                    this.file = this.$refs.file.files[0];
                    var _this = this;
                    //  var _vm = this.errorMessage;
                    var id = _this.id;
                    var smthng = '{{ __('client/company_profile.somthing_wrong') }}'

                    //var test = _this.logoData.files;
                    //alert(_this.logoData.img)
                    let formData = new FormData();
                    formData.append('file', this.file);

                    axios.post('/admin/companylogo/'+ id, formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then(function (response) {
                            if(response.data.done == 0){
                                toastr.error(smthng);

                            }else{
                                let flnm = response.data.done;

                                _this.logoimg = '/storage/uploads/companylogos/'+flnm;

                            }


                        }).catch(function (error) {
                        var errors = error.response
                        if (errors.status == 422) {
                            if (errors.data) {

                                if (errors.data.errors.password) {
                                    let err = errors.data.errors
                                    vm.password = true
                                    _vm.password = Array.isArray(err.password) ? err.password[0] : err.password
                                }
                            }
                        }
                    });
                }
            }

        });

        $(function () {
            $('#companies_logs_table').DataTable({
                processing: true,
                pageLength: 5,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: "{{ route('company_audit_logs', ['id' => $data['company']['id']]) }}",
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