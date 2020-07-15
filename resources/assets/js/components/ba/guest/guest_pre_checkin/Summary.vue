<template>
    <div>
        <guest-credit-card
                :custom_booking_id="summary.booking_id"
                :fetch_cc_info_link="summary.links.cc_info"
                :update_cc_info_link="summary.links.update_cc_info_on"
                @cardUpdated="cardUpdated"
                v-if="summary.card_info"
        ></guest-credit-card>
        <term-and-condation-popup-modal :terms_and_conditions="summary.terms_and_conditions"
                                        v-if="summary.terms_and_conditions_found"></term-and-condation-popup-modal>

        <div class="gp-title">
            <h1 class="page-title">Your Summary</h1>
            <p class="text-muted">Please check provided data and submit</p>
        </div>
        <div class="gp-box gp-box-for-preCheckinSummaryStep">
            <div class="gp-box-steps gp-inset" style="margin:0 auto">
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
            <div class="gp-box-content">
                <div class="gp-inset">
                    <div class="row">
                        <div class="col">
                            <div class="form-section-title mt-3">
                                <h4>Review Booking Details</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-hotel"></i>
                                <dt>Reference</dt>
                                <dd>{{summary.reference}}</dd>
                            </dl>
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-door-open"></i>
                                <dt>Check-in</dt>
                                <dd>{{summary.check_in}}</dd>
                            </dl>
                        </div>
                        <div class="col">
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-coins"></i>
                                <dt>Amount</dt>
                                <dd>{{summary.amount}}</dd>
                            </dl>
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-door-closed"></i>
                                <dt>Check-out</dt>
                                <dd>{{summary.check_out}}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row mb-3" v-if="summary.contact_info">
                        <div class="col-12">
                            <div class="form-section-title mt-3">
                                <h4>Contact info</h4><a :href="summary.links.step_1" class="section-edit-link">
                                <i class="fas fa-edit"></i> Edit </a>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-if="summary.contact_info">
                        <div class="col">
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-user-circle"></i>
                                <dt>Full name:</dt>
                                <dd>{{summary.full_name}}</dd>
                            </dl>
                        </div>
                        <div class="col">
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-envelope"></i>
                                <dt>Email:</dt>
                                <dd>{{summary.email}}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row" v-if="summary.contact_info">
                        <div class="col">
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-phone-volume"></i>
                                <dt>Phone:</dt>
                                <dd>{{summary.phone}}</dd>
                            </dl>
                        </div>
                        <div class="col">
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-user-friends"></i>
                                <dt>Guests:</dt>
                                <dd>{{summary.adults}} ({{summary.childern}} child)</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row" v-if="summary.arrival_info">
                        <div class="col-12">
                            <div class="form-section-title mt-3">
                                <h4>Arrival</h4><a :href="summary.links.step_2" class="section-edit-link"> <i
                                    class="fas fa-edit"></i> Edit </a>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-if="summary.arrival_info">
                        <div class="col">
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-plane"></i>
                                <dt>Arriving by</dt>
                                <dd>
                                    {{summary.arriving_by}}
                                    <span v-if="summary.flight_no != ''"> <br> No. {{summary.flight_no}}</span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col">
                            <dl class="gp-dl dl-with-icon"><i class="dl-icon fas fa-plane-arrival"></i>
                                <dt>Estimated arrival time:</dt>
                                <dd :class="(summary.flight_no != '')?'give_extra_padding':''">{{summary.arrival_time}}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row" v-if="summary.verification">
                        <div class="col-12">
                            <div class="form-section-title mt-3">
                                <h4>Document Uploaded</h4>
                                <a :href="summary.links.step_3" class="section-edit-link">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="col-12" v-if="summary.verification">
                            <dl class="gp-dl dl-with-icon"
                                    v-for="img_data in summary.guest_images">

                                <dd style="border-bottom:none;" v-if="img_data.type =='passport'"><i class="fas fa-check-circle text-success"></i> <span>Passport/Government ID</span></dd>
                                <dd style="border-bottom:none;" v-if="img_data.type =='credit_card'"><i class="fas fa-check-circle text-success"></i> <span>Credit Card Scan</span></dd>
                                <dd style="border-bottom:none;" v-if="img_data.type =='selfie'"><i class="fas fa-check-circle text-success"></i> <span>Selfie</span></dd>
