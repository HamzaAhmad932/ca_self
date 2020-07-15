<template>
    <div>
        <div class="tp-header" style="padding:0 !important;">
            <h3 class="mb-0 py-2 prop-info-at-Pre-check-in">{{ meta.property_name }}</h3>

            <div class="gp-box-steps gp-progress" style="display: flex; margin: 0px;padding:0.65rem !important;">
                <a :class="'gp-step '+(meta.current_step == r.default_step_num ? 'active' :  (meta.current_step > r.default_step_num ? 'step-completed' : ''))"
                   v-for="r in meta.routes"
                   v-if="r.show_step">
                    <span class="step-check check-icon-portion">
                        <em><i :class="r.icon"></i></em>
                    </span>
                    <span class="d-none d-sm-block">{{r.title}}</span>
                </a>
            </div>
        </div>
    </div>
</template>
<script>
    import {mapState, mapActions} from "vuex";

    export default {
        props: ['meta'],
        methods: {
            ...mapActions('general/',[
                'changeMode'
            ])
        }
    }
</script>
<style scoped>

    .gp-sub-header {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
        background: #d9e2ec !important;
        text-align: center;
    }

    a.gp-step {
        overflow: visible !important;
    }

    .gp-step.active:after {
        display: block;
        content: '';
        position: absolute;
        left: 50%;
        margin-left: -12px;
        width: 0;
        height: 0;
        border-left: 12px solid transparent;
        border-right: 12px solid transparent;
        border-bottom: 12px solid #fff;
        bottom: -16px;
    }


</style>
