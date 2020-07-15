<template>
    <div>

        <Checkout3DsModal calling_id="_3ds_verification" :booking_id="booking_id" :trigger="trigger_modal"
                          source="pre_checkin"></Checkout3DsModal>
        <header-steps :meta="meta"></header-steps>
        <div class="gp-box gp-box-of-inner-pages">
            <read-only-mode :meta="meta"></read-only-mode>
            <div class="gp-box-content box-hv">
                <div class="gp-inset">
                    <form>

                        <component :is="pms_prefix+'-precheckin-payment-summary'" :booking_id="booking_id"></component>

                        <div class="current-card" v-if="credit_card.card.card_type === 'VC'">
                            <p><span>Virtual Card</span></p>
                        </div>
                        <div class="current-card"
                             :class="credit_card.card.is_invalid_card ? 'customer-alert bg-white text-black' : ''"
                             v-if="credit_card.card.card_type === 'CC' && credit_card.card.cc_last_digit != '' && show_update_card_form == false">
                            <p>
                                <span>Current Credit Card</span>
                                <br>**** **** ****
                                {{credit_card.card.cc_last_digit}}
                                <span v-if="credit_card.card.is_invalid_card">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            </p>
                        </div>
                        <div class="col-12" v-if="credit_card.upsell_amount_due != 0 && !show_update_card_form">
                            <a class="btn btn-xs" @click="showCardForm()">
                                <div v-if="credit_card.card.card_available && credit_card.card.card_type === 'CC' && credit_card.card.cc_last_digit != ''">
                                    <i class="fas fa-edit"></i> Update card
                                </div>
                            </a>
                        </div>
                        <br>
                        <div v-if="show_update_card_form || credit_card.card.need_to_update_card">
                            <div class="form-section-title">
                                <h4>Update Credit Card</h4>
                            </div>
                            <component :is="credit_card.new_card.pgTerminal.cc_form_name" :pgTerminal="credit_card.new_card.pgTerminal" ref="pgTerminal"/>
                        </div>
                        <div class="" v-if="show_update_card_form && !hide_card_toggle">
                                <span @click="hideCardForm()"
                                      style="color: #ec485b;cursor: pointer;font-size: small;">
                                    <i class="fas fa-window-close"></i> Cancel
                                </span>
                        </div>
                    </form>
                    <br/>
                    <InCartAddOns :credit_card="credit_card"></InCartAddOns>
                </div>
            </div>
            <footer-statement></footer-statement>
        </div>

        <button type="button" id="trigger_3ds_verification" data-target="#_3ds_verification" data-toggle="modal"
                style="display: none">hidden for 3ds verification modal
        </button>
        <button @click="triggerModal()" style="display: none">Modal Trigger</button>


        <pre-checkin-footer @saveAndContinue="saveAndContinue"
                            :button_text="((credit_card.upsell_amount_due !== 0) ? 'Pay('+credit_card.symbol+credit_card.upsell_amount_due+')' : 'Save')+ ' & Continue'"
                            :booking_id="booking_id"
                            :show_forward_arrow="true">

        </pre-checkin-footer>

        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>
<script>

    import {mapState, mapActions, mapMutations} from 'vuex';
    import Checkout3DsModal from "../reuseables/Checkout3DsModal";
    import FooterStatement from "./FooterStatement";
    import InCartAddOns from "./InCartAddOns";


    export default {
        props: ['booking_id', 'pms_prefix'],
        components: {
            Checkout3DsModal,
            FooterStatement,
            InCartAddOns
        },
        mounted() {
            this.fetchStepFourData(this.booking_id);
        },
        data() {

            return {
                show_update_card_form: false,
                hide_card_toggle: false,
                trigger_modal: false,
                modal_component: '',
                apply_validation: false,
                allow_to_go: true
            }
        },
        methods: {
            ...mapMutations('general/',{
                'hide3dsModalBox': 'HIDE_3DS_MODAL_BOX'
            }),
            ...mapActions('general/',[
                'fetchStepFourData',
                'saveCardData',
                'goToPreviousStep',
                'fetchPaymentDetail'
            ]),
            showCardForm() {
                this.show_update_card_form = true;
                this.allow_to_go = true;
                //console.log('allow (showCardForm) : ', this.allow_to_go);
            },
            hideCardForm() {
                this.show_update_card_form = false;
                if (this.credit_card.card.need_to_update_card || this.credit_card.card.is_invalid_card || this.credit_card.card.is_payment_failed) {
                    this.allow_to_go = false;
                } else {
                    this.allow_to_go = true;
                }

                // Fetching fresh data for gateway terminal.
                this.fetchStepFourData(this.booking_id);
            },
            saveAndContinue() {

                let self = this;

                try {
                    self.$store.commit('SHOW_LOADER', null, {root: true});
                    let data = {
                        booking_info_id: this.booking_id,
                        card: this.credit_card.new_card,
                        current_tab: 5,
                        apply_validation: this.apply_validation || this.show_update_card_form,
                        allow_to_go: this.allow_to_go || this.show_update_card_form,
                        requested_by: 'pre_checkin',
                        amount_due: this.credit_card.upsell_amount_due > 0,
                        meta: this.meta
                    };
                    if ((!this.show_update_card_form && !this.credit_card.card.need_to_update_card) || this.meta.read_only_mode == 1) {
                        this.saveCardData(data);
                        self.$store.commit('HIDE_LOADER', null, {root: true});
                    }
                    else {
                        this.$refs.pgTerminal.process().then(v => {

                            if (v.status) {
                                data.card.first_name = v.first_name;
                                data.card.last_name = v.last_name;
                                data.card.payment_method = v.token;
                                data.allow_to_go = true;

                                this.saveCardData(data);
                                self.$store.commit('HIDE_LOADER', null, {root: true});
                            }
                            else {
                                toastr.error("Something went wrong. Try again.");
                                self.$store.commit('HIDE_LOADER', null, {root: true});
                            }

                        }).catch(e => {
                            toastr.error(e.message);
                            self.$store.commit('HIDE_LOADER', null, {root: true});
                        });
                    }
                }
                catch (e) {
                    self.$store.commit('HIDE_LOADER', null, {root: true});
                }
            },
            triggerModal() {
                this.trigger_modal = true;
                $('#trigger_3ds_verification').click();
            }

        },
        computed: {

            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                credit_card: (state) => {
                    return state.general.pre_checkin.credit_card_step;
                },
                meta: (state) => {
                    return state.general.pre_checkin.meta;
                }
            })
        },
        watch: {
            credit_card: {
                deep: true,
                handler(new_value, old_value) {

                    if (new_value.card.need_to_update_card || new_value.card.is_invalid_card || new_value.card.is_payment_failed) {
                        this.allow_to_go = false;
                        this.apply_validation = true;
                    }

                    if (new_value._3ds_modal) {
                        this.triggerModal();
                    }
                    this.hide3dsModalBox();
                }
            },
            meta: {
                deep: true,
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

    .sticky-footer {
        z-index: 12;
        position: fixed;
        width: 100%;
        margin: 0;
        bottom: 0;
    }

    .gp-inset {
        max-width: 47rem !important;
    }

    .customer-danger-alert {
        color: #7a1f2f;
        background-color: #fbd8de !important;
        border-color: #f9c8d1 !important;
    }
.alert-mr{
    margin-top: -0.5rem !important;
}
</style>
