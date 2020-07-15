<template>
    <div>
        <div aria-hidden="true" aria-labelledby="bookingSyncTimePopupModalLabel" class="modal fade" id="booking-sync-time-popup-modal"
             role="dialog" style="display:none;" tabindex="-1">
            <div class="modal-dialog" role="document" style="margin-top:10%">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingSyncTimePopupModalLabel">
                            <i class="fa fa-sync" title="Just select a date you want bookings to sync from."></i> Sync Previous Bookings from PMS
                        </h5>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button" id="sync-booking-modal-dismiss">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-row">
                                    <div class="form-group col-12">
                                        <label for="datepicker-trigger-sync">Date From</label>
                                        <input aria-describedby="sync_time" class="form-control form-control-sm" id="datepicker-trigger-sync"
                                               placeholder="Enter sync date" type="text" v-model="sync_time">
                                            <AirbnbStyleDatepicker
                                                    :closeAfterSelect="true"
                                                    :date-one="sync_time"
                                                    :mode="'single'"
                                                    :trigger-element-id="'datepicker-trigger-sync'"
                                                    @date-one-selected="val => {sync_time = val }"
                                                    :months-to-show="1"
                                            ></AirbnbStyleDatepicker>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div role="alert" class="alert alert-warning mb-1"><i  class="fas fa-exclamation-circle"></i>
                            You have an opportunity to sync your old bookings from PMS to ChargeAutomation.
                            Just select a date you want bookings to sync from.
                        </div>
                    </div>
                    <div class="modal-footer" style="justify-content:center">
                        <button class="btn btn-success" type="button" @click="save()"><i class="fa fa-sync"></i> Sync Now</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Block UI Loader-->
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
        <!-- End Block UI-->
    </div>
</template>
<script>
    import {mapActions, mapState} from "vuex";
    export default {
        mounted() {
            this.canAddSyncTime();
        },
        data() {
            return {
                sync_time: '',
            }
        },

        methods: {
            save(){
                this.saveSyncTime({'sync_date':this.sync_time});
            },
            ...mapActions('ba/', [
                'canAddSyncTime',
                'saveSyncTime',
            ]),
        },
        computed: {
            ...mapState({
                loader: (state) => {
                    return state.loader;
                },
                can_sync_booking: (state) => {
                    return state.booking.can_sync_booking;
                },
            }),
        },
    }
</script>