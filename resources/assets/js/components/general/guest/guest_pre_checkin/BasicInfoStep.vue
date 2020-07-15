<template>
    <div>

        <header-steps :meta="meta"></header-steps>
        <div class="gp-box gp-box-of-inner-pages">
            <read-only-mode :meta="meta"></read-only-mode>

            <div class="gp-box-content box-hv">
                <div class="gp-inset">
                    <form>
                        <div class="form-section-title">
                            <h4>Contact information</h4>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="guestEmail">Email Address</label>
                                    <input aria-describedby="emailHelp" class="form-control" id="guestEmail" type="email"
                                           v-model="step_1.email">
                                    <small class="form-text  text-danger" v-if="step_1.error_status.email">{{step_1.error_message.email}}</small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <div class="input-group">
                                        <vue-tel-input style="width: 100%; padding: 2px 0px 3px 0px;" v-bind="bindProps"
                                                       v-model="step_1.phone"></vue-tel-input>
                                    </div>
                                    <small class="form-text  text-danger" v-if="step_1.error_status.phone">{{step_1.error_message.phone}}</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-section-title">
                            <h4>Guests<span class="badge badge-grey">{{step_1.guests}}</span></h4>
                        </div>

                        <div class="form-row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="guestAdults">Adults</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <button @click.prevent="adultsDecrement()" class="btn btn-secondary"
                                                    type="button" style="width: 111px">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                        <input :value="step_1.adults"
                                               @input="inputGuest({e:$event, other:'childern'})"
                                               class="form-control text-center filter_number_input"
                                               id="guestAdults"
                                               name="adults" type="text" v-mask="'###'" value="2" style="width: 111px">
                                        <div class="input-group-append">
                                            <button @click.prevent="adultsIncrement()"
                                                    class="btn btn-secondary" type="button" style="width: 111px">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="guestChildren">Children (2-17 years)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <button @click.prevent="childDecrement()" class="btn btn-secondary"
                                                    type="button" style="width: 111px">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                        <input :value="step_1.childern"
                                               @input="inputGuest({e:$event, other : 'adults'})"
                                               class="form-control text-center filter_number_input"
                                               id="guestChildren"
                                               name="childern" type="text" v-mask="'###'" value="1" style="width: 111px">
                                        <div class="input-group-append">
                                            <button @click.prevent="childIncrement()" class="btn btn-secondary"
                                                    type="button" style="width: 111px">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small class="form-text  text-danger" v-if="step_1.error_status.adults">{{step_1.error_message.adults}}</small>
                    </form>
                </div>
            </div>
            <footer-statement></footer-statement>
        </div>
        <pre-checkin-footer @saveAndContinue="saveAndContinue" :button_text="'Save & Continue'" :show_forward_arrow="true" :booking_id="booking_id"></pre-checkin-footer>
        <!--        <div class="gp-footer"><a class="btn btn-success btn-confirm" href="javascript:void(0)" @click.prevent="saveAndContinue()">Save & Continue</a></div>-->
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>
<script>

    import {mapActions, mapMutations, mapState} from 'vuex';
    import Header from "../includes/Header";
    import FooterStatement from "./FooterStatement";

    export default {

        props: ['booking_id'],
        components: {
            Header,
            FooterStatement
        },
        mounted() {
            this.fetchStepOneData(this.booking_id);

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

            // $(document).ready(function() {
            //     $(".filter_number_input").inputFilter(function(value) {
            //         return /^\d*$/.test(value);
            //     });
            // });
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
            }
        },
        methods: {
            ...mapActions('general/',[
                'fetchStepOneData',
                'saveGuestData',
                'goToPreviousStep'
            ]),
            ...mapMutations('general/',[
                'adultsIncrement',
                'adultsDecrement',
                'childIncrement',
                'childDecrement',
                'inputGuest'
            ]),
            saveAndContinue() {
                this.step_1.error_status = {};
                this.step_1.error_message = {};

                let data = {
                    booking_info_id: this.booking_id,
                    step_1: this.step_1,
                    current_tab: 1,
                    meta: this.meta
                };
                this.saveGuestData(data);
            },
            previous() {

                let data = {
                    booking_id: this.booking_id,
                    meta: this.meta
                };

                this.goToPreviousStep(data);
            },
        },
        computed: {

            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                step_1: (state) => {
                    return state.general.pre_checkin.step_1;
                },
                meta: (state) => {
                    return state.general.pre_checkin.meta;
                }
            })
        },
        watch: {
            meta: {
                deep: true,
                immediate: true,
                handler(new_value, old_value) {
                    if (new_value.is_completed) {
                        window.location.href = this.meta.next_link;
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

    .badge-grey{
        background: #D3D3D3;
        color: #000000;
    }
</style>
