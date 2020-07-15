<template>
    <div>
        <Checkout3DsModal calling_id="_3ds_verification" :booking_id="booking_id" :trigger="trigger_modal" source="guest_portal"></Checkout3DsModal>
        <div class="gp-page">
            <div class="gp-title">
                <h1 class="page-title">Welcome {{guest_portal.guest_name}}</h1>
                <p>Booking dates: <span class="text-muted">{{guest_portal.booking_dates}}</span></p>
            </div>

            <div class="gp-guest-app">
                <div class="gp-box-steps">
                    <div class="gp-property">
                        <div class="gp-property-img">
                            <img v-if="header.property_initial==''" :src="header.property_logo" width="80px">
                            <div v-else class="display-initials-wrapper s6">
                                <span class="initial_icon">
                                    {{header.property_initial}}
                                </span>
                            </div>
                        </div>
                        <div class="gp-property-legend">
                            <p class="mb-0">{{header.property_name}}</p>
                            <div class="gp-property-dl small">
                                <img v-if="header.booking_source_initial==''" :src="header.booking_source_logo" alt="">
                                <div v-else class="display-initials-wrapper">
                                    <span class="initial_icon">
                                        {{header.booking_source_initial}}
                                    </span>
                                </div>
                                <span>{{header.booking_source}}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;"
                     v-if="meta.need_to_update_card || meta.need_passport_scan || meta.need_credit_card_scan">
                    <div class="col-md">
                        <div class="m-alert m-alert--icon m-alert--outline alert alert-danger"
                             role="alert" v-if="meta.need_to_update_card && guest_portal.is_auto_payment_or_security_supported">
                            <div class="m-alert__icon">
                                <i class="la la-warning"></i>
                            </div>
                            <div class="m-alert__text">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Attention!</strong>
                                        Please update your card.
                                    </div>
                                    <div class="col-6 text-right">
                                        <a @click="opentab($event)" class="btn btn-danger btn-sm m-btn m-btn--pill m-btn--wide alert_link" data-id="#card_tab" data-toggle="tab"
                                           href="#card_tab"
                                           role="tab">Update Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="m-alert m-alert--icon m-alert--outline alert alert-danger"
                             id="showVerifyAlert" role="alert"
                             v-if="meta.need_guest_verification && meta.required_passport_scan && guest_portal.is_auto_payment_or_security_supported">
                            <div class="m-alert__icon">
                                <i class="la la-warning"></i>
                            </div>
                            <div class="m-alert__text">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Attention!</strong>
                                        Please upload your photo ID or relevant documentation for identity verification.
                                    </div>
                                    <div class="col-6 text-right">
                                        <a @click="opentab($event)" class="btn btn-danger btn-sm m-btn m-btn--pill m-btn--wide alert_link" data-id="#document_upload_tab"
                                           data-toggle="tab"
                                           href="#document_upload_tab"
                                           role="tab"> Upload Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="m-alert m-alert--icon m-alert--outline alert alert-danger"
                             id="showCreditCardUploadAlert"
                             role="alert" v-if="meta.need_credit_card_scan && meta.required_credit_card_scan && guest_portal.is_auto_payment_or_security_supported">
                            <div class="m-alert__icon">
                                <i class="la la-warning"></i>
                            </div>
                            <div class="m-alert__text">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Attention!</strong>
                                        Please upload your credit card in a way that only last 4 digits are visible.
                                    </div>
                                    <div class="col-6 text-right">
                                        <a @click="opentab($event)" class="btn btn-danger btn-sm m-btn m-btn--pill m-btn--wide alert_link" data-id="#document_upload_tab"
                                           data-toggle="tab"
                                           href="#document_upload_tab"
                                           role="tab"><i class="fas fa-upload"></i> Upload Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion guest-app-collapse" id="guest-info">

                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h4>
                                <a aria-controls="collapseOne" aria-expanded="true" class="collapsed collapsedv2"
                                   data-id="#m_tabs_7_1" data-target="#collapseOne" data-toggle="collapse">
                                    Booking Info<i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="headingOne" class="collapse m_tabs_7_1" data-parent="#guest-info"
                             id="collapseOne">
                            <div class="card-body">
                                <div class="col-12" v-if="guest_portal.show_map">
                                    <div class="mapouter">
                                        <div class="gmap_canvas">
                                            <iframe :src="'https://maps.google.com/maps?q='+guest_portal.map_query+'&amp;t=&amp;z=16&amp;ie=UTF8&amp;iwloc=&amp;output=embed'" frameborder="0" height="240"
                                                    id="gmap_canvas"
                                                    marginheight="0" marginwidth="0" scrolling="no"
                                                    width="100%"></iframe>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <dl class="gp-dl">
                                        <dt class="text-center">Address</dt>
                                        <dd class="text-center">{{guest_portal.address_1}}</dd>
                                        <dd class="text-center"><strong>{{guest_portal.address_2}}</strong></dd>
                                    </dl>
                                </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <dl class="gp-dl">
                                                <dt>Booking Status</dt>
                                                <dd>
                                                    <span class="text-success">{{guest_portal.booking_status}}</span>
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="col-6">
                                            <dl class="gp-dl">
                                                <dt>Booking No.</dt>
                                                <dd>#{{guest_portal.pms_booking_id}}</dd>
                                            </dl>
                                        </div>
                                        <div class="col-6">
                                            <dl class="gp-dl">
                                                <dt>Check-In Date</dt>
                                                <dd>
                                                    {{guest_portal.check_in}}
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="col-6">
                                            <dl class="gp-dl">
                                                <dt>Check-Out Date</dt>
                                                <dd>{{guest_portal.check_out}}</dd>
                                            </dl>
                                        </div>
                                        <div class="col-6"
                                             v-if="show_contact_info_form == false && meta.required_basic_info">
                                            <dl class="gp-dl">
                                                <dt>Email</dt>
                                                <dd>
                                                    {{guest_portal.email}}
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="col-6"
                                             v-if="show_contact_info_form == false && meta.required_basic_info">
                                            <dl class="gp-dl">
                                                <dt>Phone</dt>
                                                <dd>
                                                    {{guest_portal.phone}}
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="col-6"
                                             v-if="show_contact_info_form == false && meta.required_basic_info">
                                            <dl class="gp-dl">
                                                <dt>Arriving By</dt>
                                                <dd>
                                                    {{guest_portal.arriving_by}}
                                                    <span v-if="guest_portal.flight_no != ''"> <br> No. {{guest_portal.flight_no}}</span>
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="col-6"
                                             v-if="show_contact_info_form == false && meta.required_basic_info">
                                            <dl class="gp-dl">
                                                <dt>Arrival Time</dt>
                                                <dd>{{guest_portal.arrival_time}}</dd>
                                            </dl>
                                        </div>

                                        <div class="col-12"
                                             v-if="show_contact_info_form == false && meta.required_basic_info">
                                            <span @click="editContactInfo()" style="color: #322db9;cursor: pointer;">
                                                <i class="fas fa-edit"></i> Edit
                                            </span>
                                        </div>
                                    </div>

                                <div class="col-12" v-if="show_contact_info_form">
                                    <form>
                                        <div class="form-group m-form__group">
                                            <label>Email</label>
                                            <input class="form-control m-input" placeholder="Email"
                                                   type="text" v-model="guest_portal.basic_info.email">
                                            <small class="form-text text-error"
                                                   v-if="guest_portal.basic_info.error_status.email">{{guest_portal.basic_info.error_message.email}}</small>

                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group m-form__group">
                                                    <label>Phone</label>
                                                    <vue-tel-input style="width: 100%;"
                                                                   v-bind="bindProps"
                                                                   v-model="guest_portal.basic_info.phone"></vue-tel-input>
                                                    <small class="form-text text-error"
                                                           v-if="guest_portal.basic_info.error_status.phone">{{guest_portal.basic_info.error_message.phone}}</small>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group m-form__group">
                                                    <label>Arrival Time</label>
                                                    <select class="custom-select bg-light"
                                                            style="flex-grow:1;" v-model="guest_portal.basic_info.arrival_time">
                                                        <option selected value="">Select Time</option>
                                                        <option value="15:00">15:00</option>
                                                        <option value="15:30">15:30</option>
                                                        <option value="16:00">16:00</option>
                                                        <option value="16:30">16:30</option>
                                                        <option value="17:00">17:00</option>
                                                        <option value="17:30">17:30</option>
                                                        <option value="18:00">18:00</option>
                                                        <option value="18:30">18:30</option>
                                                        <option value="19:00">19:00</option>
                                                        <option value="19:30">19:30</option>
                                                        <option value="20:00">20:00</option>
                                                        <option value="20:30">20:30</option>
                                                        <option value="21:00">21:00</option>
                                                        <option value="21:30">21:30</option>
                                                        <option value="22:00">22:00</option>
                                                        <option value="22:30">22:30</option>
                                                        <option value="23:00">23:00</option>
                                                        <option value="23:30">23:30</option>
                                                        <option value="00:00">00:00</option>
                                                        <option value="00:30">00:30</option>
                                                        <option value="01:00">01:00</option>
                                                        <option value="01:30">01:30</option>
                                                        <option value="02:00">02:00</option>
                                                        <option value="02:30">02:30</option>
                                                        <option value="03:00">03:00</option>
                                                        <option value="03:30">03:30</option>
                                                        <option value="04:00">04:00</option>
                                                        <option value="04:30">04:30</option>
                                                        <option value="05:00">05:00</option>
                                                        <option value="05:30">05:30</option>
                                                        <option value="06:00">06:00</option>
                                                        <option value="06:30">06:30</option>
                                                        <option value="07:00">07:00</option>
                                                        <option value="07:30">07:30</option>
                                                        <option value="08:00">08:00</option>
                                                        <option value="08:30">08:30</option>
                                                        <option value="09:00">09:00</option>
                                                        <option value="09:30">09:30</option>
                                                        <option value="10:00">10:00</option>
                                                        <option value="10:30">10:30</option>
                                                        <option value="11:00">11:00</option>
                                                        <option value="11:30">11:30</option>
                                                        <option value="12:00">12:00</option>
                                                        <option value="12:30">12:30</option>
                                                        <option value="13:00">13:00</option>
                                                        <option value="13:30">13:30</option>
                                                        <option value="14:00">14:00</option>
                                                        <option value="14:30">14:30</option>
                                                    </select>
                                                    <small class="form-text text-error"
                                                           v-if="guest_portal.basic_info.error_status.arrival_time">{{guest_portal.basic_info.error_message.arrival_time}}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <button @click="cancelUpdateContactInfo()"
                                                class="btn btn-default" style="color: #333; background-color: #ccc; border-color: #ccc;"
                                                type="button">Cancel
                                        </button>
                                        <button @click.prevent="updateBasicInfo()" class="btn btn-primary btn-md fa-pull-right"
                                                type="button">Update
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Guide Book -->
                    <div class="card" v-if="guide_book_types.length > 0">
                        <div class="card-header" id="headingSix">
                            <h4>
                                <a aria-controls="collapseThree" aria-expanded="true" class="collapsed collapsedv2"
                                   data-id="#document_upload_tab" data-target="#collapseSix" data-toggle="collapse"
                                   id="property_guide_book_tab">
                                    Instructions <i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="headingSix" class="collapse property_guide_book_tab" data-parent="#guest-info"
                             id="collapseSix">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6" v-for="guide_book_type in guide_book_types">
                                        <a @click="getGuideBooks(guide_book_type)" class="icon-card"
                                           data-target="#property_guide_book_detail_model"
                                           data-toggle="modal">
                                            <div class="icon-card-icon">
                                                <i :class="guide_book_type.icon"></i>
                                            </div>
                                            <div class="icon-card-title">
                                                <h6>{{guide_book_type.title}}</h6>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End: Property Guide Book -->

                    <div class="card" v-if="guest_portal.is_auto_payment_or_security_supported">
                        <div class="card-header" id="headingTwo">
                            <h4 :class="meta.need_to_update_card ? 'alert-heading' : ''">
                                <a aria-controls="collapseTwo" aria-expanded="true" class="collapsed collapsedv2"
                                   data-id="#card_tab" data-target="#collapseTwo" data-toggle="collapse" id="card_tab">
                                    Payment Card<i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="headingTwo" class="collapse card_tab" data-parent="#guest-info"
                             id="collapseTwo">
                            <div class="card-body">
                                <div class="col-12">
                                    <div v-if="guest_portal.card_info.card_available">
                                        <div v-if="guest_portal.card_info.card_type != 'VC'">
                                            <div class="row">
                                                <div class="col-md-4 col-sm-12" v-if="show_update_card_form == false">
                                                    <dl class="gp-dl" v-if="guest_portal.card_info.cc_last_digit != ''">
                                                        <dt>Card Number</dt>
                                                        <dd>**** **** **** {{guest_portal.card_info.cc_last_digit}}</dd>
                                                    </dl>
                                                    <dl class="gp-dl" v-else>
                                                        <dt>Card Not attached</dt>
                                                    </dl>
                                                </div>
                                                <div class="col-md-4 col-sm-12" v-if="show_update_card_form == false">
                                                    <dl class="gp-dl" v-if="guest_portal.card_info.cc_last_digit != ''">
                                                        <dt>Expiry Date</dt>
                                                        <dd>{{guest_portal.card_info.expiry}}</dd>
                                                    </dl>
                                                </div>
                                                <div class="col-12" v-if="show_update_card_form == false">
                                                    <span @click="editUpdateCard()"
                                                          style="color: #322db9;cursor: pointer;">
                                                        <i class="fas fa-plus-circle"></i> Update card
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <div class="row">
                                                <div class="alert alert-info has-icon col-md-12">
                                                    <div class="alert-icon">
                                                        <i class="fas fa-exclamation-circle"></i>
                                                    </div>
                                                    <dl class="gp-dl">
                                                        <dt>Virtual Card</dt>
                                                    </dl>
                                                    <p class="mb-0" v-if="guest_portal.auth_info.security_auth">
                                                        {{guest_portal.auth_info.security_auth_alert}}
                                                    </p>
                                                </div>
                                                <div class="col-12" v-if="show_update_card_form == false">
                                                    <span @click="editUpdateCard()"
                                                          style="color: #322db9;cursor: pointer;">
                                                        <i class="fas fa-plus-circle"></i> Add Card
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <div class="row">
                                            <div class="alert alert-info has-icon col-md-12">
                                                <div class="alert-icon">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                </div>
                                                <dl class="gp-dl">
                                                    <dt>Card Not attached</dt>
                                                </dl>
                                                <p class="mb-0" v-if="guest_portal.auth_info.security_auth">
                                                    {{guest_portal.auth_info.security_auth_alert}}
                                                </p>
                                            </div>
                                            <div class="col-12" v-if="show_update_card_form == false">
                                                    <span @click="editUpdateCard()"
                                                          style="color: #322db9;cursor: pointer;">
                                                        <i class="fas fa-plus-circle"></i> Add Card
                                                    </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12" v-if="show_update_card_form">

                                        <component 
                                            :is="pgTerminal.cc_form_name" 
                                            :pgTerminal="pgTerminal" 
                                            ref="pgTerminal"/>
                                            
                                        <button @click="cancelUpdateCard()"
                                                class="btn btn-default" style="color: #333; background-color: #ccc; border-color: #ccc;" type="button">Cancel
                                        </button>
                                        <button @click.prevent="updateCard()" class="btn btn-success btn-confirm fa-pull-right"
                                                type="button">Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" v-if="guest_portal.is_security_deposit_supported && guest_portal.deposits.cc_auth_found">
                        <div class="card-header" id="headingForCCAuth">
                            <h4>
                                <a class="collapsed collapsedv2" data-toggle="collapse" data-target="#collapseForCCAuth" aria-expanded="true" aria-controls="collapseThree" data-id="#m_tabs_7_4">
                                    Credit Card Authorization<i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div class="collapse m_tabs_7_4" id="collapseForCCAuth" aria-labelledby="headingFour" data-parent="#guest-info">
                            <div class="card-body">
                                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <div class="row">
                                        <div class="col-12">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Amount</th>
                                                    <th scope="col">Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="deposit in guest_portal.deposits.cc_auth">
                                                    <td>{{deposit.date}}</td>
                                                    <td>{{deposit.amount}}</td>
                                                    <td><span class="badge" :class="deposit.status_class"><i :class="deposit.icon"></i> {{deposit.status}}</span></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" v-if="guest_portal.is_auto_payment_or_security_supported">
                        <div class="card-header" id="headingThree">
                            <h4>
                                <a aria-controls="collapseThree" aria-expanded="true" class="collapsed collapsedv2"
                                   data-id="#m_tabs_7_3" data-target="#collapseThree" data-toggle="collapse">
                                    Payment Details<i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="headingThree" class="collapse m_tabs_7_3" data-parent="#guest-info"
                             id="collapseThree">
                            <div class="card-body">
                                <div id="m_table_1_wrapper">
                                    <div class="row">
                                        <div class="col-12">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Amount</th>
                                                    <th scope="col">Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="payment in guest_portal.payments">
                                                    <td>{{payment.date}}</td>
                                                    <td>{{payment.amount}} <span v-if="payment.client_remarks !== ''">({{payment.client_remarks}})</span>
                                                    </td>
                                                    <td><span :class="payment.status_class" class="badge"><i
                                                            :class="payment.icon"></i> {{payment.status}}</span></td>
                                                </tr>
                                                <tr v-if="guest_portal.payments.length == 0">
                                                    <td colspan="3" class="text-center">No payment detail found.</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" v-if="guest_portal.is_security_deposit_supported && guest_portal.deposits.sd_auth_found">
                        <div class="card-header" id="headingFour">
                            <h4>
                                <a aria-controls="collapseThree" aria-expanded="true" class="collapsed collapsedv2"
                                   data-id="#m_tabs_7_4" data-target="#collapseFour" data-toggle="collapse">
                                    Security Deposit<i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="headingFour" class="collapse m_tabs_7_4" data-parent="#guest-info"
                             id="collapseFour">
                            <div class="card-body">
                                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <div class="row">
                                        <div class="col-12">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Date</th>
                                                    <th scope="col">Amount</th>
                                                    <th scope="col">Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="deposit in guest_portal.deposits.sd_auth">
                                                    <td>{{deposit.date}}</td>
                                                    <td>{{deposit.amount}}</td>
                                                    <td><span :class="deposit.status_class" class="badge"><i
                                                            :class="deposit.icon"></i> {{deposit.status}}</span></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" v-if="guest_portal.is_auto_payment_or_security_supported && (meta.required_passport_scan || meta.required_credit_card_scan)">
                        <div class="card-header" id="headingFive">
                            <h4 :class="meta.need_guest_verification ? 'alert-heading' : ''">
                                <a aria-controls="collapseThree" aria-expanded="true" class="collapsed collapsedv2"
                                   data-id="#document_upload_tab" data-target="#collapseFive" data-toggle="collapse"
                                   id="document_upload_tab">
                                    Document Upload<i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="headingFive" class="collapse document_upload_tab" data-parent="#guest-info"
                             id="collapseFive">
                            <div class="card-body">
                                <div class="m-portlet__body" id="guestImages_panel">
                                    <div class="row">
                                        <div class="col-12 ">
                                            <div class="tab-content">
                                                <div class="tab-pane active show" role="tabpanel">
                                                    <div class="Wrap">
                                                        <div class="row justify-content-between text-center">
                                                            <div :class="guest_images_status.credit_card == 1 && guest_images_status.passport != 1 ? 'col-lg-12 col-md-12' : 'col-lg-5 col-md-4'"
                                                                 v-if="meta.required_passport_scan && guest_images_status.passport != 1">
                                                                <div class="m-dropzone dropzone m-dropzone--primary dz-clickable UploadImages"
                                                                     id="m-dropzone-two"
                                                                     style="margin: 0px auto; margin-bottom: 20px;">
                                                                    <label for="Image">
                                                                        <div class="m-dropzone__msg dz-message needsclick"
                                                                             id="">
                                                                            <h3 class="m-dropzone__msg-title"
                                                                                style="text-align: center;min-width: 200px; min-height:132px">
                                                                                <img alt="Click Here To Upload Your Passport/Government ID"
                                                                                     src="/v2/img/id_card.png"
                                                                                     style="cursor:pointer;">
                                                                            </h3>
                                                                            <p style="text-align: center;font-weight: 600;">
                                                                                Passport/Government ID</p>
                                                                        </div>
                                                                    </label>
                                                                    <input
                                                                            @change="upload($event)"
                                                                            class="inputfile inputfile-4"
                                                                            data-notify-id="passport_uploaded"
                                                                            id="Image"
                                                                            name="passport"
                                                                            ref="passport_file"
                                                                            style="display: none;"
                                                                            type="file"
                                                                    />
                                                                </div>
                                                            </div>
                                                            <div :class="guest_images_status.passport == 1 && guest_images_status.credit_card != 1 ? 'col-lg-12 col-md-12' : 'col-lg-5 col-md-4'"
                                                                 v-if="meta.required_credit_card_scan && guest_images_status.credit_card != 1">
                                                                <div class="m-dropzone dropzone m-dropzone--primary dz-clickable UploadImages"
                                                                     id="m-dropzone-one"
                                                                     style="margin: 0px auto; margin-bottom: 20px;">
                                                                    <label for="credit_card_image">
                                                                        <div class="m-dropzone__msg dz-message needsclick">
                                                                            <h3 class="m-dropzone__msg-title"
                                                                                style="text-align: center;min-width: 200px; min-height:132px">
                                                                                <img alt="Click Here To Upload Your Credit Card"
                                                                                     src="/v2/img/credit_card_instructions.png"
                                                                                     style="cursor: pointer; margin: 13px 0 0 0;">
                                                                            </h3>
                                                                            <p style="text-align: center;font-weight: 600;">
                                                                                Credit Card</p>
                                                                        </div>
                                                                    </label>
                                                                    <input
                                                                            @change="upload($event)"
                                                                            class="inputfile inputfile-4"
                                                                            data-notify-id="credit_card_uploaded"
                                                                            id="credit_card_image"
                                                                            name="credit_card"
                                                                            ref="credit_card_file"
                                                                            style="display: none;"
                                                                            type="file"
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div v-if="guest_images_status.credit_card != 1 || guest_images_status.passport != 1">
                                                        <br>
                                                        <hr>
                                                        <br>
                                                    </div>
                                                    <div class="image-container">
                                                        <div class="img-block-div" v-for="image in guest_portal.images" v-if="['passport', 'credit_card'].includes(image.type)">

                                                            <div>
                                                                <div class="mt-1 mb-1 text-center">
                                                                    {{image.type | filter_image_label}}
                                                                </div>
                                                                <div class="row mb-2 text-center">
                                                                    <div class="col-12">
                                                                        <span v-if="image.status.display" :class="image.status.badge">{{image.status.text}}</span>
                                                                    </div>
                                                                    <!--<div class="col-6 pull-right" v-if="image.client_action">
                                                                        <span @click="deleteDocument({id: image.id, booking_id})" class="flaticon-close fas fa-trash" style="color: red; cursor: pointer; float: right;"></span>
                                                                    </div>-->
                                                                </div>
                                                            </div>

                                                            <!-- Open image in new window to have better view-->
                                                            <div class="guest-documents-outter-wrapper">
                                                                <a :href="image.image" target="_blank">
                                                                    <img :src="image.image" :alt="image.type">
                                                                </a>
                                                            </div>
                                                            <br>
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

                    <!-- Digital Signature-->
                    <div class="card" v-if="meta.guest_selfie || meta.signature_pad">
                        <div class="card-header" id="headingEight">
                            <h4>
                                <a aria-controls="collapseEight" aria-expanded="true" class="collapsed collapsedv2"
                                   data-id="#document_upload_tab" data-target="#collapseEight" data-toggle="collapse"
                                   id="digital_signature_tab">
                                    Digital Signature <i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="headingEight" class="collapse property_guide_book_tab" data-parent="#guest-info"
                             id="collapseEight">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <div class="image-container">
                                            <div class="img-block-div" v-for="image in guest_portal.images" v-if="['selfie', 'signature'].includes(image.type)">

                                                <div>
                                                    <div class="mt-1 mb-1 text-center">
                                                        {{image.type | filter_image_label}}
                                                    </div>
                                                    <div class="row mb-2 text-center">
                                                        <div class="col-12">
                                                            <span v-if="image.status.display" :class="image.status.badge">{{image.status.text}}</span>
                                                        </div>
                                                        <!--<div class="col-6 pull-right" v-if="image.client_action">
                                                            <span @click="deleteDocument({id: image.id, booking_id})" class="flaticon-close fas fa-trash" style="color: red; cursor: pointer; float: right;"></span>
                                                        </div>-->
                                                    </div>
                                                </div>

                                                <!-- Open image in new window to have better view-->
                                                <div class="guest-documents-outter-wrapper">
                                                    <a :href="image.image" target="_blank">
                                                        <img :src="image.image" :alt="image.type">
                                                    </a>
                                                </div>
                                                <br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End: Digital Signature-->

                    <!-- Add ons purchased -->
                    <div class="card" v-if="guest_portal.is_pg_active && meta.add_on_service && (Object.keys(add_on_service.data.purchased).length > 0 || Object.keys(add_on_service.data.available).length > 0)">
                        <div class="card-header" id="headingSeven">
                            <h4>
                                <a aria-controls="collapseSeven" aria-expanded="true" class="collapsed collapsedv2"
                                   data-id="#collapseSeven" data-target="#collapseSeven" data-toggle="collapse"
                                   id="upsell_purchased_tab">
                                    Add On Services <i class="fas fa-chevron-right"></i>
                                </a>
                            </h4>
                        </div>
                        <div aria-labelledby="headingSeven" class="collapse upsell_purchased_tab" data-parent="#guest-info"
                             id="collapseSeven">
                            <div class="card-body">
                                <div class="row" v-if="Object.keys(add_on_service.data.purchased).length > 0">
                                    <div class="col-12">
                                        <div class="mt-3 mb-4">
                                            <div class="card-section-title">
                                                <h4>Add-on Services Purchased<span class="badge badge-info">{{Object.keys(add_on_service.data.purchased).length}}</span></h4>
                                            </div>
                                            <div class="addon-item mt-4" v-for="(addon, i) in add_on_service.data.purchased"
                                                 :title="addon.per.label +' '+  addon.period.label +' '+addon.value + ' --- Payment Method : *****'+
                                     addon.payment_method.cc_last_4_digit + ' (' +addon.payment_method.cc_exp_month +'/'+addon.payment_method.cc_exp_year+')'" >
                                                <div class="addon-item-header">
                                                    <div class="custom-control custom-checkbox">
                                                        <i class="fw-500 fs-22 fas fa-check-circle" style="color: #1EAF24"></i>
                                                    </div>
                                                    <div class="addon-item-header-content">
                                                        <div class="addon-item-header-text">
                                                            <h4>{{addon.type}}</h4>
                                                            <p class="text-muted">{{addon.description}}</p>
                                                        </div>
                                                        <div class="addon-price">
                                                            <span class="text-success h6">{{addon.amount}} </span>
                                                            <br>
                                                            <span>Payment Method ****{{addon.payment_method.cc_last_4_digit}} ({{addon.payment_method.cc_exp_month}}/{{addon.payment_method.cc_exp_year}})</span>
                                                        </div>

                                                        <div class="addon-price">
                                                            <span class="text-success h5">{{addon.upsell_price}} </span>
                                                            <span>{{addon.per.label}}  {{addon.period.label}}</span>
                                                        </div>
                                                    </div>
                                                    <a :aria-controls="'#addonCollapse_'+addon.id" :href="'#addonCollapse_'+addon.id"
                                                       aria-expanded="false" class="link-overlay collapsed" data-toggle="collapse"
                                                       role="button">
                                                        <div class="addon-collapse-btn"><i class="fas fa-chevron-up"></i></div>
                                                    </a>
                                                </div>
                                                <div :id="'addonCollapse_'+addon.id" class="addon-body collapse">
                                                    <div class="addon-body-content">
                                                        <div class="addon-section-item" v-if="addon.is_time_set">
                                                            <div class="icon"><i class="fas fa-clock"></i></div>
                                                            <h6>Time Frame</h6>
                                                            {{addon.time_frame}}
                                                        </div>
                                                        <div :class="{'active': rule.isHighlighted}" class="addon-section-item"
                                                             v-for="rule in addon.rules">
                                                            <div class="icon"><i :class="rule.icon"></i></div>
                                                            <h6>{{rule.title}}</h6>
                                                            {{rule.description}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" v-if="Object.keys(add_on_service.data.available).length > 0">
                                    <div class="col-12">
                                        <div class="mt-3 mb-4">
                                            <div class="card-section-title">
                                                <h4>Add-on Services Available<span class="badge badge-info">{{Object.keys(add_on_service.data.available).length}}</span></h4>
                                            </div>
                                            <form>
                                                <div class="addon-item mt-4" v-for="(addon, index) in add_on_service.data.available">
                                                    <div class="addon-item-header">
                                                        <div class="custom-control custom-checkbox">
                                                            <input
                                                                    :data-price="addon.total_price"
                                                                    :id="'add_on_check_'+addon.id"
                                                                    class="custom-control-input"
                                                                    type="checkbox"
                                                                    :checked="addon.in_cart"
                                                                    @click="setIncartAmount({index, 'event': $event})"
                                                            />

                                                            <label :for="'add_on_check_'+addon.id" class="custom-control-label"></label>
                                                        </div>
                                                        <div class="addon-item-header-content">
                                                            <div class="addon-item-header-text">
                                                                <h4>{{addon.title}}</h4>
                                                                <p class="text-muted">{{addon.description}}</p>
                                                            </div>
                                                            <div class="addon-price guest-input" v-if="addon.show_guest_count">
                                                                <span class="text-muted">Person: </span>
                                                                <input type="number" min="1" class="form-control input-sm guest-input filter_number_input guest-portal-upsell-person-input" v-model="addon.guest_count" @input="modifyTotalPrice(index)">
                                                            </div>
                                                            <div class="addon-price"><span class="text-success h5">{{add_on_service.symbol}}{{addon.price}} </span><span>{{addon.period}}</span>
                                                            </div>
                                                        </div>
                                                        <a :aria-controls="'#addonCollapse_'+addon.id" :href="'#addonCollapse_'+addon.id"
                                                           aria-expanded="false" class="link-overlay collapsed" data-toggle="collapse"
                                                           role="button">
                                                            <div class="addon-collapse-btn"><i class="fas fa-chevron-up"></i></div>
                                                        </a>
                                                    </div>
                                                    <div :id="'addonCollapse_'+addon.id" class="addon-body collapse">
                                                        <div class="addon-body-content">
                                                            <div class="addon-section-item" v-if="addon.is_time_set">
                                                                <div class="icon"><i class="fas fa-clock"></i></div>
                                                                <h6>Time Frame</h6>{{addon.from_time}}{{addon.from_am_pm}} to
                                                                {{addon.to_time}}{{addon.to_am_pm}}
                                                            </div>
                                                            <div :class="{'active': rule.isHighlighted}" class="addon-section-item"
                                                                 v-for="rule in addon.rules">
                                                                <div class="icon"><i :class="rule.icon"></i></div>
                                                                <h6>{{rule.title}}</h6>
                                                                {{rule.description}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <div class="text-center" v-if="add_on_service.in_cart_due_amount > 0">
                                                <button @click.prevent="payAddonCharges()" class="btn btn-success btn-confirm footer-btn">Pay {{add_on_service.in_cart_due_amount > 0 ? add_on_service.symbol+add_on_service.in_cart_due_amount : ''}}
                                                    <i class="fas fa-arrow-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End: Add ons purchased -->

                </div>
            </div>
        </div>
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
        <property-guide-book-detail-model calling_id="property_guide_book_detail_model"></property-guide-book-detail-model>
        <button type="button" id="trigger_3ds_verification" data-target="#_3ds_verification" data-toggle="modal" style="display: none">hidden for 3ds verification modal</button>
        <button @click="triggerModal()" style="display: none">Modal Trigger</button>
    </div>
</template>
<script>

    import {mapActions, mapState, mapMutations} from 'vuex';
    import Checkout3DsModal from "../../../general/guest/reuseables/Checkout3DsModal";

    export default {
        props: ['booking_id'],
        mounted() {
            this.fetchGuestPortalData(this.booking_id);
            this.fetchAddOnServices(this.booking_id);
            this.openUpsellTab();

            jQuery.fn.ForceNumericOnly =
                function () {
                    return this.each(function () {
                        $(this).keydown(function (e) {
                            var key = e.charCode || e.keyCode || 0;
                            // allow backspace, tab, delete, enter, arrows, numbers and keypad numbers ONLY
                            // home, end, period, and numpad decimal
                            return (
                                key == 8 ||
                                key == 9 ||
                                key == 13 ||
                                key == 46 ||
                                key == 110 ||
                                key == 190 ||
                                (key >= 35 && key <= 40) ||
                                (key >= 48 && key <= 57) ||
                                (key >= 96 && key <= 105));
                        });
                    });
                };
            $(".filter_number_input").ForceNumericOnly();
        },
        components: {
            Checkout3DsModal
        },
        created() {
            this.redirectToTab();
        },
        data() {
            return {
                bindProps: {
                    autocomplete: "on",
                    autofocus: false,
                    defaultCountry: "",
                    disabled: false,
                    disabledFetchingCountry: false,
                    disabledFormatting: false,
                    dropdownOptions: {disabledDialCode: false, tabindex: 0},
                    dynamicPlaceholder: false,
                    enabledCountryCode: false,
                    enabledFlags: true,
                    ignoredCountries: [],
                    inputClasses: [],
                    inputOptions: {showDialCode: true, tabindex: 0},
                    maxLen: 18,
                    mode: "international",
                    name: "phone_input",
                    onlyCountries: [],
                    placeholder: "Enter Phone Number",
                    preferredCountries: [],
                    required: true,
                    validCharactersOnly: true,
                    wrapperClasses: [],
                },
                trigger_modal  : false,
                pgTerminal: {
                    cc_form_name: 'dummy-add-card', //'dummy-add-card',
                    is_token: false,
                    is_redirect: false,
                    redirect_link: '',
                    public_key: '',
                    client_secret: '',
                    account_id: '',
                    first_name: '',
                    last_name: '',
                    booking_id: '',
                    with3DsAuthentication: true,
                    show_authentication_button: false
                },
            }
        },
        methods: {
            ...mapMutations({
                'hide3dsModalBox': 'HIDE_3DS_MODAL_BOX_AT_GUEST_PORTAL'
            }),
            ...mapActions([
                'fetchGuestPortalData',
                'fetchAddCardTerminalData',
                'saveGuestPanelCardData',
                'saveBasicInfo',
                'deleteImage',
                'saveImageDocument',
                'fetchAddOnServices',
                'purchaseAddOnService',
                'setIncartAmount',
                'modifyTotalPrice'
            ]),
            redirectToTab() {

                if (location.hash !== '') {
                    setTimeout(function () {
                        let id = location.hash;
                        if($(id).length !== 0){
                            $(id)[0].click();
                            $([document.documentElement, document.body]).animate({
                                scrollTop: ($(id).offset().top) - 130
                            }, 1500);
                        }
                    }, 2000);
                }
            },
            opentab(e) {

                let id = e.target.dataset.id;
                window.location.hash = id;
                let class_list = $(id).attr('class').split(/\s+/);
                if (class_list.includes('collapsed')) {
                    $(id)[0].click();
                }

                $([document.documentElement, document.body]).animate({
                    scrollTop: ($(id).offset().top) - 130
                }, 1500);
            },
            updateCard() {
                
                let data = {
                    booking_info_id: this.booking_id,
                    card: this.guest_portal.card,
                    apply_validation: true,
                    requested_by: 'guest_portal'
                };
                
                let self = this;
                
                self.$store.commit('SHOW_LOADER', null, {root: true});
                
                this.$refs.pgTerminal.process().then(v => {
                     
                    if(v.status) {
                        
                        data.card.first_name = v.first_name;
                        data.card.last_name = v.last_name;
                        data.card.payment_method = v.token;
                        
                        this.saveGuestPanelCardData(data);
                        
                    } else {
                        toastr.error("Something went wrong. Try again.");
                        self.$store.commit('HIDE_LOADER', null, {root: true});
                    }
                     
                 }).catch(e => {
                    
                    toastr.error(e.message);
                    self.$store.commit('HIDE_LOADER', null, {root: true});
                     
                    if(e.code === 'page-reload') {
                        setTimeout(() => {
                            self.editUpdateCard();    
                         }, 3000);
                    }
                     
                 });

                
            },

            upsellSelected(e) {
                let price = parseFloat(e.target.dataset.price);
                if (e.target.checked) {
                    this.add_on_service.in_cart_due_amount += price;
                } else {
                    this.add_on_service.in_cart_due_amount -= price;
                }
            },

            payAddonCharges(){

                let data = {};
                let upsell_listing_ids = [];
                $.each(this.add_on_service.data.available, function (key, value) {
                    if(value.in_cart){
                        upsell_listing_ids.push({id: value.id, persons: value.guest_count, show_guest_count: value.show_guest_count});
                    }
                });
                data.upsell_listing_ids = upsell_listing_ids;
                data.booking_info_id = this.booking_id;
                data.amount_due = this.add_on_service.in_cart_due_amount;
                this.purchaseAddOnService(data);
            },

            updateBasicInfo() {

                let data = this.guest_portal.basic_info;
                data.booking_id = this.booking_id;

                this.saveBasicInfo(data);
            },
            cancelUpdateCard() {
                this.$store.commit('UPDATE_CARD_SHOW', false);
            },
            
            editUpdateCard() {
                
                let self = this;
                
                let data = {
                    booking_id: this.booking_id
                };
                
                this.fetchAddCardTerminalData(data).then(v => {
                    
                    self.pgTerminal = v;
                    self.$store.commit('UPDATE_CARD_SHOW', true);
                    
                }).catch(e => {
                    toastr.error(e);
                });

            },
            editContactInfo() {
                this.$store.commit('UPDATE_CONTACT_FORM_SHOW', true);
            },
            cancelUpdateContactInfo() {
                this.$store.commit('UPDATE_CONTACT_FORM_SHOW', false);
            },
            upload(e) {

                let image = this.$refs[e.target.name + '_file'].files[0];
                let is_invalid = this.imageValidator(image);

                if (!is_invalid) {
                    let data = new FormData();
                    data.append('file', image);
                    data.append('name', e.target.name);
                    data.append('alert_type', e.target.dataset.notifyId);
                    data.append('booking_id', this.booking_id);
                    data.append('requested_by', 'guest_portal');

                    this.saveImageDocument(data);
                }
            },
            imageValidator(file) {

                var errorFlag = false;
                var types = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!types.includes(file.type)) {
                    toastr.error("Image type must be 'JPG', 'PNG', 'JPEG'.");
                    errorFlag = true;
                }
                if (file.size > 5000000) {
                    toastr.error("Image size must be less than 5 MB.");
                    errorFlag = true;
                }

                return errorFlag;
            },
            deleteImg(id) {

                let data = {
                    id,
                    booking_id: this.booking_id
                };
                this.deleteImage(data);
            },
            getGuideBooks(guide_book_type) {
                this.$store.commit('GUIDE_BOOK_TYPE', guide_book_type);
            },
            triggerModal(){
                this.trigger_modal = true;
                $('#trigger_3ds_verification').click();
            },
            openUpsellTab() {
                var upsell_purchased = '';
                var url = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                    if (key == 'upsell-purchased' && value == 'upsell') {
                        setTimeout(function(){
                            document.getElementById("headingSeven").scrollIntoView();
                            document.getElementById("upsell_purchased_tab").setAttribute("aria-expanded", true);
                            document.getElementById("collapseSeven").classList.add("show");
                        }, 4000);
                    }
                });
            },
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                guest_portal: (state) => {
                    return state.guest_portal;
                },
                meta: (state) => {
                    return state.guest_portal.meta;
                },
                show_contact_info_form: (state) => {
                    return state.guest_portal.show_contact_info_form;
                },
                show_update_card_form: (state) => {
                    return state.guest_portal.show_update_card_form;
                },
                header: (state) => {
                    return state.guest_portal.header;
                },
                guide_book_types: (state) => {
                    return state.guest_portal.guide_book_types;
                },
                upsells: (state) => {
                    return state.guest_portal.upsells;
                },
                add_on_service: (state) => {
                    return state.pre_checkin.add_on_service;
                },
                in_cart_upsells: (state) => {
                    return state.pre_checkin.in_cart_upsells;
                },
                guest_images_status: (state) => {
                    return state.guest_portal.guest_images_status;
                }
            })
        },
        watch: {
            guest_portal: {
                deep: true,
                handler(new_value, old_value){
                    if(new_value._3ds_modal){
                        this.triggerModal();
                    }
                    this.hide3dsModalBox();
                }
            }
        },
        filters: {
            filter_image_label: function (value) {
                let trimmed = value.replace("_", " ");
                return trimmed.charAt(0).toUpperCase() + trimmed.slice(1);
            }
        },
    }
</script>
<style scoped>
    .gp-box-steps {
        background: #fff;
        border-radius: 4px 4px 4px 4px;
        box-shadow: 0 1px 2px -1px rgba(0, 0, 0, 0.12), 0 2px 8px 0 rgba(0, 0, 0, 0.06);
    }

    .gp-page {
        margin-bottom: 100px;
    }

    .footer-btn{
        width: 190px;
        line-height: 2rem;
    }

    .guest-input{
        z-index: 2;
    }
</style>
