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
	<div class="m-content">
		<div class="row">
			<div class="col-xl-3 col-lg-4">
				<div class="m-portlet m-portlet--full-height  ">
					<div class="m-portlet__body">
						<div class="m-card-profile">
							<div class="m-card-profile__title m--hide">
								Your Profile
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



								<span class="m-card-profile__name">{{ $data['client']->name }}</span>
								<a href="" class="m-card-profile__email m-link">{{ $data['client']->email }}</a>
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
										Company Profile
									</a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link " data-toggle="tab" href="#m_user_profile_tab_2"
									   role="tab" aria-selected="true">
										<i class="flaticon-share m--hide"></i>
										Update Profile
									</a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link" data-toggle="tab" href="#m_user_profile_tab_3"
									   role="tab" aria-selected="false">
										Messages
									</a>
								</li>
								<li class="nav-item m-tabs__item">
									<a class="nav-link m-tabs__link" data-toggle="tab" href="#m_user_profile_tab_4"
									   role="tab" aria-selected="false">
										Settings
									</a>
								</li>
							</ul>
						</div>
						{{--<div class="m-portlet__head-tools">--}}
							{{--<ul class="m-portlet__nav">--}}
								{{--<li class="m-portlet__nav-item m-portlet__nav-item--last">--}}
									{{--<div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push"--}}
										 {{--m-dropdown-toggle="hover" aria-expanded="true">--}}
										{{--<a href="#"--}}
										   {{--class="m-portlet__nav-link btn btn-lg btn-secondary  m-btn m-btn--icon m-btn--icon-only m-btn--pill  m-dropdown__toggle">--}}
											{{--<i class="la la-gear"></i>--}}
										{{--</a>--}}
										{{--<div class="m-dropdown__wrapper" style="z-index: 101;">--}}
                                            {{--<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"--}}
												  {{--style="left: auto; right: 21.5px;"></span>--}}
											{{--<div class="m-dropdown__inner">--}}
												{{--<div class="m-dropdown__body">--}}
													{{--<div class="m-dropdown__content">--}}
														{{--<ul class="m-nav">--}}
															{{--<li class="m-nav__section m-nav__section--first">--}}
																{{--<span class="m-nav__section-text">Quick Actions</span>--}}
															{{--</li>--}}
															{{--<li class="m-nav__item">--}}
																{{--<a href="" class="m-nav__link">--}}
																	{{--<i class="m-nav__link-icon flaticon-share"></i>--}}
																	{{--<span class="m-nav__link-text">Create Post</span>--}}
																{{--</a>--}}
															{{--</li>--}}
															{{--<li class="m-nav__item">--}}
																{{--<a href="" class="m-nav__link">--}}
																	{{--<i class="m-nav__link-icon flaticon-chat-1"></i>--}}
																	{{--<span class="m-nav__link-text">Send Messages</span>--}}
																{{--</a>--}}
															{{--</li>--}}
															{{--<li class="m-nav__item">--}}
																{{--<a href="" class="m-nav__link">--}}
																	{{--<i class="m-nav__link-icon flaticon-multimedia-2"></i>--}}
																	{{--<span class="m-nav__link-text">Upload File</span>--}}
																{{--</a>--}}
															{{--</li>--}}
															{{--<li class="m-nav__section">--}}
																{{--<span class="m-nav__section-text">Useful Links</span>--}}
															{{--</li>--}}
															{{--<li class="m-nav__item">--}}
																{{--<a href="" class="m-nav__link">--}}
																	{{--<i class="m-nav__link-icon flaticon-info"></i>--}}
																	{{--<span class="m-nav__link-text">FAQ</span>--}}
																{{--</a>--}}
															{{--</li>--}}
															{{--<li class="m-nav__item">--}}
																{{--<a href="" class="m-nav__link">--}}
																	{{--<i class="m-nav__link-icon flaticon-lifebuoy"></i>--}}
																	{{--<span class="m-nav__link-text">Support</span>--}}
																{{--</a>--}}
															{{--</li>--}}
															{{--<li class="m-nav__item" id="CompStatus">--}}

																{{--<a href="" @click.prevent="CompStatusbtn()" v-if="stval === '{{ config('db_const.user.status.deactive.value') }}'" class="m-nav__link"> <i class="m-nav__link-icon la la-toggle-off"></i>--}}
																	{{--<span class="m-nav__link-text">@{{dactv}}</span>--}}
																{{--</a>--}}
																{{--<a href="" @click.prevent="CompStatusbtn()" v-if="stval === '{{ config('db_const.user.status.active.value') }}'" class="m-nav__link"> <i class="m-nav__link-icon la la-toggle-on"></i>--}}
																	{{--<span class="m-nav__link-text">@{{actv}}</span>--}}
																{{--</a>--}}

																{{--<a href="" @click.prevent="CompStatusbtn()" class="m-nav__link"> <i class="m-nav__link-icon la la-toggle-on"></i>--}}
																{{--<span class="m-nav__link-text">@{{dactv}}</span>--}}
																{{--</a>--}}


																{{--@if($data['company']->status == config('db_const.user.status.active.value'))--}}
																{{--<a href="" @click.prevent="CompStatusbtn()" class="m-nav__link"> <i class="m-nav__link-icon la la-toggle-off"></i>--}}
																{{--<span class="m-nav__link-text" :id="stval" >@{{dactv}}</span>--}}
																{{--</a>--}}
																{{--@elseif($data['company']->status == config('db_const.user.status.deactive.value'))--}}
																{{--<a href="" @click.prevent="CompStatusbtn()" class="m-nav__link"> <i class="m-nav__link-icon la la-toggle-on"></i>--}}
																{{--<span class="m-nav__link-text" :id="stval" >@{{actv}}</span>--}}
																{{--</a>--}}
																{{--@endif--}}
															{{--</li>--}}
															{{--<li class="m-nav__separator m-nav__separator--fit m--hide">--}}
															{{--</li>--}}
															{{--<li class="m-nav__item m--hide">--}}
																{{--<a href="#"--}}
																   {{--class="btn btn-outline-danger m-btn m-btn--pill m-btn--wide btn-sm">Submit</a>--}}
															{{--</li>--}}
														{{--</ul>--}}
													{{--</div>--}}
												{{--</div>--}}
											{{--</div>--}}
										{{--</div>--}}
									{{--</div>--}}
								{{--</li>--}}
							{{--</ul>--}}
						{{--</div>--}}
					</div>
					<div class="tab-content">


						<div class="tab-pane active show" id="m_user_profile_tab_1" >
							<div id="CompanyProfile">
								<form  class="m-form m-form--fit m-form--label-align-right" method='post'>
									@csrf

									<div class="m-portlet__body">
										<div class="row">
											<div class="col-xl-10">
												<div class="m-form__section m-form__section--first">
													<div class="m-form__heading">
														<h3 class="m-form__heading-title">{{ __('client/company_profile.company_details') }}</h3>
													</div>
													<div class="form-group m-form__group row">
														<label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.company_name') }}</label>
														<div class="col-xl-9 col-lg-9" :class="{'has-error' : hasErrors.name}">
															<input type="text" name="name" class="form-control m-input"
																   v-model="updateData.name" >
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
															   title="{{__('client/company_profile.mailing_address_help')}}"></i>
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
															@click.prevent="MemUpdateBtn()">{{ __('client/company_profile.save') }}</button>
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
																   v-model="updateData.name" >
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
															{{__('client/company_profile.mailing_address')}}
															<i data-toggle="m-tooltip" data-width="auto"
															   class="m-form__heading-help-icon flaticon-info"
															   title="{{__('client/company_profile.mailing_address_help')}}"></i>
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
														<label class="col-xl-3 col-lg-3 col-form-label">{{ __('client/company_profile.address') }}</label>
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
												<div class="m-separator m-separator--dashed m-separator--lg"></div>
												<div class="m-form__section m-form__section--first">
													<div class="m-form__heading">
														<h3 class="m-form__heading-title">{{ __('client/company_profile.account_details') }}</h3>
													</div>

													<div class="form-group m-form__group row">
														<div class="col-lg-8 m-form__group-sub">
															<label class="form-control-label">{{ __('client/company_profile.username') }}</label>
															<input type="text" name="username" class="form-control m-input" value="{{ $data['client']->email }}" disabled>
														</div>
														<div class="col-lg-4 m-form__group-sub">

															{{--<label class="form-control-label">* Password:</label>--}}
															{{--<input type="password" name="password" class="form-control m-input"  >--}}
															{{--<span class="m-form__help">Please enter min 6 charcter</span>--}}

														</div>
													</div>
													<div class="form-group m-form__group row">

														<div class="col-lg-4 m-form__group-sub">

															<button type="button" class="btn btn-default" data-toggle="modal" data-target="#m_modal_5">{{ __('client/company_profile.password') }}</button>

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

						<div class="tab-pane" id="m_user_profile_tab_3">

						</div>
						<div class="tab-pane" id="m_user_profile_tab_4">

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
	<script type="application/javascript">

        var CompanyProf = new Vue({
            el: '#CompanyProfile',
            data() {
                return {
                    updateData: {
                        id: '{{ $data['company']->id }}',
                        name: '{{$data['company']->name}}',
                        phone: '{{$data['company']->contact_number}}',
                        address: '{{$data['company']->address}}',
                        city: '{{$data['company']->city}}',
                        state: '{{$data['company']->state}}',
                        country: '{{$data['company']->country}}'

                    },
                    hasErrors: {
                        name: false,
                        phone: false,
                        address: false,
                        address2: false,
                        city: false,
                        state: false,
                        country: false

                    },
                    errorMessage: {
                        name: null,
                        phone: null,
                        address: null,
                        address2: null,
                        city: null,
                        state: null,
                        country: null

                    }
                    //passwordMatch:null
                }
            },
            methods: {
                MemUpdateBtn() {
                    var _this = this;
                    var vm = this.hasErrors;
                    var _vm = this.errorMessage;
                    var id = _this.updateData.id;
                    axios.post('/client/companyprofileupdate/' + id, _this.updateData)
                        .then(function (response) {
                            //console.log(response);
                            if (response.data.done == 1) {
                                toastr.success('Profile Updated Successfully');

                            } else {
                                toastr.error("Somthing wrong.");

                            }


                        }).catch(function (error) {
                        var errors = error.response
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.name) {
                                    let err = errors.data.errors
                                    vm.name = true
                                    _vm.name = Array.isArray(err.name) ? err.name[0] : err.name
                                }


                                if (errors.data.errors.phone) {
                                    let err = errors.data.errors
                                    vm.phone = true
                                    _vm.phone = Array.isArray(err.phone) ? err.phone[0] : err.phone
                                }
                            }
                        }
                    });
                }
            }

        });
        //===========================================================================================================
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
                    axios.post('/client/clientprofileupdate/' + id, _this.updateData)
                        .then(function (response) {
                            //console.log(response);
                            if (response.data.done == 1) {
                                toastr.success('Profile Updated Successfully');

                            } else {
                                toastr.error("Somthing wrong.");

                            }


                        }).catch(function (error) {
                        var errors = error.response
                        if (errors.status == 422) {
                            if (errors.data) {
                                if (errors.data.errors.name) {
                                    let err = errors.data.errors
                                    vm.name = true
                                    _vm.name = Array.isArray(err.name) ? err.name[0] : err.name
                                }


                                if (errors.data.errors.phone) {
                                    let err = errors.data.errors
                                    vm.phone = true
                                    _vm.phone = Array.isArray(err.phone) ? err.phone[0] : err.phone
                                }
                            }
                        }
                    });
                }
            }

        });
        //================================================================================================================
        var ClientPass = new Vue({
            el: '#newpass',
            data() {
                return {
                    passData: {
                        id: '{{ $data['client']->id }}',
                        password: ''
                    },
                    hasErrors: {
                        password: false
                    },
                    errorMessage: {
                        password: null
                    }
                    //passwordMatch:null
                }
            },
            methods: {
                ClientPassBtn() {

                    var _this = this;
                    var vm = this.hasErrors;
                    var _vm = this.errorMessage;
                    var id = _this.passData.id;
                    axios.post('/client/memberupdatepass/' + id, _this.passData)
                        .then(function (response) {
                            //console.log(response);
                            if (response.data.done == 1) {
                                toastr.success('Password Updated Successfully');
                                window.location.reload();
                            } else {
                                toastr.error("Somthing wrong.");


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
        //============================================================================================================

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

                    axios.post('companylogo/'+ id, formData,
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

        //=======================================================================================
        var companySt = new Vue({
            el: '#CompStatus',
            data:{
                //stslabl: '',
                actv: '{{ config('db_const.user.status.active.label') }}',
                dactv: '{{ config('db_const.user.status.deactive.label') }}',
                stval : '{{$data['company']->status}}',

                activeval: '{{ config('db_const.user.status.active.value') }}',
                deactiveval: '{{ config('db_const.user.status.deactive.value') }}',
                active: '{{ config('db_const.user.status.active.label') }}',
                deactive: '{{ config('db_const.user.status.deactive.label') }}',

                txt : '',
                ttl : '',
                msg : ''
            },
            methods:{
                CompStatusbtn(){
                    let id = '{{$data['company']->id}}';
                    let _this = this;
                    let actv = _this.actv;
                    let dactv = _this.dactv;
                    let stid = _this.stval;

                    //alert(stid)



                    if (stid == _this.deactiveval){
                        txt = actv;
                        stid = _this.activeval;
                        ttl = '{{ __('client/company_profile.active_confirm.title') }}';
                        msg = '{{ __('client/company_profile.active_confirm.msg') }}';
                    }else if(stid == _this.activeval){_this.txt = dactv;
                        stid = _this.deactiveval;
                        txt = dactv;
                        ttl = '{{ __('client/company_profile.deactive_confirm.title') }}';
                        msg = '{{ __('client/company_profile.deactive_confirm.msg') }}';
                    }

                    //alert (th)
                    //alert (ttl)
                    swal({title:" "+ttl+" "+txt+".", text: " "+msg+".",
                        type:"warning",
                        showCancelButton:!0,
                        confirmButtonText:"Yes, "+txt+ " it!"
                    }).then(function(e){
                        if(e.value == true) {
                            axios.post('/client/companystatus/' + id + '/' + stid)
                                .then((response) => {

                                    if (response.data.status == _this.activeval ){
                                        let  rttl = '{{ __('client/company_profile.active_response.title') }}';
                                        let rmsg = '{{ __('client/company_profile.active_response.msg') }}';
                                        _this.stval = _this.activeval;
                                        _this.actv = _this.active;
                                        swal(rttl+"!", rmsg, "success")
                                    } else if (response.data.status == _this.deactiveval) {
                                        let  rttl = '{{ __('client/company_profile.deactive_response.title') }}';
                                        let rmsg = '{{ __('client/company_profile.deactive_response.msg') }}';
                                        _this.stval = _this.deactiveval;
                                        _this.dactv = _this.deactive;
                                        swal(rttl+" !", rmsg,"error")
                                    }

                                },(error) => {
                                    //console.log("Hi I'm Error   ");
                                    // error callback
                                })

                        }

                    }); //this is swal end ;

                }
            }

        })

	</script>
@endsection