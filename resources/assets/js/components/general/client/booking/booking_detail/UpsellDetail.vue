<template>
    <div>
        <div class="mt-3 mb-4">
            <div class="card-section-title">
                <h4>Add-on Services Purchased<span class="badge badge-info">{{Object.keys(upsells).length}}</span></h4>
            </div>
            <div class="table-responsive">
                <table class="table card-table text-md table-width-fix-md table-middle">
                    <thead>
                    <tr>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Label</th>
                        <th>Fee</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="Object.keys(upsells).length == 0"> <th>No Upsell Purchased</th></tr>
                    <tr v-else v-for="upsell in upsells">
                        <td>{{upsell.type}}</td>
                        <td>{{upsell.due_date}}</td>
                        <td>{{upsell.charge_ref_no}}</td>
                        <td><span class="text-success fw-500">{{upsell.amount}}  </span></td>
                        <td>{{upsell.period.label}} {{upsell.per.label}}</td>
                        <td>{{upsell.value}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script>
    import {mapState, mapActions} from 'vuex';

    export default {
        name: 'Upsell',
        props: ['booking_id'],
        mounted() {
            this.fetchUpsells(this.booking_id);
        },
        methods :{
            ...mapActions('general/',[
                'fetchUpsells'
            ])
        },
        computed: {
            ...mapState({
                loader: function(state){
                    return state.loader;
                },
                upsells: function (state) {
                    return state.general.booking_detail.upsells;
                }
            })
        }
    }
</script>