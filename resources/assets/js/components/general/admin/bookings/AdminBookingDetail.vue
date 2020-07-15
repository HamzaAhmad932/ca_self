<template>
    <div>
        <!----Modals Components----------->
        <!-------End Modal Components------>

        <!-- Booking List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">Booking Detail</h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item m-nav__item--home">
                            <a class="m-nav__link m-nav__link--icon" href="dashboard">
                                <i class="m-nav__link-icon la la-home"></i>Dashboard
                            </a>
                        </li>
                        <li class="m-nav__separator">-</li>
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Booking Detail </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- END: Subheader -->
        <div class="m-content">
            <div class="m-portlet m-portlet--mobile">

                <!-- Booking Detail -->
                <div class="m-portlet__head" style="border-bottom: none;">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h4>Booking Info</h4>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row">
                                <div class="col-md-1"><strong>More</strong></div>
                                <div class="col-md-2"><strong>Booking Number</strong></div>
                                <div class="col-md-3"><strong>User Account</strong></div>
                                <div class="col-md-3"><strong>Property</strong></div>
                                <div class="col-md-2"><strong>Room</strong></div>
                                <div class="col-md-1"><strong>Status</strong></div>
                            </div>
                            <div class="panel-group">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="row"
                                             style="border-top: 1px solid #ebedf2; margin-top: 2rem; padding-top: 2rem;">
                                            <div class="col-md-1">
                                                <a :aria-controls="'id_'+booking_details.id"
                                                   :href="'#id_'+booking_details.id"
                                                   :id="booking_details.id"
                                                   aria-expanded="false"
                                                   class="card-collapse collapsed"
                                                   data-open="true"
                                                   data-toggle="collapse"
                                                   role="button">
                                                    <i :id="booking_details.id" class="fas fa-chevron-down"
                                                       data-open="true"
                                                       style="display: block; height: 100%; width: 100%;"></i>
                                                </a>
                                            </div>
                                            <div class="col-md-2">{{booking_details.pms_booking_id}}</div>
                                            <div class="col-md-3">
                                                <strong>ID: </strong>{{booking_details.user_account_id}}<br />
                                                <strong>Name: </strong>{{booking_details.user_account.name}}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>ID: </strong>{{booking_details.property_info.id}}<br />
                                                <strong>PMS ID: </strong>{{booking_details.property_id}}<br />
                                                <strong>Name: </strong>{{booking_details.property_info.name}}<br />
                                            </div>
                                            <div class="col-md-2">
                                                <strong>ID: </strong>{{booking_details.room ? booking_details.room.id : '--'}}<br />
                                                <strong>PMS ID: </strong>{{booking_details.room ? booking_details.room.pms_room_id : '--'}}<br />
                                                <strong>Name: </strong>{{booking_details.room ? booking_details.room.name : '--'}}<br />
                                            </div>
                                            <div class="col-md-1">{{booking_details.booking_status}}</div>
                                        </div>
                                    </div>

                                    <div :id="'id_'+booking_details.id" class="panel-collapse collapse">
                                        <div class="panel-body open-div">
                                            <div class="row open-div-row">
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Property Info ID</strong>
                                                    <br/>{{booking_details.property_info.id}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Check In</strong>
                                                    <br/>{{booking_details.check_in.month}}
                                                    {{booking_details.check_in.day}}, {{booking_details.check_in.year}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Check Out</strong>
                                                    <br/>{{booking_details.check_out.month}}
                                                    {{booking_details.check_out.day}},
                                                    {{booking_details.check_out.year}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Security Amount</strong>
                                                    <br/>{{booking_details.deposit}}<br/><span :class="booking_details.deposit_status.class"
                                                                                               class="small">{{booking_details.deposit_status.deposit_status}}</span>
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Amount</strong>
                                                    <br/>{{booking_details.amount}}<br/><span :class="booking_details.payment_status.class"
                                                                                              class="badge">{{booking_details.payment_status.status}}</span>
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Booking Date</strong>
                                                    <br/>{{booking_details.booking_time}}
                                                </div>
                                            </div>
                                            <div class="row open-div-row">
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Guest</strong>
                                                    <br/><strong>Name:</strong> {{ booking_details.guest_name }}
                                                    <br/><strong>Phone:</strong> {{ booking_details.guest_phone }}
                                                    <br/><strong>Email:</strong> {{booking_details.guest_email}}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Arrival Time</strong>
                                                    <br/>{{ booking_details.arrival_time }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">No. of Guests</strong>
                                                    <br/>{{ booking_details.guests }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Last Portal Visit</strong>
                                                    <br/>{{ booking_details.last_seen_of_guest }}
                                                </div>
                                                <div class="col-md-2">
                                                    <strong class="m--margin-bottom-20">Payment Type</strong>
                                                    <br/>{{ booking_details.payment_type }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
                </div>
                <!-- END: Booking Detail -->

                <!-- Credit Card Detail -->
                <div class="m-portlet__head" style="border-bottom: none;">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h4>Credit Card Info</h4>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped- table-bordered table-hover table-checkable">
                                        <thead>
                                        <tr>
                                            <th>Detail</th>
                                            <th>Booking Info ID</th>
                                            <th>User Account ID</th>
                                            <th>Is VC</th>
                                            <th>Card Name</th>
                                            <th>Name</th>
                                            <th>CC Last 4 Digits</th>
                                            <th>CC Exp Date</th>
                                            <th>System Usage</th>
                                            <th>Customer Object</th>
                                            <th>Auth Token</th>
                                            <th>Status</th>
                                            <th>Attempts</th>
                                            <th>Error Message</th>
                                            <th>Due Date</th>
                                            <th>Created At</th>
                                            <th>Country</th>
                                            <th>Is 3DS</th>
                                            <th>Type</th>
                                            <th>Is Default</th>
                                        </tr>
                                        </thead>
                                        <tbody v-for="cc_info in booking_details.cc_infos">
                                        <tr class="parent">
                                            <td><i @click="displayDetails" class="fa fa-chevron-down"></i></td>
                                            <td>{{cc_info.booking_info_id}}</td>
                                            <td>{{cc_info.user_account_id}}</td>
                                            <td>{{cc_info.is_vc}}</td>
                                            <td>{{cc_info.card_name}}</td>
                                            <td>{{cc_info.f_name}} {{cc_info.l_name}}</td>
                                            <td>{{cc_info.cc_last_4_digit}}</td>
                                            <td>{{cc_info.cc_exp_month}}-{{cc_info.cc_exp_year}}</td>
                                            <td><pre>{{cc_info.system_usage}}</pre></td>
                                            <td v-html="cc_info.customer_object_data"></td>
                                            <td>{{cc_info.auth_token}}</td>
                                            <td>{{cc_info.status | creditCardStatus}}</td>
                                            <td>Total Attempts: {{cc_info.attempts}}</td>
                                            <td>{{cc_info.error_message}}</td>
                                            <td>{{cc_info.due_date}}</td>
                                            <td>{{cc_info.created_at}}</td>
                                            <td>{{cc_info.country}}</td>
                                            <td>{{cc_info.is_3ds | is_3ds}}</td>
                                            <td>{{cc_info.type | creditCardType}}</td>
                                            <td>{{cc_info.is_default | isDefault}}</td>
                                        </tr>
                                        <tr class="cchild">
                                            <td colspan="20">Credit Card Info Audit</td>
                                        </tr>
                                        <tr class="cchild" v-for="cc_info_audit in cc_info.credit_card_info_audits">
                                            <td colspan="20">
                                                <div class="row">
                                                    <div class="col-md-2"><strong>Event</strong> <br/><br/>{{cc_info_audit.event}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Auditable Type</strong> <br/><br/>{{cc_info_audit.auditable_type}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Auditable ID</strong> <br/><br/>{{cc_info_audit.auditable_id}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Old Values</strong> <br/><br/>{{cc_info_audit.old_values}}
                                                    </div>
                                                    <div class="col-md-2"><strong>New Values</strong> <br/><br/>{{cc_info_audit.new_values}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Created At</strong> <br/><br/>{{cc_info_audit.created_at}}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cchild" v-if="cc_info.credit_card_info_audits.length == 0">
                                            <td class="text-center" colspan="20">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tbody v-if="booking_details.cc_infos.length == 0">
                                        <tr>
                                            <td class="text-center" colspan="20">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
                </div>
                <!-- END: Credit Card Detail -->

                <!-- Credit Card Authorization Detail -->
                <div class="m-portlet__head" style="border-bottom: none;">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h4>Credit Card Authorization</h4>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped- table-bordered table-hover table-checkable">
                                        <thead>
                                        <tr>
                                            <th>Detail</th>
                                            <th>Booking Info ID</th>
                                            <th>CC Info ID</th>
                                            <th>User Account ID</th>
                                            <th>Attempts</th>
                                            <th>Attempts For 500</th>
                                            <th>Hold Amount</th>
                                            <th>Token</th>
                                            <th>Transaction Obj</th>
                                            <th>Is Auto Re-auth</th>
                                            <th>Type</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Captured</th>
                                            <th>Decline Email Sent</th>
                                            <th>Remarks</th>
                                            <th>Created At</th>
                                            <th>Payment Intent ID</th>
                                            <th>In Processing</th>
                                            <th>Manually Released</th>
                                        </tr>
                                        </thead>
                                        <tbody v-for="cc_authorization in booking_details.credit_card_authorization">
                                        <tr class="parent">
                                            <td><i @click="displayDetails" class="fa fa-chevron-down"></i></td>
                                            <td>{{cc_authorization.booking_info_id}}</td>
                                            <td>{{cc_authorization.cc_info_id}}</td>
                                            <td>{{cc_authorization.user_account_id}}</td>
                                            <td>{{cc_authorization.attempts}}</td>
                                            <td>{{cc_authorization.attempts_for_500}}</td>
                                            <td>{{cc_authorization.hold_amount}}</td>
                                            <td>{{cc_authorization.token}}</td>
                                            <td v-html="cc_authorization.transaction_obj_data"></td>
                                            <td>{{cc_authorization.is_auto_re_auth}}</td>
                                            <td>{{cc_authorization.type}}</td>
                                            <td>{{cc_authorization.due_date}}</td>
                                            <td>{{cc_authorization.status.authorization_status}}</td>
                                            <td>{{cc_authorization.captured}}</td>
                                            <td>{{cc_authorization.decline_email_sent}}</td>
                                            <td>{{cc_authorization.remarks}}</td>
                                            <td>{{cc_authorization.created_at}}</td>
                                            <td>{{cc_authorization.payment_intent_id}}</td>
                                            <td>{{cc_authorization.in_processing}}</td>
                                            <td>{{cc_authorization.manually_released}}</td>
                                        </tr>
                                        <tr class="cchild">
                                            <td colspan="20">Authorization Detail</td>
                                        </tr>
                                        <tr class="cchild" v-for="detail in cc_authorization.authorization_details">
                                            <td colspan="20">
                                                <div class="row">
                                                    <div class="col-md-1"><strong>Name</strong> <br/><br/>{{detail.name}}
                                                    </div>
                                                    <div class="col-md-1"><strong>CC Info ID</strong> <br/><br/>{{cc_authorization.cc_info_id}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Payment Processor Response</strong>
                                                        <br/><br/><span
                                                                v-html="detail.payment_processor_response_object"></span>
                                                    </div>
                                                    <div class="col-md-1"><strong>CC Auth ID</strong> <br/><br/>{{detail.cc_auth_id}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Payment Gateway Form ID</strong> <br/><br/>{{detail.payment_gateway_form_id}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Payment Gateway Name</strong>
                                                        <br/><br/>{{detail.payment_gateway_name}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Amount</strong> <br/><br/>{{detail.amount}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Payment Status</strong> <br/><br/>{{detail.payment_status}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Charge Ref No</strong> <br/><br/>{{detail.charge_ref_no}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Client Remarks</strong> <br/><br/>{{detail.client_remarks}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Error Message</strong> <br/><br/>{{detail.error_msg}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Order ID</strong> <br/><br/>{{detail.order_id}}
                                                    </div>
                                                    <div class="col-md-1"><strong>Created At</strong> <br/><br/>{{detail.created_at}}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cchild" v-if="cc_authorization.authorization_details.length == 0">
                                            <td class="text-center" colspan="20">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tbody v-if="booking_details.credit_card_authorization.length == 0">
                                        <tr>
                                            <td class="text-center" colspan="20">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
                </div>
                <!-- END: Credit Card Authorization Detail -->

                <!-- Transaction Detail -->
                <div class="m-portlet__head" style="border-bottom: none;">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h4>Transaction Init</h4>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body" id="b-list">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped- table-bordered table-hover table-checkable">
                                        <thead>
                                        <tr>
                                            <th>Detail</th>
                                            <th>Transaction Init ID</th>
                                            <th>Due Date</th>
                                            <th>Next Attempt Time</th>
                                            <th>Price</th>
                                            <th>Payment Status</th>
                                            <th>Charge Ref No</th>
                                            <th>Transaction Type</th>
                                            <th>Attempts</th>
                                            <th>Client Remarks</th>
                                            <th>Error Code Id</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                        </tr>
                                        </thead>
                                        <tbody v-for="transaction_init in booking_details.transaction_init">
                                        <tr class="parent">
                                            <td><i @click="displayDetails" class="fa fa-chevron-down"></i></td>
                                            <td>{{ transaction_init.due_date | formatDateTime }}</td>
                                            <td>{{ transaction_init.id }}</td>
                                            <td>{{ transaction_init.next_attempt_time | formatDateTime }}</td>
                                            <td>{{ transaction_init.price }}</td>
                                            <td>{{ transaction_init.payment_status }}</td>
                                            <td>{{transaction_init.charge_ref_no}}</td>
                                            <td>{{ transaction_init.transaction_type | transactionType}}</td>
                                            <td>Total Attempts: {{ transaction_init.attempt }}</td>
                                            <td>{{ transaction_init.client_remarks }}</td>
                                            <td>{{ transaction_init.error_code_id }}</td>
                                            <td>{{transaction_init.status | transactionStatus}}</td>
                                            <td>{{ transaction_init.created_at | formatDateTime }}</td>
                                        </tr>
                                        <tr class="cchild">
                                            <td colspan="13">Transaction Init Detail</td>
                                        </tr>
                                        <tr class="cchild"
                                            v-for="transactions_detail in transaction_init.transactions_detail">
                                            <td colspan="13">
                                                <div class="row">
                                                    <div class="col-md-2"><strong>Transaction Init ID</strong> <br/>{{transactions_detail.transaction_init_id}}
                                                    </div>
                                                    <div class="col-md-2"><strong>CC Info ID</strong> <br/>{{transactions_detail.cc_info_id}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Name</strong> <br/>{{transactions_detail.name}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Payment Gateway Name</strong> <br/>{{transactions_detail.payment_gateway_name}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Payment Status</strong> <br/>{{transactions_detail.payment_status}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Charge Ref No</strong> <br/>{{transactions_detail.charge_ref_no}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Client Remarks</strong> <br/>{{transactions_detail.client_remarks}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Error Message</strong> <br/>{{transactions_detail.error_msg}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Order ID</strong> <br/>{{transactions_detail.order_id}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Created At</strong> <br/>{{transactions_detail.created_at}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Updated At</strong> <br/>{{transactions_detail.updated_at}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Amount</strong> <br/>{{transactions_detail.amount}}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cchild" v-if="transaction_init.transactions_detail.length == 0">
                                            <td class="text-center" colspan="13">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tbody v-if="booking_details.transaction_init.length == 0">
                                        <tr>
                                            <td class="text-center" colspan="13">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
                </div>
                <!-- END: Transaction Detail -->

                <!-- Property Info Detail -->
                <div class="m-portlet__head" style="border-bottom: none;">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h4>Property Info</h4>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped- table-bordered table-hover table-checkable">
                                        <thead>
                                        <tr>
                                            <th>Detail</th>
                                            <th>Name</th>
                                            <th>Address</th>
                                            <th>Property Key</th>
                                            <th>Currency Code</th>
                                            <th>Time Zone</th>
                                            <th>Booking Source Setting</th>
                                            <th>Payment Gateway Setting</th>
                                            <th>Last Sync</th>
                                            <th>Available On PMS</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                        </tr>
                                        </thead>
                                        <tbody v-if="Object.keys(booking_details.property_info).length > 0">
                                        <tr class="parent">
                                            <td><i @click="displayDetails" class="fa fa-chevron-down"></i></td>
                                            <td>{{ booking_details.property_info.name }}</td>
                                            <td>{{ booking_details.property_info.address }}<br/><strong>City: </strong>{{
                                                booking_details.property_info.city }}<br/><strong>Country: </strong>{{
                                                booking_details.property_info.country }}
                                            </td>
                                            <td>{{ booking_details.property_info.property_key }}</td>
                                            <td>{{ booking_details.property_info.currency_code }}</td>
                                            <td>{{booking_details.property_info.time_zone }}</td>
                                            <td>{{ booking_details.property_info.use_bs_settings }}</td>
                                            <td>{{ booking_details.property_info.use_pg_settings }}</td>
                                            <td>{{ booking_details.property_info.last_sync | formatDateTime }}</td>
                                            <td>{{ booking_details.property_info.available_on_pms | availabilityOnPMS
                                                }}
                                            </td>
                                            <td>{{booking_details.property_info.status | propertyStatus }}</td>
                                            <td>{{ booking_details.property_info.created_at | formatDateTime }}</td>
                                        </tr>
                                        <tr class="cchild">
                                            <td colspan="12">Property Info Audits</td>
                                        </tr>
                                        <tr class="cchild"
                                            v-for="property_info_audits in booking_details.property_info.property_info_audits">
                                            <td colspan="12">
                                                <div class="row">
                                                    <div class="col-md-2"><strong>Event</strong> <br/><br/>{{property_info_audits.event}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Auditable Type</strong> <br/><br/>{{property_info_audits.auditable_type}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Auditable ID</strong> <br/><br/>{{property_info_audits.auditable_id}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Old Values</strong> <br/><br/>{{property_info_audits.old_values}}
                                                    </div>
                                                    <div class="col-md-2"><strong>New Values</strong> <br/><br/>{{property_info_audits.new_values}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Created At</strong> <br/><br/>{{property_info_audits.created_at}}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cchild"
                                            v-if="booking_details.property_info.property_info_audits.length == 0">
                                            <td class="text-center" colspan="12">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tbody v-if="Object.keys(booking_details.property_info).length == 0">
                                        <tr>
                                            <td class="text-center" colspan="12">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
                </div>
                <!-- END: Property Info Detail -->

                <!-- Payment Gateway Detail -->
                <div class="m-portlet__head" style="border-bottom: none;">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h4>User Payment Gateway</h4>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <!--begin: table -->
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped- table-bordered table-hover table-checkable">
                                        <thead>
                                        <tr>
                                            <th>Detail</th>
                                            <th>Payment Gateway Form ID</th>
                                            <th>Payment Gateway Name</th>
                                            <th>User Account ID</th>
                                            <th>Is Verified</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                        </tr>
                                        </thead>
                                        <tbody v-if="Object.keys(booking_details.payment_gateway).length > 0">
                                        <tr class="parent">
                                            <td><i @click="displayDetails" class="fa fa-chevron-down"></i></td>
                                            <td>{{ booking_details.payment_gateway.payment_gateway_form_id }}</td>
                                            <td>{{ booking_details.payment_gateway.payment_gateway_form_name }}</td>
                                            <td>{{ booking_details.payment_gateway.user_account_id }}</td>
                                            <td>{{ booking_details.payment_gateway.is_verified |
                                                paymentGatewayIsVerified }}
                                            </td>
                                            <td>{{booking_details.payment_gateway.status | paymentGatewayStatus }}</td>
                                            <td>{{ booking_details.payment_gateway.created_at | formatDateTime }}</td>
                                        </tr>
                                        <tr class="cchild">
                                            <td colspan="7">User Payment Gateway Audits</td>
                                        </tr>
                                        <tr class="cchild"
                                            v-for="payment_gateway_detail in booking_details.payment_gateway.payment_gateway_detail">
                                            <td colspan="7">
                                                <div class="row">
                                                    <div class="col-md-2"><strong>Event</strong> <br/><br/>{{payment_gateway_detail.event}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Auditable Type</strong> <br/><br/>{{payment_gateway_detail.auditable_type}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Auditable ID</strong> <br/><br/>{{payment_gateway_detail.auditable_id}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Old Values</strong> <br/><br/>{{payment_gateway_detail.old_values}}
                                                    </div>
                                                    <div class="col-md-2"><strong>New Values</strong> <br/><br/>{{payment_gateway_detail.new_values}}
                                                    </div>
                                                    <div class="col-md-2"><strong>Created At</strong> <br/><br/>{{payment_gateway_detail.created_at}}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="cchild"
                                            v-if="booking_details.payment_gateway.payment_gateway_detail.length == 0">
                                            <td class="text-center" colspan="7">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tbody v-if="Object.keys(booking_details.payment_gateway).length == 0">
                                        <tr>
                                            <td class="text-center" colspan="7">
                                                Record not found
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end: table -->
                </div>
                <!-- END: Payment Gateway Detail -->

            </div>
        </div>
        <!-- End Booking List-->

        <!-- Block UI Loader-->
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
        <!-- End Block UI-->

    </div>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        props: ['booking_info_id'],

        data() {
            return {
                //
            }
        },
        methods: {
            getAdminBookingDetail() {
                this.$store.dispatch('fetchAdminBookingDetail', this.booking_info_id);
            },
            displayDetails(event) {
                console.log(event.target.closest('tbody'));
                event.target.closest('tbody').classList.toggle("open");
            }
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.replace(/([A-Z])/g, ' $1').trim();
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1)
            },
            shortName: function (value) {
                if (!value) {
                    return '';
                } else {
                    //return value.length;
                    if (value.length > 70) {
                        return value.substring(0, 70) + '...';
                    } else {
                        return value
                    }

                }
            },
            creditCardType: function (value) {

                if (value == 1) {
                    return "VC";
                } else if (value == 0) {
                    return "CC";
                }

            },
            creditCardStatus: function (value) {
                if (!value) {
                    return '';
                }

                if (value == 1) {
                    return "Created";
                } else if (value == 2) {
                    return "Scheduled";
                } else if (value == 3) {
                    return "In-Retry";
                } else if (value == 4) {
                    return "Failed";
                } else if (value == 5) {
                    return "Void";
                }
            },
            is_3ds: function (value) {

                if (value == 1) {
                    return "3DS required";
                } else if (value == 0) {
                    return "3DS not required";
                }

            },
            isDefault: function (value) {

                if (value == 1) {
                    return "Default Card";
                } else if (value == 0) {
                    return "Old Card";
                }

            },
            transactionStatus: function (value) {

                if (value == 1) {
                    return "Yes";
                } else if (value == 0) {
                    return "No";
                }

            },
            transactionType: function (value) {

                var transaction_type = '';

                if (value == 1) {
                    transaction_type = 'Payment';
                } else if (value == 2) {
                    transaction_type = 'Payment #1';
                } else if (value == 3) {
                    transaction_type = 'Payment #2';
                } else if (value == 4) {
                    transaction_type = 'Refund';
                } else if (value == 5) {
                    transaction_type = 'Refund';
                } else if (value == 6) {
                    transaction_type = 'Payment (Manual)';
                } else if (value == 8) {
                    transaction_type = 'Payment #1 (Manual)';
                } else if (value == 9) {
                    transaction_type = 'Payment #2 (Manual)';
                } else if (value == 10) {
                    transaction_type = 'Refund (Manual)';
                } else if (value == 11) {
                    transaction_type = 'Refund (Manual)';
                } else if (value == 12) {
                    transaction_type = 'Auto Security Deposit Collection Full';
                } else if (value == 13) {
                    transaction_type = 'Auto Security Deposit Refund Full';
                } else if (value == 14) {
                    transaction_type = 'Manual Security Deposit Collection';
                } else if (value == 15) {
                    transaction_type = 'Manual Security Deposit Refund';
                } else if (value == 16) {
                    transaction_type = 'Manual Security Deposit Refund Partial';
                } else if (value == 17) {
                    transaction_type = 'Additional Charge';
                } else if (value == 18) {
                    transaction_type = 'Cancellation Fee';
                } else if (value == 23) {
                    transaction_type = 'Refund';
                }

                return transaction_type;

            },
            propertyStatus(value) {
                if (value == 0) {
                    return "DeActive";
                } else if (value == 1) {
                    return "Active";
                }
            },
            availabilityOnPMS(value) {
                if (value == 0) {
                    return "Not valid or deleted on PMS";
                } else if (value == 1) {
                    return "Available or Valid Property";
                }
            },
            paymentGatewayIsVerified(value) {
                if (value == 0) {
                    return "Global";
                } else if (value == 1) {
                    return "Local";
                }
            },
            paymentGatewayStatus(value) {
                if (value == 0) {
                    return "DeActive";
                } else if (value == 1) {
                    return "Active";
                }
            },
            formatDateTime(value) {
                var months = {
                    'jan': '01', 'feb': '02', 'mar': '03', 'apr': '04',
                    'may': '05', 'jun': '06', 'jul': '07', 'aug': '08',
                    'sep': '09', 'oct': '10', 'nov': '11', 'dec': '12'
                };
                var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                let date = new Date(value);
                let day = date.getDate();
                let monthIndex = date.getMonth();
                let year = date.getFullYear();
                let hours = date.getHours();
                let minutes = date.getMinutes();
                let seconds = date.getSeconds();

                return monthNames[monthIndex] + ' ' + day + ' ' + year + ' ' + hours + ':' + minutes + ':' + seconds;
            }

        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                booking_details: (state) => {
                    return state.admin_booking.booking_details;
                },
            })
        },
        mounted() {
            this.getAdminBookingDetail()
        },
        watch: {
            //
        },

    }

</script>

<style scoped>
    /*Table Collaps style*/
    .parent ~ .cchild {
        display: none;
    }

    .open .parent ~ .cchild {
        display: table-row;
    }

    .parent {
        cursor: pointer;
    }

    tbody {
        color: #212121;
    }

    .open {
        background-color: #e6e6e6;
    }

    .open .cchild {
        background-color: #999;
        color: white;
    }

    .parent > *:last-child {
        width: 30px;
    }

    .parent i {
        transform: rotate(0deg);
        transition: transform .3s cubic-bezier(.4, 0, .2, 1);
        margin: -.5rem;
        padding: .5rem;

    }

    .open .parent i {
        transform: rotate(180deg)
    }

    /*!*Div open*!*/
    .open-div {
        background-color: #e6e6e6 !important;
    }

    .open-div-row {
        padding: 2rem !important;
        margin-top: 2rem !important;
    }

</style>
