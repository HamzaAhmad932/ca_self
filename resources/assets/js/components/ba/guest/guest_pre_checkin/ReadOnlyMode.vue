<template>
    <div>
        <div v-if="!meta.is_guest">
            <div class="gp-inset" style="margin:0 auto">

                <div class="alert bg-dark text-white m-0" v-if="meta.read_only_mode == 1">
                    You are in View-Only Mode. This mode allows you to go through the pre-checkin without completing it.
                    If you want to complete the pre-checkin for your guest <a href="javascript:void(0)"
                                                                              class="alert-link"
                                                                              @click.prevent="changeMode({'booking_id': meta.booking_id, 'mode_value': 0})">CLICK
                    HERE</a>.
                </div>

                <div class="alert alert-warning m-0" v-if="meta.read_only_mode == 0">
                    You are in Live Mode. If you want to return to View-Only Mode <a href="javascript:void(0)"
                                                                                           class="alert-link"
                                                                                           @click.prevent="changeMode({'booking_id': meta.booking_id, 'mode_value': 1})">CLICK
                    HERE</a>.
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {mapActions} from "vuex";

    export default {
        props: ['meta'],
        methods: {
            ...mapActions([
                'changeMode'
            ])
        }
    }
</script>
<style scoped>
</style>
