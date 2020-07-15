<template>
    <div>
        <header-steps :meta="meta"></header-steps>
        <div class="gp-box gp-box-of-inner-pages">
            <read-only-mode :meta="meta"></read-only-mode>
            <div class="gp-box-content box-hv">
                <div class="gp-inset">
                    <div class="row" v-if="add_on_service.data.purchased.length > 0">
                        <div class="col">
                            <h3 class="lead fw-300 mb-0">Purchased Add-on Services</h3><br>
                        </div>
                    </div>
                    <div>
                        <div class="addon-item" v-for="(addon, i) in add_on_service.data.purchased"
                             :title="addon.per.label +' '+  addon.period.label +' '+addon.value + ' --- Payment Method : *****'+
                                     addon.payment_method.cc_last_4_digit + ' (' +addon.payment_method.cc_exp_month +'/'+addon.payment_method.cc_exp_year+')'">
                            <div class="addon-item-header bg-color" >
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
                                        <span style="margin-left:5px">Payment Method ****{{addon.payment_method.cc_last_4_digit}} ({{addon.payment_method.cc_exp_month}}/{{addon.payment_method.cc_exp_year}})</span>
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
                                <div class="addon-body-content bg-white">
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
                    <div class="row" v-if="add_on_service.data.available.length > 0">
                        <div class="col">
                            <h3 class="lead fw-300 mb-0">Available Add-on Services</h3><br>
                        </div>
                    </div>
                    <form>
                        <div class="addon-item" v-for="(addon, index) in add_on_service.data.available">
                            <div class="addon-item-header bg-color">
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
                                        <input type="number" min="1" @keypress="number_only"
                                               class="form-control input-sm guest-input"
                                               v-model="addon.guest_count" @input="modifyTotalPrice(index)">
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
                                <div class="addon-body-content bg-white">
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
                    <hr>
                    <div v-if="add_on_service.data.available.length > 0" class="text-center mt-4 lead fw-500">Add-on
                        Total: {{add_on_service.symbol}}{{isNaN(add_on_service.in_cart_due_amount) ? 0 :
                        add_on_service.in_cart_due_amount}}
                    </div>
                </div>
            </div>
            <footer-statement></footer-statement>
        </div>
        <pre-checkin-footer @saveAndContinue="saveAndContinue" :button_text="'Save & Continue'" :show_forward_arrow="true" :booking_id="booking_id"></pre-checkin-footer>
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>

<script>
    import {mapActions, mapState, mapMutations} from "vuex";
    import FooterStatement from "./FooterStatement";

    export default {
        props: ['booking_id'],
        components: {
            FooterStatement
        },
        data() {
            return {}
        },
        mounted() {
            this.fetchAddOnServices(this.booking_id);

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
        methods: {
            ...mapActions('general/', [
                'fetchAddOnServices',
                'saveAddonsCart',
                'goToPreviousStep',
                'setIncartAmount',
                'modifyTotalPrice'
            ]),
            previous() {
                let data = {
                    booking_id: this.booking_id,
                    meta: this.meta
                };

                this.goToPreviousStep(data);
            },
            saveAndContinue() {
                let data = {
                    booking_info_id: this.booking_id,
                    //upsell_listing_ids: this.getCartSelectedUpsellIds(), //this.in_cart_upsells,
                    current_tab: 4,
                    meta: this.meta
                };
                this.saveAddonsCart(data);
            },

            getCartSelectedUpsellIds() {
                let upsell_listing_ids = [];
                $.each(this.add_on_service.data.available, function (key, value) {
                    if (value.in_cart) {
                        upsell_listing_ids.push(value.id);
                    }
                });
                return upsell_listing_ids;
            },

            upsellSelected(e) {
                // this.total = '';
                // let total = 0;
                //
                // if ($e != null)
                //     total += e.target.dataset.price;
                //
                // $.each(this.add_on_service.data.available, function (key, value) {
                //     if(value.in_cart){
                //         total += parseFloat(value.total_price);
                //     }
                // });
                // console.log(total);
                // this.total = total;

                let price = parseFloat(e.target.dataset.price);
                if (e.target.checked) {
                    this.add_on_service.in_cart_due_amount += price;
                } else {
                    this.add_on_service.in_cart_due_amount -= price;
                }
            },

            number_only(e){
                //console.log(e.keyCode);
                //console.log(e.target.value);
                let keyCode = (e.keyCode ? e.keyCode : e.which);
                if(keyCode === 8 || ( keyCode >= 48 && keyCode <= 57 )){
                    return true;
                }
                else {
                    e.preventDefault();
                }
            }
        },
        computed: {

            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                add_on_service: (state) => {
                    return state.general.pre_checkin.add_on_service;
                },
                meta: (state) => {
                    return state.general.pre_checkin.meta;
                },
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
<style scoped>
    .guest-input {
        z-index: 2;
    }
    .bg-color {
        background-color: #fafafa;
    }
</style>