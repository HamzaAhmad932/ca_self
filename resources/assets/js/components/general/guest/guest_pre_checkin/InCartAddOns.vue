<template>
    <div v-if="credit_card.upsell_amount_due !== 0">
        <div class="form-section-title">
            <h4>Add-on services</h4>
        </div>
        <div class="card-inset-table">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th scope="row">Add on services</th>
                        <td class="text-right">Day</td>
                        <td class="text-right">Person</td>
                        <td class="text-right">Unit Price</td>
                        <td class="text-right">Total</td>
                    </tr>
                    <tr v-for="upsell in credit_card.upsells">
                        <th scope="row">
                            <i class="fas fa-arrow-alt-circle-right ml-2"></i>
                            {{upsell.label}}
                        </th>
                        <td class="text-right">{{upsell.day}}</td>
                        <td class="text-right">{{upsell.person}}</td>
                        <td class="text-right">{{credit_card.symbol}}{{upsell.unit_price}}</td>
                        <td class="text-right">{{credit_card.symbol}}{{upsell.unit_total}}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
            </div>
            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th scope="row">Total</th>
                            <td class="text-right">
                                {{credit_card.symbol}}{{credit_card.upsell_total}}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Paid</th>
                            <td class="text-right">
                                {{credit_card.symbol}}{{credit_card.upsell_paid}}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Amount due</th>
                            <td class="text-right">
                                {{credit_card.symbol}}{{credit_card.upsell_amount_due}}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        props: ['credit_card'],
    }
</script>