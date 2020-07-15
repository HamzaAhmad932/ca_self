let actions = {

    makeBookingIdReactiveForAdditionalCharge(booking_id, is_payment_gateway_found, is_credit_card_available) {

        if (!is_payment_gateway_found) {
            swal.fire({
                title: "No payment gateway added!",
                type: "warning",
                html: '<p style="font-size: 0.95rem;">Click <a href="/client/v2/pms-setup-step-3">here</a> to add payment gateway.</p>',
                // showCancelButton: !0,
                confirmButtonText: "OK"
            });
            return;
        }
        if (!is_credit_card_available) {

            swal.fire({
                title: "Payment method not found.",
                type: "warning",
                html: '<p style="font-size: 0.95rem;">Please attach a Credit Card</p>',
                // showCancelButton: !0,
                confirmButtonText: "OK"
            });
            return;
        }

        $('#trigger_additional_charge').click();
        this.$store.dispatch('additionalChargeActiveId', booking_id);

    },
};


export default actions;