<template>
    <!-- Guest Credit card Modal starts-->
    <div aria-hidden="true" aria-labelledby="guest_credit_card_modal" class="modal fade" id="guest_credit_card_modal"
         role="dialog" tabindex="-1" @click.self="close_cc_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Guest Credit Card
<!--                        <br><small><span class="text-warning"><strong style="font-weight: bolder">Note:</strong> Please do not re-enter Virtual Card!</span></small>-->
                    </h4>

                    <button @click="close_cc_modal" aria-label="Close" class="close" data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger has-icon col-md-12"
                         v-if="guest_credit_card.credit_card_available && guest_credit_card.invalid">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <p class="mb-0">
                            The credit card provided was declined, please update your card.
                        </p>
                    </div>
                    <div v-if="guest_credit_card.credit_card_available">
                        <div class="form-section-title">
                            <h4>Current Card</h4>
                        </div>
                        <div class="current-card">
                            <!-- <img class="card-type" src="img/mastercard-icon.png" alt=""> -->
                            <p><span>Card Number</span><br>****
                                &nbsp;
                                ****
                                &nbsp;
                                {{ guest_credit_card.last_4_digits }}

                            </p>
                            <p style="display: inline-block;"><span>Name</span><br>{{ guest_credit_card.full_name }}</p>
                            <p style="display: inline-block;float: right;padding-right: 2rem;"><span>Expiry</span><br>{{
                                guest_credit_card.expiry_month }}/{{ guest_credit_card.expiry_year }}</p>
                        </div>
                    </div>

                    <component 
                        :is="pgTerminal.cc_form_name" 
                        :pgTerminal="pgTerminal" 
                        ref="pgTerminal"/>
                    
                    
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal" @click="close_cc_modal"
                            id="guest_credit_card_modal_close" type="button">Cancel
                    </button>
                    <button @click.prevent="update_card()" class="btn btn-sm btn-success px-3" type="button">Update
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Guest Credit card Modal ends-->
</template>
<script>
    import {mapState} from 'vuex';

    export default {
        props: ['custom_booking_id', 'fetch_cc_info_link' ,'update_cc_info_link', 'module_prefix'],
        data: () => {
            return {
                guest_credit_card: {
                    credit_card_available: false,
                    invalid: false,
                    full_name: '',
                    last_4_digits: '',
                    expiry_month: '',
                    expiry_year: '',
                    booking_id: ''
                },
                pgTerminal: {
                    S: 'dummy-add-card',
                    is_token: false,
                    is_redirect: false

                }
            };
        },
        components: {
            //StripeAddCard,
        },
        computed: {
            ...mapState({
                booking_id: (state) => {
                    return state.general.guest_credit_card_id;
                }
            })
        },

        mounted() {
            if (this.custom_booking_id > 0) {
                this.fetchCreditCard(this.custom_booking_id)
            }
        },

        watch: {
            booking_id: {
                immediate: true,
                deep: true,
                handler(newVal, oldVal) {
                    this.guest_credit_card.booking_id = newVal;

                    if (newVal != 0) {
                        this.fetchCreditCard(newVal);
                    }

                }
            },
            custom_booking_id: {
                handler(newVal, oldVal) {
                    if (newVal != 0) {
                        this.fetchCreditCard(newVal);
                    }
                }
            },
            module_prefix: {
                handler(newVal, oldVal) {
                    if (newVal === '' || typeof newVal == "undefined") {
                        return 'general';
                    }
                }
            },

        },

        methods: {
            
            close_cc_modal() {
              this.$store.dispatch('general/guestCreditCardActiveID', 0);
              this.$refs.pgTerminal.clearCard();
            },

                fetchCreditCard(booking_id) {
                    let self = this;
                    //hide loader
                    self.$store.commit('SHOW_LOADER', null, {root: true});

                    axios({
                        url: this.getAxiosUrl('fetch-guest-cc'), //'/client/v2/fetch-guest-cc/' + booking_id

                    }).then((resp) => {

                        self.set_form_values(resp.data);

                        this.pgTerminal = resp.data.pgTerminal;

                        //hide loader
                        self.$store.commit('HIDE_LOADER', null, {root: true});


                    }).catch((err) => {
                        console.log(err);

                        //hide loader
                        self.$store.commit('HIDE_LOADER', null, {root: true});
                    });
                },

                update_card() {
                    let self = this;
                    let valid = false;
                    if (!valid) {
                        swal.fire({
                            title: "Are you sure to update credit Card?",
                            type: "warning",
                            showCancelButton: !0,
                            confirmButtonText: "Yes, Update Now!"
                        }).then(function (e) {

                            if (e.value) {

                                self.$store.commit('SHOW_LOADER', null, {root: true});
                                //console.log('before in-process');
                                self.$refs.pgTerminal.process().then(v => {


                                    if(v.status) {

                                        self.send_to_backend(v.token, v.first_name, v.last_name);

                                    } else {
                                        toastr.error("Something went wrong. Try again.");
                                    }

                                }).catch(e => {

                                    self.$store.commit('HIDE_LOADER', null, {root: true});
                                    toastr.error(e.message);
                                });



                            }

                        });
                    }
                },

            set_form_values(card_data) {

                this.guest_credit_card = {
                    credit_card_available: card_data.card_available,
                    invalid: card_data.invalid,
                    full_name: card_data.card_name,
                    last_4_digits: card_data.last_4_digits,
                    expiry_month: card_data.expiry_month,
                    expiry_year: card_data.expiry_year,
                    booking_id: this.get_booking_id(), //this.booking_id
                };
            },

            send_to_backend(payment_method, first_name, last_name) {

                    let self = this;

                    let dataObj = {
                        booking_id: this.get_booking_id(), //this.booking_id,
                        first_name: first_name,
                        last_name: last_name,
                        payment_method: payment_method
                    };

                    axios({
                            url:  this.getAxiosUrl('update-card'), //'/client/v2/update-card-by-client',
                            method: 'POST',
                            data: {data: dataObj}

                        }).then((resp) => {

                            this.$store.commit('HIDE_LOADER', null, {root: true});

                            if (resp.data.status) {

                                toastr.success(resp.data.message);

                                //assign updated values to current card box
                                self.backend_complete(resp.data.data);

                                if (this.custom_booking_id > 0) {
                                    self.$emit('cardUpdated');
                                }


                            } else {
                                toastr.error(resp.data.message);
                            }

                        }).catch((err) => {

                            this.$store.commit('HIDE_LOADER', null, {root: true});
                            toastr.error(err.message);
                            self.backend_complete(resp.data.data);
                        });

                },

                backend_complete(value) {

                this.set_form_values(value);

                if (this.booking_id > 0) {
                    //dispatching payment detail tab at booking detail page
                    this.$store.dispatch(this.module_prefix+ '/fetchPaymentsTabInformation', this.booking_id);
                }
                //reset all values and close the modal
                $('#guest_credit_card_modal_close').click();
            },

            get_booking_id(){
                return (this.custom_booking_id !== undefined && this.custom_booking_id != null && this.custom_booking_id > 0)
                    ? this.custom_booking_id
                    : this.booking_id;
            },

            getAxiosUrl(type){
                    if (type === 'update-card') {
                        return this.custom_booking_id > 0
                            ? this.update_cc_info_link
                            : '/client/v2/update-card-by-client';
                    } else if(type === 'fetch-guest-cc') {
                        return this.custom_booking_id > 0
                            ?  this.fetch_cc_info_link
                            : '/client/v2/fetch-guest-cc/' + this.booking_id;
                    }

                return '/';
            },
        }
    }
</script>