<!--                                <img :src="'/storage/uploads/guestImages/'+img_data.image"-->
<!--                                     alt="Image not found"/>-->
<!--                                <br/>-->
<!--                                <label>{{img_data.type | filter_image_label}}</label>-->
                            </dl>
                        </div>

                        <div class="col-12" v-if="summary.card_info && summary.show_payment_method">
                            <div class="form-section-title mt-3">
                                <h4>Payment Method</h4>
<!--                                <a :href="summary.links.step_5" class="section-edit-link">-->
<!--                                    <i class="fas fa-edit"></i> Edit-->
<!--                               @click="makeBookingIdReactiveForCreditCard()" </a>-->
                                <a href="javascript:void(0)" class="section-edit-link"
                                   data-target="#guest_credit_card_modal" data-toggle="modal" id="trigger_credit_card">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="col-12" v-if="summary.card_info && summary.show_payment_method">
                            <dl class="gp-dl">
                                <dt>Card Number:</dt>
                                <dd>**** **** **** {{summary.cc_last_4_digit}}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row" v-if="summary.signature_pad">
                        <div class="col-12">
                            <div class="form-section-title mt-3">
                                <h4>Digital Signature</h4>
                            </div>
                            <!--<div class="verification-document"
                                 v-if="Object.keys(summary.digital_signature).length !== 0">
                                <img :src="'/storage/uploads/guestImages/'+summary.digital_signature.image"
                                     alt="Image not found"/>
                                <br/>
                                <label>{{summary.digital_signature.type | filter_image_label}}</label>
                            </div>-->
                            <signature-pad
                                    :booking_id="summary.booking_id"
                                    :type="summary.signature_type"
                                    :signature-find="Object.keys(summary.digital_signature).length !== 0"
                                    :signature-image="Object.keys(summary.digital_signature).length !== 0 ? summary.digital_signature.image : ''"
                                    @update-signature-find="Object.keys(summary.digital_signature).length !== 0"
                                    ref="signaturePad"></signature-pad>
                        </div>
                    </div>
                    <div class="row" id="tac" v-if="summary.terms_and_conditions_found && summary.terms_and_conditions.required == '1'">
                        <div class="col-12" >
                            <div class="form-section-title mt-3">
                                <h4>Terms & Conditions</h4>
                            </div>
                            <div class="col-12">
                                <div class="custom-control custom-checkbox float-left">
                                    <input class="custom-control-input " id="customCheck1" type="checkbox"
                                           v-model="summary.terms_and_conditions_accepted">
                                    <label class="custom-control-label " for="customCheck1">I agree to the</label>
                                </div>
                                <a :href="summary.terms_link" target="_blank">&nbsp;Terms & Conditions</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer-statement></footer-statement>
        </div>
        <pre-checkin-footer
                @saveAndContinue="saveAndContinue"
                :button_text="'Save & Continue'"
                :booking_id="booking_id"
                :disabled="summary.terms_and_conditions_found && summary.terms_and_conditions.required == '1' && !summary.terms_and_conditions_accepted"
                :show_forward_arrow="true">
        </pre-checkin-footer>
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';
    import FooterStatement from "./FooterStatement";
    import StripeAddCard from "../../../general/gatewayTerminals/StripeAddCard";
    import DummyAddCard from "../../../general/gatewayTerminals/DummyAddCard";
    import GuestCreditCard from "../../../general/client/reusables/GuestCreditCard";

    export default {
        props: ['booking_id'],
        components: {
            FooterStatement,
            GuestCreditCard,
            StripeAddCard,
            DummyAddCard,
        },
        mounted() {
            this.fetchStepFiveData(this.booking_id);
            // console.log(this.summary);
        },
        methods: {
            ...mapActions([
                'fetchStepFiveData',
                'completePrecheckin',
                'goToPreviousStep',
                'saveDigitalSignature',
                'goToNextStep'
            ]),

            cardUpdated(){
                this.fetchStepFiveData(this.booking_id);
            },

            saveAndContinue() {

                if (!this.meta.is_guest && this.meta.read_only_mode == 1) {
                    this.completeThisStep();
                    return;
                }

                let self = this;
                let proceed = true;
                /** Check if Required Terms and Conditions Attached To Property  */
                if (this.summary.terms_and_conditions_found && this.summary.terms_and_conditions.required == '1') {
                    this.tac.has_required_tac = true;
                    /** If Required Terms and Conditions Are Accepted By Guest
                     * If Not Then It Will Stop any Further Processing and
                     * Throws Errors To Guest To Accept given Terms and Conditions  */
                    if (!this.summary.terms_and_conditions_found) {
                        toastr.error('You Should Accept Our Terms and Conditions');
                        document.getElementById("tac").scrollIntoView();
                        proceed = false;
                    }
                    else{
                        this.tac.is_accepted_tac = this.summary.terms_and_conditions_found;
                    }
                }
                // Proceed to next step if Attached Terms and Conditions have been accepted
                if (proceed) {
                    if (this.summary.signature_pad) {

                        try {

                            let signaturePad = self.$refs.signaturePad;

                            signaturePad.save();

                            if (Object.keys(this.summary.digital_signature).length === 0 && signaturePad.isEmpty) {
                                toastr.error('Digital Signature is required.');
                            } else if (Object.keys(this.summary.digital_signature).length !== 0 && signaturePad.isEmpty) {
                                self.completeThisStep();
                            } else if (!signaturePad.isEmpty) {

                                let dataSignature = {
                                    type: self.summary.signature_type,
                                    booking_id: self.booking_id,
                                    image: signaturePad.image,
                                    image_action: 'new',
                                    meta: {routes: {}, current_step: ''},
                                };

                                self.saveDigitalSignature(dataSignature).then(function (res) {

                                    self.completeThisStep();
                                });

                            }

                        } catch (err) {
                            toastr.error('Summary step: ' + err.name + ' : ' + err.message);
                        }

                    }
                    else {
                        self.completeThisStep();
                    }
                }

            },

            completeThisStep() {
                let data = {
                    booking_info_id: this.booking_id,
                    meta: this.meta,
                    tac: this.tac
                };

                this.completePrecheckin(data);
            },
            previous() {

                let data = {
                    booking_id: this.booking_id,
                    meta: this.meta
                };

                this.goToPreviousStep(data);
            },
        },
        filters: {
            filter_image_label: function (value) {
                let trimmed = value.replace("_", " ");
                return trimmed.charAt(0).toUpperCase() + trimmed.slice(1);
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                summary: (state) => {
                    return state.pre_checkin.summary;
                },
                tac: (state) => {
                    return state.pre_checkin.tac;
                },
                meta: (state) => {
                    return state.pre_checkin.meta;
                },
                header: (state) => {
                    return state.pre_checkin.header;
                },
            })
        },
        watch: {
            summary: {
                deep: true,
                immediate: true,
                handler(new_value, old_value) {

                    if (new_value.is_completed) {
                        window.location.href = this.summary.next_link;
                    }
                }
            }
        }
    }
</script>
<style type="text/css">
    [v-cloak] {
        display: none;
    }

    .input-group-prepend button.btn, .input-group-append button.btn {
        z-index: 0;
    }

    .gp-dl {
        text-align: left !important;
    }

    span.initial_icon {
        background-color: #334e68;
        color: #fff;
        border-radius: 100%;
        width: 32px;
        line-height: 32px;
        text-align: center;
        font-weight: 600;
        font-size: 16px;
        margin-right: 5px;
    }

    @media only screen and (min-width: 768px) {
        /* For desktop: */
        dd.give_extra_padding {
            padding-bottom: 2.3rem !important;
        }
    }
</style>
