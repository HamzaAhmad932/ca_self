<template>
    <div>
        <div class="gp-footer">
            <div class="row">
                <div class="col-md-2" >
                    <a class="btn btn-default d-none d-md-inline-block" style="font-size: 0.875rem !important;" href="javascript:void(0)" @click.prevent="previous()" v-if="meta.current_step > 0">
                        ← <span class="d-none d-sm-inline-block">Back</span>
                    </a>
                </div>
                <div class="col-md-8">
                    <a class="btn btn-success btn-confirm mx-4 my-0"
                       href="javascript:void(0)"
                       style="opacity: 0.6; cursor: not-allowed !important"
                       v-if="disabled === true">
                        {{button_text}} {{show_forward_arrow ? ' ➜' :'' }}
                    </a>
                    <a class="btn btn-success btn-confirm mx-4 my-0" v-else
                       href="javascript:void(0)"
                       @click.prevent="saveAndContinue()">
                        {{button_text}} {{show_forward_arrow ? ' ➜' :'' }}
                    </a>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-light d-block d-md-none mx-4 my-2" href="javascript:void(0)" @click.prevent="previous()" v-if="meta.current_step > 0">
                        <span class="d-block"> ← Back</span>
                    </a>
                    <!--<a class="btn btn-default" href="javascript:void(0)">
                        Skip <i class="fas fa-arrow-right"></i>
                    </a>
                    <button class="btn btn-secondary float-right">
                        Skip <i class="fas fa-arrow-right"></i>
                    </button>-->
                </div>
            </div>

<!--            <div class="row">-->
<!--                <div class="col-md-12">-->
<!--                    <div class="copy-right-area">© {{ new Date().getFullYear() }} - Powered by ChargeAutomation </div>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </div>
</template>
    <script>
        import {mapActions, mapState} from "vuex";

        export default {
            props: ['button_text', 'booking_id', 'show_forward_arrow', 'disabled'],
            methods: {
                ...mapActions([
                    'goToPreviousStep'
                ]),
                saveAndContinue() {
                    this.$emit('saveAndContinue');
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
                    meta: (state) => {
                        return state.pre_checkin.meta;
                    }
                })
            },
        }
    </script>

