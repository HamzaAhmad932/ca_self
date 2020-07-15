<template>
    <div>
        <div class="page-content" id="page_content_dashboard">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-header has-border-bottom mb-4">
                            <h1 class="page-title">Template Variables</h1>
                        </div>
                        <div class="page-body">
                            <div class="row">
                                <div class="col-md-12 col-lg-12">
                                    <div class="content-box">
                                        <!-- Content Card-->
                                        <div class="booking-card payment-paid">
                                            <div class="card-pane">
                                                <div class="row no-gutters">
                                                    <div class="col-12 col-lg-12" style="margin-bottom: 1rem;">
                                                        <div class="card-section-title">You can use following codes to
                                                            make your information objects dynamic.
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-lg-6">
                                                        <ul>
                                                            <li>[pmsPropertyId]</li>
                                                            <li>[propertyName]</li>
                                                            <li>[bookingID]</li>
                                                            <li>[pmsBookingID]</li>
                                                            <li>[currencySymbol]</li>
                                                            <li>[checkInDate]</li>
                                                            <li>[checkOutDate]</li>
                                                            <li>[bookingTotalAmount]</li>
                                                            <li>[authType]</li>
                                                            <li>[authAmount]</li>
                                                            <li>[authTotalAttempts]</li>
                                                            <li>[authDatetime]</li>
                                                            <li>[authFailedReason]</li>
                                                            <li>[SDAuthRefundDatetime]</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-6 col-lg-6">
                                                        <ul>
                                                            <li>[cardNumber]</li>
                                                            <li>[cardExpiry]</li>
                                                            <li>[chargeDescription]</li>
                                                            <li>[transactionID]</li>
                                                            <li>[GatewayReferenceNo]</li>
                                                            <li>[GatewayOrderId]</li>
                                                            <li>[chargedAtDateTime]</li>
                                                            <li>[transactionAmount]</li>
                                                            <li>[paymentProcessorName]</li>
                                                            <li>[paymentSplitType]</li>
                                                            <li>[transactionType]</li>
                                                            <li>[paymentFailedReason]</li>
                                                            <li>[chargeOn]</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-12 col-lg-12">
                                                        <div class="card-section-title">Usage Example</div>
                                                    </div>
                                                    <div class="row"
                                                         style="border-bottom:1px solid #D9E2EC; margin-top: 1rem;">
                                                        <div class="col-md-4 col-lg-4">
                                                            <div class="card-section-title"
                                                                 style="border-bottom: none;">Text with Template
                                                                Variables
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8 col-lg-8">
                                                            <p>
                                                                <strong>[paymentSplitType]</strong>
                                                                Transaction Amount
                                                                <strong>[currencySymbol] [transactionAmount]</strong>
                                                                Charged on
                                                                <strong>[chargedAtDateTime]</strong>
                                                                having Transaction ID :
                                                                <strong>[transactionID]</strong>
                                                                with <strong>[paymentProcessorName]</strong>
                                                                Payment Processor.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row" style="margin-top: 1rem;">
                                                        <div class="col-md-4 col-lg-4">
                                                            <div class="card-section-title"
                                                                 style="border-bottom: none;">Result
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8 col-lg-8">
                                                            <p>
                                                                <strong>Booking Payment Auto Collection Full</strong>
                                                                Transaction Amount
                                                                <strong>$110 </strong> Charged on
                                                                <strong>2019-04-09 00:00:10</strong>
                                                                having Transaction ID :
                                                                <strong>ch_1EwPxoBGXUPXBw5EdHz0APVk</strong>
                                                                with <strong>Stripe</strong>
                                                                Payment Processor.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Content Card-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        components: {},
        data() {
            return {}
        },
        methods: {},
    }

</script>
<style>
</style>
