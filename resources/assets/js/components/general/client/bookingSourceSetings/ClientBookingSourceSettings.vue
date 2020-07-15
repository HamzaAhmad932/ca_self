<template>
    <div>
        <!--Booking Source Seetings Payment Rules Begin-->
        <div :class="isMasterSettings ? 'setup-box account-setup-content' : 'setup-box'">
            <div class="setup-body">
                <h4 class="setup-page-title" v-if="action === 'edit'">Set your payment rules per booking source</h4>
                <!--                <div class="row justify-space-between" v-if="action === 'edit' && Object.keys(bookingSourcesWithSettings).length > 1">-->
                <!--                    <div class="col-md-4">-->
                <!--                    </div>-->
                <!--                    <div class="col-md-8 col-lg-6 ml-auto">-->
                <!--                        <div class="connection-btn-stack">-->
                <!--                            <div class="btn-group">-->
                <!--                                <a class="btn btn-sm btn-secondary px-3" href="javaScript:void(0)" @click="connectAllBS(true)">Connect All</a>-->
                <!--                                <a class="btn btn-sm btn-secondary px-3" href="javaScript:void(0)" @click="connectAllBS(false)">Disconnect All</a></div>-->
                <!--                        </div>-->
                <!--                    </div>-->
                <!--                </div>-->

                <div class="card"
                     style="max-height: 410px; margin-top:20px;  overflow: auto;    padding: 10px 10px; !important;">
                    <div :id="'sourceBooking'+bookingSource.id" class="accordion mb-2"
                         v-for="(bookingSource, bSIndex) in bookingSourcesWithSettings">
                        <div class="card">
                            <div :aria-controls="'collapseOne'+bookingSource.id" :data-target="'#collapseOne'+bookingSource.id"
                                 :id="'headingOne'+bookingSource.id" aria-expanded="true"
                                 class="card-header cursor-pointer" data-toggle="collapse">
                                <a class="booking-accordion-title collapsed float-left">
                                    <div class="booking-source-logo">
                                        <img :src="bookingSource.logo" v-if="bookingSource.logo.length > 2"/>
                                        <div class="display-initials-wrapper s5" v-if="bookingSource.logo.length < 3">
                                            <span class="initial_icon">{{ bookingSource.logo }}</span>
                                        </div>
                                    </div>
                                    {{bookingSource.name}}
                                    <!--   <div class="accordion-toggle"></div>-->
                                </a>
                                <!-- Rounded switch -->
                                <!--<div style ="text-align:right;padding-top:20px;padding-right:20px; !important" >
                                <label class="switch" @click.stop="stopPropagationEvent($event)">
                                    <input type="checkbox" v-model.boolean="bookingSource.status">
                                    <span class="slider round"></span>
                                </label>
                                </div>-->
                            </div>

                            <div :class="'checkbox-toggle checkbox-choice on-off-buttion-at-booking-sources-page main_source_checkbox'+bookingSource.id">
                                <input :id="'OnOffForSource'+bookingSource.id" @click.stop="stopPropagationEvent($event)" @change="checkSourceRules(bSIndex)"
                                       class="custom-control-input"
                                       type="checkbox"
                                       v-model.boolean="bookingSource.status">
                                <label :for="'OnOffForSource'+bookingSource.id" class="checkbox-label" data-off="OFF"
                                       data-on="ON">
                                <span class="toggle-track">
                                    <span class="toggle-switch"></span>
                                </span>
                                    <span class="toggle-title"></span>
                                </label>
                            </div>

                            <div :aria-labelledby="'headingOne'+bookingSource.id"
                                 :class="(bookingSourceFormId != 0 ? 'collapse show' : 'collapse')" :data-parent="'#sourceBooking'+bookingSource.id"
                                 :id="'collapseOne'+bookingSource.id">
                                <div class="card-body"
                                     v-if="bookingSource.payment_capability || bookingSource.security_capability">
                                    <div class="input-group input-group-sm" v-if="action === 'edit'">
                                        <div class="input-group-prepend">
                                            <label :for="'autoSettingsOne'+bookingSource.id"
                                                   class="input-group-text label-select_for_copy">Copy settings from</label>
                                        </div>
                                        <select :data-bk-id="bookingSource.id"
                                                :id="'autoSettingsOne'+bookingSource.id" @change="setBookingSourceSettingsFromPreviousSetSettings($event.target, bookingSource.id)"
                                                class="custom-select custom-select-sm select_for_copy">
                                            <option :value="0" selected="">Choose...</option>
                                            <option :value="bs.id"
                                                    v-for="bs in bookingSourcesWithSettings"
                                                    v-if="(bs.id != bookingSource.id) && bs.payment_capability"> {{bs.name}}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="source-divider" v-if="action === 'edit'"><span>or Set settings manually ↓</span>
                                    </div>

                                    <!-- Credit Card Validation Begin -->
                                    <div :id="'cardValidation'+bookingSource.id" class="accordion mb-2"
                                         v-if="bookingSource.payment_capability && bookingSource.support_cc">
                                        <div class="card child-card">
                                            <div :aria-controls="'settings1'+bookingSource.id" :data-target="'#settings1'+bookingSource.id"
                                                 :id="'settingsOne'+bookingSource.id" aria-expanded="true"
                                                 class="card-header cursor-pointer" data-toggle="collapse">
                                                <a class="booking-accordion-title collapsed float-left child-collapse">
                                                    Credit Card Validation
                                                    <span class="ml-1">(Pre-Authorization)</span>
                                                </a>
                                            </div>
                                            <div :class="'checkbox-toggle checkbox-choice on-off-buttion-at-booking-sources-page inner_source_checkbox'+bookingSource.id">
                                                <input :id="'OnOffForSource_CCValidation'+bookingSource.id" @click.stop="stopPropagationEvent($event)" @change="checkActiveStatus(bSIndex)"
                                                       class="custom-control-input"
                                                       type="checkbox"
                                                       v-model="bookingSource.booking_deposit.status">
                                                <label :for="'OnOffForSource_CCValidation'+bookingSource.id"
                                                       class="checkbox-label"
                                                       data-off="OFF" data-on="ON">
                                                    <span class="toggle-track">
                                                        <span class="toggle-switch"></span>
                                                    </span>
                                                    <span class="toggle-title"></span>
                                                </label>
                                            </div>
                                            <div :aria-labelledby="'settingsOne'+bookingSource.id" :data-parent="'#cardValidation'+bookingSource.id"
                                                 :id="'settings1'+bookingSource.id"
                                                 class="collapse">
                                                <!--Credit Card Auth Card Body Begin-->
                                                <div class="card-body">
                                                    <div class="card-section-title mt-1 mb-2">How much to authorize?
                                                    </div>
                                                    <div class="form-row align-items-end mb-2">
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label :for="'amountType'+bookingSource.id">Amount
                                                                Type</label>
                                                            <select class="custom-select custom-select-sm"
                                                                    id="'amountType'+bookingSource.id"
                                                                    v-model="bookingSource.booking_deposit.amountType">
                                                                <option :value="amountTypes.fixedAmount">Fixed Amount
                                                                </option>
                                                                <option :value="amountTypes.percentageAmount">% of
                                                                    Booking Amount
                                                                </option>
                                                                <option :value="amountTypes.firstNightAmount">First
                                                                    Night
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-md-2 col-sm-3"
                                                             v-if="bookingSource.booking_deposit.amountType < amountTypes.firstNightAmount">
                                                            <label v-if="bookingSource.booking_deposit.amountType === amountTypes.fixedAmount">Fixed
                                                                Amount</label>
                                                            <label v-if="bookingSource.booking_deposit.amountType === amountTypes.percentageAmount">Percentage</label>
                                                            <input class="form-control form-control-sm"
                                                                   type="text"
                                                                   v-mask="'#######'"
                                                                   v-model="bookingSource.booking_deposit.amountTypeValue" value="75.00">
                                                        </div>

                                                    </div>
                                                    <div class="card-section-title mt-1 mb-2">When to authorize?</div>
                                                    <div class="form-row align-items-end mb-2">
                                                        <div class="form-group col-md-3 col-sm-6">

                                                            <select :id="'timeAfterBooking'+bookingSource.id"
                                                                    class="form-control"
                                                                    v-model="bookingSource.booking_deposit.authorizeAfterDays">
                                                                <option :value="0"> immediately</option>
                                                                <option :value="n*60*60" v-for="n in 23"> {{n}} {{n > 1
                                                                    ? 'Hours' : 'Hour'}}
                                                                </option>
                                                                <option :value="j*86400" v-for="j in 365"> {{j}} {{j > 1
                                                                    ? 'Days' : 'Day'}}
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label :for="'timeAfterBooking'+bookingSource.id">After
                                                                Booking</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-row align-items-end mb-2">
                                                        <div class="form-group col-md-6">
                                                            <div class="form-check mb-1">
                                                                <input :id="'checkAuto1'+bookingSource.id"
                                                                       class="form-check-input"
                                                                       type="checkbox"
                                                                       v-model="bookingSource.booking_deposit.autoReauthorize">
                                                                <label :for="'checkAuto1'+bookingSource.id"
                                                                       class="form-check-label">Automatically
                                                                    re-authorize every seven days</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-warning mb-1" role="alert"><i
                                                            class="fas fa-exclamation-circle"> </i> Note: Above rules do
                                                        not apply to virtual cards.
                                                    </div>
                                                </div>
                                                <!--Credit Card Auth Card Body Begin-->
                                            </div>
                                        </div>

                                    </div>
                                    <!-- Credit Card Validation End-->

                                    <!-- Payment Schedule Begin -->
                                    <div :id="'paymentSchedule'+bookingSource.id" class="accordion mb-2"
                                         v-if="bookingSource.payment_capability">
                                        <!--Payment Charge Card Body Begin-->
                                        <div class="card child-card">
                                            <div :aria-controls="'settings2'+bookingSource.id" :data-target="'#settings2'+bookingSource.id"
                                                 :id="'settingsTwo'+bookingSource.id" aria-expanded="true"
                                                 class="card-header cursor-pointer" data-toggle="collapse">
                                                <a class="booking-accordion-title collapsed float-left child-collapse">Payment Schedule</a>
                                            </div>
                                            <div :class="'checkbox-toggle checkbox-choice on-off-buttion-at-booking-sources-page inner_source_checkbox'+bookingSource.id">
                                                <input :id="'OnOffForSource_PS'+bookingSource.id" @click.stop="stopPropagationEvent($event)"
                                                       @change="checkActiveStatus(bSIndex)"
                                                       class="custom-control-input"
                                                       type="checkbox"
                                                       v-model="bookingSource.booking_payment.status">
                                                <label :for="'OnOffForSource_PS'+bookingSource.id"
                                                       class="checkbox-label" data-off="OFF"
                                                       data-on="ON">
                                                    <span class="toggle-track">
                                                        <span class="toggle-switch"></span>
                                                    </span>
                                                    <span class="toggle-title"></span>
                                                </label>
                                            </div>
                                            <div :aria-labelledby="'settingsTwo'+bookingSource.id" :data-parent="'#paymentSchedule'+bookingSource.id"
                                                 :id="'settings2'+bookingSource.id"
                                                 class="collapse">
                                                <div class="card-body">
                                                    <div class="card-section-title mt-1 mb-2">Collect Booking Amount

                                                            <div v-if="bookingSource.support_cc && bookingSource.support_vc" :class="'checkbox-toggle checkbox-choice on-off-buttion-at-booking-sources-page inner_source_checkbox'+bookingSource.id"
                                                            title="Process Only VC Bookings.">
                                                                <span style="float:right;top: -16px; position: relative"> Process Only Virtual Card Bookings &nbsp;</span>
                                                            <input :id="'OnlyVC'+bookingSource.id"
                                                                      class="custom-control-input"
                                                                      type="checkbox"
                                                                      v-model="bookingSource.booking_payment.onlyVC">
                                                            <label :for="'OnlyVC'+bookingSource.id"
                                                                   class="checkbox-label" data-off="OFF"
                                                                   data-on="ON" style="top: -16px;">
                                                                <span class="toggle-track">
                                                                    <span class="toggle-switch"></span>
                                                                </span>
                                                                <span class="toggle-title"></span>
                                                            </label>
                                                            </div>
                                                    </div>
                                                    <div  v-if="bookingSource.support_cc && !bookingSource.booking_payment.onlyVC"
                                                          class="form-row align-items-end mb-2" style="margin-top:5%">
                                                        <div class="form-group col-md-4">
                                                            <label :for="'amountTypePayment'+bookingSource.id">Amount
                                                                Type</label>
                                                            <select :id="'amountTypePayment'+bookingSource.id"
                                                                    class="custom-select custom-select-sm"
                                                                    v-model="bookingSource.booking_payment.amountType">
                                                                <option :value="amountTypes.percentageAmount">% of
                                                                    Booking Amount
                                                                </option>
                                                                <option :value="amountTypes.firstNightAmount">First
                                                                    Night
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-md-2">
                                                            <div v-if="bookingSource.booking_payment.amountType === amountTypes.percentageAmount">
                                                                <label style="width: 125px !important;"
                                                                       v-if="bookingSource.booking_payment.amountType === amountTypes.percentageAmount">Percentage</label>
                                                                <select :id="'amountTypePaymentSelect'+bookingSource.id"
                                                                        class="custom-select custom-select-sm"
                                                                        v-model="bookingSource.booking_payment.amountTypeValue">
                                                                    <option :value="n" v-for="n in 100">{{n}}</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="form-group col-md-1" style="text-align: right">
                                                            <label class="m-radio m-radio--solid m-radio--state-brand">
                                                                <input :checked="bookingSource.booking_payment.dayType === 1"
                                                                       :name="'paymentChargeDay'+bookingSource.id"
                                                                       :value="dayType.afterBooking"
                                                                       type="radio"
                                                                       v-model="bookingSource.booking_payment.dayType">
                                                                <span></span></label>
                                                        </div>

                                                        <div class="form-group col-md-3">

                                                            <select :disabled="bookingSource.booking_payment.dayType === dayType.beforeCheckIn"
                                                                    :id="'timeAfterBookingPayment'+bookingSource.id"
                                                                    class="form-control"
                                                                    v-model="bookingSource.booking_payment.afterBookingDays">
                                                                <option :value="0"> immediately</option>
                                                                <option :value="n*60*60" v-for="n in 23"> {{n}} {{n > 1
                                                                    ? 'Hours' : 'Hour'}}
                                                                </option>
                                                                <option :value="j*86400" v-for="j in 365"> {{j}} {{j > 1
                                                                    ? 'Days' : 'Day'}}
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label class="m-radio m-radio--solid m-radio--state-brand">after
                                                                booking<span></span></label>
                                                        </div>

                                                    </div>
                                                    <div v-if="bookingSource.support_cc && !bookingSource.booking_payment.onlyVC" class="form-row align-items-end mb-2">
                                                        <div class="form-group col-md-5 offset-6 text-center">
                                                            <label class="m-radio m-radio--solid m-radio--state-brand">OR</label>
                                                        </div>
                                                    </div>

                                                    <div  v-if="bookingSource.support_cc && !bookingSource.booking_payment.onlyVC" class="form-row align-items-end mb-2">

                                                        <div class="form-group col-md-1 offset-6"
                                                             style="text-align: right">
                                                            <label class="m-radio m-radio--solid m-radio--state-brand">
                                                                <input :checked="bookingSource.booking_payment.dayType === 2"
                                                                       :name="'paymentChargeDay'+bookingSource.id"
                                                                       :value="dayType.beforeCheckIn"
                                                                       type="radio"
                                                                       v-model="bookingSource.booking_payment.dayType">
                                                                <span></span></label>
                                                        </div>

                                                        <div class="form-group col-md-3">
                                                            <!--                                                        <label :for="'timeAfterBooking'+bookingSource.id">Day</label>-->
                                                            <select :disabled="bookingSource.booking_payment.dayType === dayType.afterBooking"
                                                                    :id="'timeAfterBookingPayment'+bookingSource.id"
                                                                    class="form-control"
                                                                    v-model="bookingSource.booking_payment.beforeCheckInDays">
                                                                <option :value="0"> immediately</option>
                                                                <option :value="n*60*60" v-for="n in 23"> {{n}} {{n > 1
                                                                    ? 'Hours' : 'Hour'}}
                                                                </option>
                                                                <option :value="j*86400" v-for="j in 365"> {{j}} {{j > 1
                                                                    ? 'Days' : 'Day'}}
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label class="m-radio m-radio--solid m-radio--state-brand">
                                                                before check-in
                                                                <span class="small ml-1 text-default" v-tooltip.top-center="'Default check-in time is 4:00PM local time'"  tabindex="0"
                                                                      title="Default check-in time is 4:00PM local time"><i class="fas fa-info-circle"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div v-if="bookingSource.support_cc && !bookingSource.booking_payment.onlyVC
                                                    && ((bookingSource.booking_payment.amountType === amountTypes.firstNightAmount)
                                                    || (bookingSource.booking_payment.amountTypeValue < 100))">
                                                        <div class="card-section-title mt-1 mb-2">Remaining Balance
                                                        </div>
                                                        <div class="form-row align-items-end mb-2">
                                                            <div class="form-group col-md-3">
                                                                <select class="form-control"
                                                                        v-model="bookingSource.booking_payment.remainingBeforeCheckInDays">
                                                                    <option :value="0"> immediately</option>
                                                                    <option :value="n*60*60" v-for="n in 23"> {{n}} {{n
                                                                        > 1 ? 'Hours' : 'Hour'}}
                                                                    </option>
                                                                    <option :value="j*86400" v-for="j in 365"> {{j}} {{j
                                                                        > 1 ? 'Days' : 'Day'}}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-2">
                                                                <label class="m-radio m-radio--solid m-radio--state-brand">
                                                                    before check-in
                                                                    <span class="small ml-1 text-default" v-tooltip.top-center="'Default check-in time is 4:00PM local time'"  tabindex="0"
                                                                          title="Default check-in time is 4:00PM local time"><i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="alert alert-warning mb-1" role="alert"
                                                         v-if="((bookingSource.channel_code === 19) || (bookingSource.channel_code === '19')) && !bookingSource.booking_payment.onlyVC">
                                                        <i class="fas fa-exclamation-circle"> </i> Note:
                                                        For Booking.com non-refundable bookings, 100% of the reservation
                                                        amount will be charged right away.
                                                    </div>
                                                    <div class="alert alert-warning mb-1" role="alert" v-if="bookingSource.support_vc">
                                                        <i class="fas fa-exclamation-circle"> </i> Note:
                                                        {{(bookingSource.booking_payment.onlyVC || !bookingSource.support_cc) ? 'For virtual card, ' : 'If virtual card is detected, '}}
                                                         we will follow {{bookingSource.name}}'s policy.
                                                    </div>
                                                    <!--                                                : Charge the virtual card the non-refundable amount based on your refund policy.-->
                                                </div>
                                            </div>
                                        </div>
                                        <!--Payment Charge Card Body End-->

                                    </div>
                                    <!-- Payment Schedule End -->

                                    <!-- SD Deposit Begin -->
                                    <div :id="'securityDeposit'+bookingSource.id" class="accordion mb-2"
                                         v-if="bookingSource.security_capability">
                                        <div class="card child-card">
                                            <div :aria-controls="'settings3'+bookingSource.id"
                                                 :data-target="'#settings3'+bookingSource.id" :id="'settingsThree'+bookingSource.id"
                                                 aria-expanded="true" class="card-header cursor-pointer"
                                                 data-toggle="collapse">
                                                <a class="booking-accordion-title collapsed float-left child-collapse">
                                                    Security / Damage Deposit</a>
                                            </div>
                                            <div :class="'checkbox-toggle checkbox-choice on-off-buttion-at-booking-sources-page inner_source_checkbox'+bookingSource.id">
                                                <input :id="'OnOffForSource_SD'+bookingSource.id" @click.stop="stopPropagationEvent($event)"
                                                       @change="checkActiveStatus(bSIndex)"
                                                       class="custom-control-input"
                                                       type="checkbox"
                                                       v-model="bookingSource.security_deposit.status">
                                                <label :for="'OnOffForSource_SD'+bookingSource.id"
                                                       class="checkbox-label" data-off="OFF"
                                                       data-on="ON">
                                                    <span class="toggle-track">
                                                        <span class="toggle-switch"></span>
                                                    </span>
                                                    <span class="toggle-title"></span>
                                                </label>
                                            </div>
                                            <div :aria-labelledby="'settingsThree'+bookingSource.id" :data-parent="'#securityDeposit'+bookingSource.id"
                                                 :id="'settings3'+bookingSource.id"
                                                 class="collapse">
                                                <div :aria-labelledby="'settingsThree'+bookingSource.id" :data-parent="'#securityDeposit'+bookingSource.id"
                                                     :id="'settings3'+bookingSource.id"
                                                     class="collapse">
                                                    <!--Security Deposit  Card Body  Begin-->
                                                    <div class="card-body">
                                                        <div class="card-section-title mt-1 mb-2">How much to
                                                            authorize?
                                                        </div>
                                                        <div class="form-row align-items-end mb-2">
                                                            <div class="form-group col-md-6">
                                                                <label :for="'amountType'+bookingSource.id">Amount
                                                                    Type</label>
                                                                <select class="custom-select custom-select-sm"
                                                                        id="'securityDepositAmountType'+bookingSource.id"
                                                                        v-model="bookingSource.security_deposit.amountType">
                                                                    <option :value="amountTypes.fixedAmount">Fixed
                                                                        Amount
                                                                    </option>
                                                                    <option :value="amountTypes.percentageAmount">% of
                                                                        Booking Amount
                                                                    </option>
                                                                    <option :value="amountTypes.firstNightAmount">First
                                                                        Night
                                                                    </option>
                                                                </select>
                                                            </div>

                                                            <div class="form-group col-md-2"
                                                                 v-if="bookingSource.security_deposit.amountType < amountTypes.firstNightAmount">
                                                                <label v-if="bookingSource.security_deposit.amountType === amountTypes.fixedAmount">Fixed
                                                                    Amount</label>
                                                                <label v-if="bookingSource.security_deposit.amountType === amountTypes.percentageAmount">Percentage</label>
                                                                <input class="form-control form-control-sm"
                                                                       type="text"
                                                                       v-mask="'#######'"
                                                                       v-model="bookingSource.security_deposit.amountTypeValue" value="75.00">
                                                            </div>
                                                        </div>

                                                        <div class="card-section-title mt-1 mb-2">When to authorize?
                                                        </div>
                                                        <div class="form-row align-items-end mb-2">
                                                            <div class="form-group col-md-6">

                                                                <select :id="'timeAfterBooking'+bookingSource.id"
                                                                        class="form-control"
                                                                        v-model="bookingSource.security_deposit.authorizeAfterDays">
                                                                    <option :value="0"> immediately</option>
                                                                    <option :value="n*60*60" v-for="n in 23"> {{n}} {{n
                                                                        > 1 ? 'Hours' : 'Hour'}}
                                                                    </option>
                                                                    <option :value="j*86400" v-for="j in 365"> {{j}} {{j
                                                                        > 1 ? 'Days' : 'Day'}}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label :for="'timeAfterBooking'+bookingSource.id">
                                                                    Before Check-In
                                                                    <span class="small ml-1 text-default" v-tooltip.top-center="'Default check-in time is 4:00PM local time'"  tabindex="0"
                                                                          title="Default check-in time is 4:00PM local time"><i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-row align-items-end mb-2">
                                                            <div class="form-group col-md-6">
                                                                <div class="form-check mb-1">
                                                                    <input :id="'checkAuto2'+bookingSource.id"
                                                                           class="form-check-input"
                                                                           type="checkbox"
                                                                           v-model="bookingSource.security_deposit.autoReauthorize">
                                                                    <label :for="'checkAuto2'+bookingSource.id"
                                                                           class="form-check-label">Automatically
                                                                        re-authorize every seven days</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="alert alert-warning mb-1" role="alert"><i
                                                                class="fas fa-exclamation-circle"> </i> Note:
                                                            if guest credit card is not available, an email notification
                                                            will be sent with a link for guest to add their card.
                                                        </div>
                                                    </div>
                                                    <!--Security Deposit  Card Body End-->
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <!-- SD Deposit End -->

                                    <!-- Cancellation Policies Begin -->
                                    <div :id="'cancelationPolicy'+bookingSource.id" class="accordion mb-2"
                                         v-if="bookingSource.payment_capability">
                                        <div class="card child-card">
                                            <div :aria-controls="'settings4'+bookingSource.id"
                                                 :data-target="'#settings4'+bookingSource.id" :id="'settingsFour'+bookingSource.id"
                                                 aria-expanded="true" class="card-header cursor-pointer"
                                                 data-toggle="collapse">
                                                <a class="booking-accordion-title collapsed float-left child-collapse">Cancellation
                                                    Policy Setting</a>
                                            </div>
                                            <div :class="'checkbox-toggle checkbox-choice on-off-buttion-at-booking-sources-page inner_source_checkbox'+bookingSource.id">
                                                <input :id="'OnOffForSource_CPS'+bookingSource.id" @click.stop="stopPropagationEvent($event)"
                                                       @change="checkActiveStatus(bSIndex)"
                                                       class="custom-control-input"
                                                       type="checkbox"
                                                       v-model="bookingSource.return_rules.status">
                                                <label :for="'OnOffForSource_CPS'+bookingSource.id"
                                                       class="checkbox-label" data-off="OFF"
                                                       data-on="ON">
                                                    <span class="toggle-track">
                                                        <span class="toggle-switch"></span>
                                                    </span>
                                                    <span class="toggle-title"></span>
                                                </label>
                                            </div>
                                            <div :aria-labelledby="'settingsFour'+bookingSource.id" :data-parent="'#cancelationPolicy'+bookingSource.id"
                                                 :id="'settings4'+bookingSource.id"
                                                 class="collapse">
                                                <!--Return Rules | Cancellation Policies  Card Body  Begin-->
                                                <div class="card-body">
                                                    <div class="card-section-title mt-1 mb-2"
                                                         style="margin-bottom:25px !important;">Guests are not charged a
                                                        fee if they cancel <i class="fas fa-exclamation-circle"
                                                                              title="Any collected reservation payment will be refunded automatically"></i>
                                                        :
                                                    </div>
                                                    <div class="form-row align-items-end mb-2">
                                                        <div class="form-group col-md-1" style="text-align:right">


                                                            <!--<div style ="text-align:right;padding-top:10px;!important">
                                                                <label class="switch">
                                                                    <input type="checkbox"  v-model="bookingSource.return_rules.afterBookingStatus" @change="(bookingSource.return_rules.beforeCheckInStatus = (bookingSource.return_rules.afterBookingStatus && bookingSource.return_rules.beforeCheckInStatus ? false : bookingSource.return_rules.beforeCheckInStatus))">
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </div>-->

                                                            <div class="checkbox-toggle checkbox-choice">
                                                                <input :id="'CPS_afterBookingStatus'+bookingSource.id" @change="(bookingSource.return_rules.beforeCheckInStatus = (bookingSource.return_rules.afterBookingStatus && bookingSource.return_rules.beforeCheckInStatus ? false : bookingSource.return_rules.beforeCheckInStatus))"
                                                                       class="custom-control-input"
                                                                       type="checkbox"
                                                                       v-model="bookingSource.return_rules.afterBookingStatus">
                                                                <label :for="'CPS_afterBookingStatus'+bookingSource.id"
                                                                       class="checkbox-label"
                                                                       data-off="OFF" data-on="ON">
                                                                <span class="toggle-track">
                                                                    <span class="toggle-switch"></span>
                                                                </span>
                                                                    <span class="toggle-title"></span>
                                                                </label>
                                                            </div>

                                                        </div>

                                                        <div class="form-group col-md-3 ml-2">
                                                            <select :disabled="bookingSource.return_rules.beforeCheckInStatus === true"
                                                                    :id="'timeAfterBooking'+bookingSource.id"
                                                                    class="form-control"
                                                                    v-model="bookingSource.return_rules.afterBooking">
                                                                <option :value="0"> Any Time</option>
                                                                <option :value="n*60*60" v-for="n in 23"> within {{n}}
                                                                    {{n > 1 ? 'Hours' : 'Hour'}}
                                                                </option>
                                                                <option :value="j*86400" v-for="j in 365"> within {{j}}
                                                                    {{j > 1 ? 'Days' : 'Day'}}
                                                                </option>
                                                            </select>
                                                        </div>


                                                        <div class="form-group col-md-4">
                                                            <div class="form-check mb-1" style="text-align:left">
                                                                <label class="form-check-label"
                                                                       style="padding-bottom: 5px;margin-left: -20px !important;">
                                                                    after booking.</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-row align-items-end mb-2">
                                                        <div class="form-group col-md-1"></div>
                                                        <div class="form-group col-md-3 text-center">
                                                            <label>OR</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-row align-items-end mb-2">
                                                        <div class="form-group col-md-1">

                                                            <!--<div style ="text-align:right;padding-top:10px; !important">
                                                                <label class="switch">
                                                                    <input type="checkbox" class="custom-control-input"  v-model="bookingSource.return_rules.beforeCheckInStatus" :id="'checkAuto3'+bookingSource.id"
                                                                           @change="beforeCheckinStatusUpdate(bSIndex)">
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </div>-->

                                                            <div class="checkbox-toggle checkbox-choice">
                                                                <input :id="'checkAuto3'+bookingSource.id" @change="beforeCheckinStatusUpdate(bSIndex, $event)"
                                                                       class="custom-control-input"
                                                                       type="checkbox"
                                                                       v-model="bookingSource.return_rules.beforeCheckInStatus">
                                                                <label :for="'checkAuto3'+bookingSource.id"
                                                                       class="checkbox-label" data-off="OFF"
                                                                       data-on="ON">
                                                                <span class="toggle-track">
                                                                    <span class="toggle-switch"></span>
                                                                </span>
                                                                    <span class="toggle-title"></span>
                                                                </label>
                                                            </div>

                                                        </div>

                                                        <div class="form-group col-md-3 ml-2 text-center">
                                                            <select :disabled="bookingSource.return_rules.afterBookingStatus === true"
                                                                    :id="'timeAfterBooking2'+bookingSource.id"
                                                                    @change="beforeCheckinValueUpdate(bSIndex, $event)"
                                                                    class="form-control"
                                                                    v-model="bookingSource.return_rules.beforeCheckIn">
                                                                <option :value="0"> Any Time</option>
                                                                <option :value="n*60*60" v-for="n in 23"> More than
                                                                    {{n}} {{n > 1 ? 'Hours' : 'Hour'}}
                                                                </option>
                                                                <option :value="j*86400" v-for="j in 365"> More than
                                                                    {{j}} {{j > 1 ? 'Days' : 'Day'}}
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-md-4">
                                                            <div class="form-check mb-1" style="text-align:left">
                                                                <label class="form-check-label"
                                                                       style="padding-bottom: 5px;margin-left: -20px !important;">
                                                                    before check-in.
                                                                    <span class="small ml-1 text-default" v-tooltip.top-center="'Default check-in time is 4:00PM local time'"  tabindex="0"
                                                                          title="Default check-in time is 4:00PM local time"><i class="fas fa-info-circle"></i>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div v-if="!(bookingSource.return_rules.beforeCheckInStatus && bookingSource.return_rules.beforeCheckIn == '0')">
                                                        <div class="card-section-title mt-1 mb-2" style="
                                                :25px !important;">Guests are charged for cancellations, as per the
                                                            rules below:
                                                        </div>

                                                        <div class="form-row align-items-end">
                                                            <div class="form-group col-md-3">
                                                                <label class="form-check-label">Cancellation Fee</label>
                                                            </div>

                                                            <div class="form-group col-md-5">
                                                                <label class="form-check-label">if cancelled
                                                                    within</label>
                                                            </div>

                                                            <div class="form-group col-md-4">
                                                                <label class="form-check-label">Flat Fee:</label>
                                                            </div>

                                                        </div>
                                                        <div class="form-row align-items-end mb-2"
                                                             v-for="(rule , index) in bookingSource.return_rules.rules">

                                                            <div class="form-group col-md-3">
                                                                <select :style="[(rule.canFee === '')  || (rule.canFee === undefined) ? {'border':'1px solid red'} : {}]"
                                                                        @change="ruleChangedCanFee(bookingSource.id)"
                                                                        class="form-control"
                                                                        v-model.number="rule.canFee">
                                                                    <option :value="n"
                                                                            v-for="n in 100"
                                                                            v-if="(index === 0 || ( index > 0  && bookingSource.return_rules.rules[(bookingSource.return_rules.rules[index-1].canFee === 'first_night' && index > 1 ? index-2 : index-1)].canFee > n)) && ((n % 5) === 0)">
                                                                        {{n}} %
                                                                    </option>
                                                                    <option value="first_night">First Night</option>
                                                                </select>
                                                            </div>

                                                            <div class="form-group col-md-3">
                                                                <select :id="'timeAfterBooking'+bookingSource.id"
                                                                        @change="ruleChangedIsCancelled(bookingSource.id)"
                                                                        class="form-control"
                                                                        v-bind:style="[((rule.is_cancelled !== 0  &&  rule.is_cancelled !== 0) && ((rule.is_cancelled === '') || (rule.is_cancelled === undefined)))
                                                                 ? {'border':'1px solid red'} : {}]"
                                                                        v-model.number="rule.is_cancelled">
                                                                    <option :value="0"
                                                                            v-if="!bookingSource.return_rules.beforeCheckInStatus && cancelledCheck(index, bookingSource.return_rules.beforeCheckInStatus, bookingSource.return_rules.beforeCheckIn, bookingSource.return_rules.afterBookingStatus, bookingSource.return_rules.rules, 0)"
                                                                    >
                                                                        Any Time
                                                                    </option>

                                                                    <option
                                                                            :value="y*86400"
                                                                            v-for="y in year"
                                                                            v-if="cancelledCheck(index, bookingSource.return_rules.beforeCheckInStatus, bookingSource.return_rules.beforeCheckIn, bookingSource.return_rules.afterBookingStatus, bookingSource.return_rules.rules, y)"
                                                                    >
                                                                        {{y}} {{y > 1 ? 'Days' : 'Day'}}
                                                                    </option>
                                                                    <option v-if="bookingSource.return_rules.beforeCheckIn != 0 && bookingSource.return_rules.beforeCheckIn < 86400"
                                                                            value="86400">1 Day
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-2">
                                                                <p style="line-height: 10px">of Check-in
                                                                    <strong style="
                                                                font-size: 150%;
                                                                font-weight: 600;
                                                                padding-left: 0.8em;
                                                             ">+</strong>
                                                                </p>
                                                            </div>

                                                            <div class="form-group col-md-3">
                                                                <input class="form-control" min="0" placeholder="00"
                                                                       type="text"
                                                                       v-mask="'#######'"
                                                                       v-model="rule.is_cancelled_value">
                                                            </div>
                                                            <div class="form-group col-md-1">
                                                                <button @click="(bookingSource.return_rules.rules.length  > 1 ? bookingSource.return_rules.rules.splice(index, 1) : cantRemove())"
                                                                        class="cross-btn">
                                                                    &#10060;
                                                                </button>
                                                            </div>

                                                            <div class="form-control-feedback"
                                                                 style="color: red"
                                                                 v-if="( (rule.canFee !== '' &&  rule.canFee !== undefined)
                                                                 && (rule.is_cancelled !== 0)
                                                                 && ((rule.is_cancelled === '') || (rule.is_cancelled === undefined)))">
                                                                Kindly add time duration for this Policy otherwise
                                                                System will discard this policy.
                                                            </div>

                                                            <div class="form-control-feedback"
                                                                 style="color: red"
                                                                 v-if="(((rule.canFee === '') || (rule.canFee=== undefined)) &&
                                                                ((rule.is_cancelled === 0) ||
                                                                (rule.is_cancelled !== '' &&  rule.is_cancelled !== undefined)))">
                                                                Kindly add Cancellation Fee for this Policy otherwise
                                                                System will discard this policy.
                                                            </div>
                                                        </div>

                                                        <div class="form-row align-items-end mb-2">
                                                            <div class="form-group col-md-4 offset-8"
                                                                 style="text-align: right">
                                                                <button @click="bookingSource.return_rules.rules.push({canFee: '', is_cancelled: '', is_cancelled_value: ''})"
                                                                        class="btn btn-primary px-md-4">
                                                                    <i class="fa fa-plus"></i> Add
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <!--Return Rules | Cancellation Policies Card Body End-->
                                        </div>
                                    </div>
                                    <!-- Cancellation Policies End -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="setup-footer d-flex justify-content-center">
            <a class="btn btn-light align-self-start setup-back text-muted" href="/client/v2/pms-setup-step-1"
               v-if="isMasterSettings"> <i class="fas fa-arrow-left"></i><span> Back </span></a>
            <a @click="saveSettings()" class="btn btn-success px-md-4" href="javascript:void(0);"
               v-if="action === 'edit'"> {{ isMasterSettings ? 'Save and Continue' : 'Save'}} <i class="fas fa-arrow-right"
                                                                                                 v-if="isMasterSettings"></i></a>
            <a class="btn btn-light align-self-start setup-skip text-muted" href="/client/v2/pms-setup-step-3"
               v-if="isMasterSettings"><span> Skip </span> <i class="fas fa-arrow-right"></i></a>
        </div>
        <!--Booking Source Settings Payment Rules End-->
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';

    Vue.use(VueToast);

    export default {
        props: ['propertyInfoId', 'propertyInfoObjectIndex', 'bookingSourceFormId', 'action', 'isMasterSettings'],
        data() {
            return {
                msg: 'Please Wait...',
                block: true,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                bookingSourcesWithSettings: {},
                //previousSetBookingSources : {},
                requestSent: false,
                amountTypes: {
                    fixedAmount: 1,
                    percentageAmount: 2,
                    firstNightAmount: 3,
                },
                propsValuesCopied: {
                    propertyInfoId: '',
                    bookingSourceFormId: '',

                },
                show_warning : [],

                dayType: {
                    'afterBooking': 1,
                    'beforeCheckIn': 2,
                },
                year: 365,
                show_single_day: false
            }
        },
        components: {
            //,
        },
        methods: {
            cancelledCheck(index, beforeCheckInStatus, beforeCheckIn, afterBookingStatus, rules, y) {


                if (index === 0 && beforeCheckInStatus && beforeCheckIn >= (y * 86400)) {
                    return true;
                }

                if (
                    index > 0
                    && beforeCheckInStatus
                    && beforeCheckIn >= (y * 86400)
                    && typeof rules[index - 1].is_cancelled !== "undefined"
                    && rules[index - 1].is_cancelled !== ''
                    && rules[index - 1].is_cancelled < (y * 86400)
                ) {
                    return true;
                }

                if (
                    index === 0
                    && afterBookingStatus
                ) {
                    return true;
                }

                if (
                    index > 0
                    && afterBookingStatus
                    && typeof rules[index - 1].is_cancelled !== "undefined"
                    && rules[index - 1].is_cancelled !== ''
                    && rules[index - 1].is_cancelled < (y * 86400)
                ) {
                    return true;
                }

                if (index === 0 && !beforeCheckInStatus) {
                    return true;
                }

                if (index > 0 && !beforeCheckInStatus) {
                    return rules[index - 1].is_cancelled < (y * 86400)
                }

                return false;


            },

            beforeCheckinValueUpdate(bs_index, e) {
                this.year = 365;
                if (e.target.value == '0') {
                    this.bookingSourcesWithSettings[bs_index].return_rules.rules = [{
                        canFee: '',
                        is_cancelled: '',
                        is_cancelled_value: ''
                    }];
                }
            },
            getClientBookingSourcePreviousSettings() {
                let _this = this;
                if ((_this.propertyInfoId !== undefined) && (_this.propertyInfoId !== '') && (_this.propertyInfoId >= 0)) {
                    _this.block = true;
                    _this.msg = 'Checking Previous Settings';
                    axios.post('/client/v2/get-client-bs-settings', {
                        "propertyInfoId": _this.propertyInfoId,
                        "bookingSourceFormId": _this.bookingSourceFormId
                    })
                        .then(function (response) {
                            if (response.data.status === true) {
                                _this.bookingSourcesWithSettings = response.data.data.bookingSourceSettings;
                                ///_this.previousSetBookingSources = response.data.data.previousSetBookingSources;
                            } else {
                                _this.bookingSourcesWithSettings = {};
                                //_this.previousSetBookingSources  = {};

                            }
                            _this.block = false;
                            _this.msg = 'Please Wait...';
                        }).catch(function (error) {
                        _this.bookingSourcesWithSettings = {};
                        //_this.previousSetBookingSources  = {};
                        console.log(error);
                        _this.block = false;
                        _this.msg = 'Please Wait...';
                        _this.toasterView('Failed to Get Booking Source Settings for Property', false);
                    });
                }
            },

            ruleChangedCanFee(bookingSourceId) {
                let totalRulesDefined = this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules.length;
                let i = 0;
                for (; i < totalRulesDefined; i++) {
                    let j = i;
                    for (; j < totalRulesDefined; j++) {
                        if (parseInt(this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].canFee) > parseInt(this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[i].canFee) && (this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].canFee !== 'first_night'))
                            this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].canFee = '';
                        if (parseInt(this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].canFee) === parseInt(this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[i].canFee) && i !== j)
                            this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].canFee = '';
                        if (this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[i].canFee === 'first_night' && this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].canFee === 'first_night' && i !== j)
                            this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].canFee = '';
                    }
                }
            },
            ruleChangedIsCancelled(bookingSourceId) {

                let totalRulesDefined = this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules.length;
                let i = 0;
                for (; i < totalRulesDefined; i++) {
                    let j = i;
                    for (; j < totalRulesDefined; j++) {
                        if ((this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].is_cancelled <= this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[i].is_cancelled) && (j != i))
                            this.bookingSourcesWithSettings[bookingSourceId].return_rules.rules[j].is_cancelled = '';
                    }
                }
            },

            setBookingSourceSettingsFromPreviousSetSettings(event, bookingSourceId) {
                //TODO Swall Add
                var _this = this;
                _this.block = true;
                if (this.bookingSourcesWithSettings[event.value] !== undefined) {
                    this.bookingSourcesWithSettings[bookingSourceId].booking_deposit = this.bookingSourcesWithSettings[event.value].booking_deposit;
                    this.bookingSourcesWithSettings[bookingSourceId].booking_payment = this.bookingSourcesWithSettings[event.value].booking_payment;
                    this.bookingSourcesWithSettings[bookingSourceId].security_deposit = this.bookingSourcesWithSettings[event.value].security_deposit;
                    this.bookingSourcesWithSettings[bookingSourceId].return_rules = this.bookingSourcesWithSettings[event.value].return_rules;
                    this.bookingSourcesWithSettings[bookingSourceId].status = this.bookingSourcesWithSettings[event.value].status;
                    _this.block = false;
                    _this.toasterView('Settings Copied, Kindly Save Your Settings!', true);
                } else {
                    axios.post('/client/v2/get-client-bs-settings', {
                        "propertyInfoId": _this.propertyInfoId,
                        "bookingSourceFormId": event.value,
                        'previousSetBookingSourcesCheck': false
                    })
                        .then(function (response) {
                            if (response.data.status === true) {
                                let data = response.data.data.bookingSourceSettings[event.value];
                                _this.bookingSourcesWithSettings[bookingSourceId].booking_deposit = data.booking_deposit;
                                _this.bookingSourcesWithSettings[bookingSourceId].booking_payment = data.booking_payment;
                                _this.bookingSourcesWithSettings[bookingSourceId].security_deposit = data.security_deposit;
                                _this.bookingSourcesWithSettings[bookingSourceId].return_rules = data.return_rules;
                                _this.bookingSourcesWithSettings[bookingSourceId].status = data.status;
                                _this.toasterView('Settings Copied, Kindly Save Your Settings!', true);
                            } else {
                                _this.toasterView('Failed Copy Booking Source Settings', false);
                            }
                            _this.block = false;
                        }).catch(function (error) {
                        console.log(error);
                        _this.block = false;
                        _this.toasterView('Failed Copy Booking Source Settings', false);

                    });
                }
            },
            saveSettings() {
                var _this = this;
                _this.block = true;

                axios.post('/client/v2/save-bs-settings', {
                    "propertyInfoId": _this.propertyInfoId,
                    "bookingSourcesSettings": _this.bookingSourcesWithSettings
                }).then(function (response) {
                        if (response.data.status === true) {
                            _this.toasterView('Settings Saved!', true);

                            //update intercom data
                            updateIntercomData('payment_rules_changed');

                            if ((_this.isMasterSettings !== undefined) && (_this.isMasterSettings)) {
                                setTimeout(function() {
                                    window.location.href = '/client/v2/pms-setup-step-3';
                                }, 1000);
                            } else {
                                _this.$emit('saved', _this.propertyInfoObjectIndex, 'bookingSource');
                            }
                        } else {
                            _this.toasterView(response.data.message, false);
                        }
                        _this.block = false;
                    }).catch(function (error) {
                    if(error.response.status == 422) {
                        $.each(error.response.data.errors, function (key, value) {
                            console.log(value[0]);
                            _this.toasterView(value[0], false);
                        });
                    }else{
                        _this.toasterView('Failed to Save Booking Source Settings', false);
                    }
                    _this.block = false;
                });
            },

            beforeCheckinStatusUpdate(bs_index, e) {

                if (e.target.checked) {
                    swal.fire({
                        title: "In case of contradiction between charging & not charging policy, not charging policy will supersede.",
                        type: "warning",
                        // html: '<p style="font-size: 0.95rem;">Please attach a Credit Card</p>',
                        // showCancelButton: !0,
                        confirmButtonText: "OK"
                    });

                    this.bookingSourcesWithSettings[bs_index].return_rules.rules = [{
                        canFee: '',
                        is_cancelled: '',
                        is_cancelled_value: ''
                    }];
                }

                this.bookingSourcesWithSettings[bs_index].return_rules.afterBookingStatus = (this.bookingSourcesWithSettings[bs_index].return_rules.afterBookingStatus && this.bookingSourcesWithSettings[bs_index].return_rules.beforeCheckInStatus) ? false : this.bookingSourcesWithSettings[bs_index].return_rules.afterBookingStatus;
            },

            /**
             * Toaster View on any action
             * @param msg
             * @param status
             */
            toasterView(msg, status = false) {
                let type = (status ? 'success' : 'error');
                Vue.$toast.open({message: msg, duration: 3000, type: type, position: 'top-right',});
            },
            cantRemove() {
                swal.fire({
                    type: "info",
                    title: 'One Refund Setting Field is compulsory',
                    //text: 'One Refund Setting Field is compulsory',
                })
            },
            stopPropagationEvent($event) {
                //console.log($event);
            },
            connectAllBS(connect) {
                let state = connect ? 1 : 0;
                $.each(this.bookingSourcesWithSettings, function (key, value) {
                    value.status = state;
                });
                //this.toasterView('Booking Sources Connect Status Changed, Kindly Save settings.', true);
            },
            checkActiveStatus(index){
                let self = this;
                setTimeout(function () {
                    let bs = self.bookingSourcesWithSettings[index];
                    let rules = ['booking_deposit','booking_payment','return_rules','security_deposit'];
                    let changeStatus = false;
                    $.each(rules, function (key, value) {
                        if(bs[value]['status']){
                            changeStatus = true;
                            return false;
                        }
                    });
                    if(!changeStatus){
                        self.bookingSourcesWithSettings[index].status = false;
                    }else{
                        self.bookingSourcesWithSettings[index].status = true;
                    }
                },100);
            },
            checkSourceRules(index){
                let self = this;
                setTimeout(function () {
                    let bs= self.bookingSourcesWithSettings[index];
                    if(bs.status){
                        let rules = ['booking_deposit','booking_payment','return_rules','security_deposit'];
                        let changeStatus = false;
                        $.each(rules, function (key, value) {
                            if(bs[value]['status']){
                                changeStatus = true;
                                return false;
                            }
                        });
                        if(!changeStatus){
                            self.bookingSourcesWithSettings[index].status = false;
                            var heading = document.getElementById("headingOne"+bs.id);
                            if(heading.attributes[4].nodeValue == 'false'){
                                heading.click();
                            }
                            self.toasterView('You Must Activate At least One Payment Rule to Activate '+bs.name, false);
                        }
                    }
                },100);

            },

        },//Vue Methods End
        watch: {
            action: function () {
                this.getClientBookingSourcePreviousSettings();
            },
        },
        mounted() {
            this.getClientBookingSourcePreviousSettings();
        },

    }
</script>

<style scoped>
    /* The switch - the box around the slider */
    .switch {
        position: relative;
        display: inline-block;
        width: 30px;
        height: 19px;
    }

    /* Hide default HTML checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #D9E2EC;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 12.5px;
        width: 12.5px;
        left: 2px;
        bottom: 3px;
        background-color: white;
        -webkit-transition: .01s;
        transition: .01s;
    }

    input:checked + .slider {
        background-color: #1EAF24;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px grey;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(14px);
        -ms-transform: translateX(14px);
        transform: translateX(14px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .cross-btn {
        color: #f77474;
        background-color: transparent;
        border-radius: 70px;
        cursor: pointer;
        border: 2px solid #f77474;
        width: 26px;
        height: 26px;
        text-align: center;
        vertical-align: top;
        padding: 0px;
        font-size: 11px;
        margin: 0px 0px 6px 0px;
        float: left;
        padding-bottom: 2px;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>
