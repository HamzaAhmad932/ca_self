<template>
    <div>
        <div class="gp-box text-center" style="padding-top: 0 !important;">
            <div class="py-2 pb-1 mb-1 gp-inset">
                <h3 class="mb-0">Welcome {{step_0.guest_name}} </h3>
                <div class="text-md text-muted">Please start Pre Check-in</div>
            </div>

            <div class="gp-box-steps gp-inset" style="margin:0 auto; background: #FFF !important;">
                <div class="gp-property">
                    <div class="gp-property-img">
                        <img v-if="header.property_initial==''" :src="header.property_logo">
                        <div v-else class="display-initials-wrapper s6">
                            <span class="initial_icon">
                                {{header.property_initial}}
                            </span>
                        </div>
                    </div>
                    <div class="gp-property-legend">
                        <p class="mb-0 font-weight-bold" style="font-size: 18px;">{{header.property_name}}</p>
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
            <div class="gp-box-content box-hv">
                <div class="gp-inset">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <dl class="dl-with-icon"><i class="dl-icon fas fa-hotel"></i>
                                <dt>Reference</dt>
                                <dd>{{step_0.reference}}</dd>
                            </dl>
                            <dl class="dl-with-icon"><i class="dl-icon fas fa-door-open"></i>
                                <dt>Check-in</dt>
                                <dd>{{step_0.checkin_date}}</dd>
                            </dl>
                            <dl class="dl-with-icon"><i class="dl-icon fas fa-plane-arrival"></i>
                                <dt>Arrival time</dt>
                                <dd>{{step_0.arrival_time}}</dd>
                            </dl>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <dl class="dl-with-icon"><i class="dl-icon fas fa-coins"></i>
                                <dt>Amount</dt>
                                <dd>{{step_0.amount}}</dd>
                            </dl>
                            <dl class="dl-with-icon"><i class="dl-icon fas fa-door-closed"></i>
                                <dt>Check-out</dt>
                                <dd>{{step_0.checkout_date}}</dd>
                            </dl>
                            <dl class="dl-with-icon"><i class="dl-icon fas fa-user-friends"></i>
                                <dt>Guests</dt>
                                <dd>{{step_0.guest}}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <footer-statement></footer-statement>
        </div>

        <pre-checkin-footer @saveAndContinue="saveAndContinue" :button_text="'Get Started'" :show_forward_arrow="true"></pre-checkin-footer>

        <!--Loader-->
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>
<script>

    import {mapActions, mapState} from 'vuex';
    import FooterStatement from "./FooterStatement";

    export default {
        props: ['booking_id'],
        components: {
            FooterStatement
        },
        mounted() {
            this.fetchGuestDetail(this.booking_id);
        },
        methods: {
            ...mapActions('ba/', [
                'fetchGuestDetail',
            ]),
            ...mapActions('general/', [
                'goToNextStep',
            ]),
            saveAndContinue() {
                let data = {
                    booking_id: this.booking_id,
                    meta: this.meta
                };

                this.goToNextStep(data);
            }
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                step_0: (state) => {
                    return state.ba.pre_checkin.step_0;
                },
                header: (state) => {
                    return state.ba.pre_checkin.header;
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
</style>
