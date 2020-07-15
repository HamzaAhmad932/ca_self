<template>
    <div>
        <header-steps :meta="meta"></header-steps>
        <div class="gp-box gp-box-of-inner-pages">
            <read-only-mode :meta="meta"></read-only-mode>
            <div class="gp-box-content box-hv">
                <div class="gp-inset">
                    <form>
                        <div class="form-group">
                            <label for="guestArrivalMethod">Arriving by</label>
                            <select @change="toggleDynamicInputs()" class="custom-select" id="guestArrivalMethod"
                                    v-model="step_2.arriving_by">
                                <option value="">Select option</option>
                                <option value="Car">Car</option>
                                <option value="Plane">Plane</option>
                                <option value="Bus">Bus</option>
                                <option value="Ship">Ship</option>
                                <option value="Train">Train</option>
                                <option value="Other">Other</option>
                            </select>
                            <small class="form-text  text-danger" v-if="step_2.error_status.arriving_by">{{step_2.error_message.arriving_by}}</small>
                        </div>
                        <div class="form-group" v-if="show_plane || step_2.arriving_by == 'Plane'">
                            <label for="flightNumber">Flight number</label>
                            <input
                                    class="form-control"
                                    id="flightNumber"
                                    placeholder="Flight number"
                                    type="text"
                                    v-model="step_2.plane_number"/>
                            <small class="form-text  text-danger" v-if="step_2.error_status.plane_number">{{step_2.error_message.plane_number}}</small>
                        </div>
                        <div class="form-group" v-if="show_other || step_2.arriving_by == 'Other'">
                            <label for="other">Other Details</label>
                            <input
                                    class="form-control"
                                    id="other"
                                    type="text"
                                    v-model="step_2.other"/>
                            <small class="form-text  text-danger" v-if="step_2.error_status.other">{{step_2.error_message.other}}</small>
                        </div>
                        <div class="form-group">
                            <label>Estimated Arrival Time</label>
                                <select class="custom-select bg-light"
                                        v-model="step_2.arrival_time">
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
                                <small class="form-text text-danger" v-if="step_2.error_status.arrival_time">{{step_2.error_message.arrival_time}}</small>
                        </div>
                    </form>
                </div>
            </div>
            <footer-statement></footer-statement>
        </div>

        <pre-checkin-footer @saveAndContinue="saveAndContinue" :button_text="'Save & Continue'" :show_forward_arrow="true" :booking_id="booking_id"></pre-checkin-footer>

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
            this.fetchStepTwoData(this.booking_id);
        },
        data() {
            return {
                show_plane: false,
                show_other: false
            }
        },
        methods: {
            toggleDynamicInputs: function () {
                this.show_plane = this.step_2.arriving_by === 'Plane';
                this.show_other = this.step_2.arriving_by === 'Other';
            },

            saveAndContinue() {
                let data = {
                    booking_info_id: this.booking_id,
                    step_2: this.step_2,
                    current_tab: 2,
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

            ...mapActions([
                'fetchStepTwoData',
                'saveGuestData',
                'goToPreviousStep'
            ])
        },
        computed: {

            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                step_2: (state) => {
                    return state.pre_checkin.step_2;
                },
                meta: (state) => {
                    return state.pre_checkin.meta;
                }
            })
        },
        watch: {
            step_2: {
                deep: true,
                immediate: true,
                handler(new_value, old_value) {

                    if (new_value.is_completed) {
                        window.location.href = this.step_2.next_link;
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
