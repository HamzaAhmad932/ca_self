<template>
    <div>
        <nav class="setup-steps pmsintegration_nav" v-if="$can('accountSetup')">
            <a :class="getClass(1)" :href="((steps.step1 !== undefined) && (steps.step1 === true) ? '/client/v2/pms-setup-step-1' : 'javaScript:void(0)')"
               class="pmsintegration_nav_items">
                <div class="step-icon"><i class="fas fa-cogs"></i></div>
                <div class="setup-title">1. PMS</div>
            </a>
            <a :class="getClass(2)" class="pmsintegration_nav_items" href="/client/v2/pms-setup-step-2">
                <div class="step-icon"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="setup-title">2. Payment Rules</div>
            </a>
            <a :class="getClass(3)" class="pmsintegration_nav_items" href="/client/v2/pms-setup-step-3">
                <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                <div class="setup-title">3. Payment Gateways</div>
            </a>
            <a :class="getClass(4)" class="pmsintegration_nav_items" href="/client/v2/pms-setup-step-4">
                <div class="step-icon"><i class="fas fa-address-card"></i></div>
                <div class="setup-title">4. Guest Experience</div>
            </a>
            <a :class="getClass(5)" :href="((steps.step5 !== undefined) && (steps.step5 === true) ? '/client/v2/pms-setup-step-5' : 'javaScript:void(0)')"
               class="pmsintegration_nav_items">
                <div class="step-icon"><i class="fas fa-home"></i></div>
                <div class="setup-title">5. Activate Properties</div>
            </a>
        </nav>
    </div>
</template>

<script>
    export default {
        name: "pms_setup_steps_navbar",
        props: ['step'],
        data() {
            return {
                steps: '', //PMS Steps Completed details
            }
        },

        methods: {
            /**
             * Get List of Supported Gateways
             */
            getCompletedStepsDetail() {
                let _this = this;
                axios.post('/client/v2/pms-get-steps-completed-status')
                    .then(function (response) {
                        if (response.data.status) {
                            _this.steps = response.data.data;
                        }
                    }).catch(function (error) {
                });
            },
            getClass(step) {

                if (this.step === step)
                    return 'setup-step-item active';

                let cls = 'setup-step-item';
                let step_ = 'step' + step;

                switch (step) {
                    case 1:
                    case 5:
                        cls = ((this.steps[step_] !== undefined) && (this.steps[step_] === true)
                            ? 'setup-step-item  connected' : 'setup-step-item ');
                        break;
                    case 2:
                    case 3:
                    case 4:
                        cls = ((this.steps[step_] !== undefined) && (this.steps[step_] === true)
                            ? 'setup-step-item  connected'
                            : (((this.steps.step5 !== undefined) && (this.steps.step5 === true)) || (this.step > step))
                                ? 'setup-step-item connected skipped' : 'setup-step-item');
                        break;
                }

                return cls;
            },
        },//Methods End
        mounted() {
            this.getCompletedStepsDetail();
        },
    }
</script>

<style scoped>
    .skipped {
        background-color: lightblue;
    }

</style>