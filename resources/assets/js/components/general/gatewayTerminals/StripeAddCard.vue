<template>
    <div>
        
        <div class="row">
            
            <div class="col col-6">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input class="form-control" id="first_name" name="first_name" placeholder="First Name" required type="text" v-model="pgTerminal.first_name">
                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.first_name">
                        <strong>{{ errorMessage.first_name }}</strong>
                    </span>
                </div>
            </div>

            <div class="col col-6">
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input class="form-control" id="last_name" name="last_name" placeholder="Last Name" required type="text" v-model="pgTerminal.last_name">
                    <span class="invalid-feedback d-block" role="alert" v-if="hasErrors.last_name">
                        <strong>{{ errorMessage.last_name }}</strong>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <!--<label for="card-element">Credit/Debit Card:</label>-->
            <div id="card-element" class="form-control"></div>
            <div id="card-errors" role="alert" style="color: darkred;"></div>
        </div>
        
        <div class="row" v-if="pgTerminal.show_authentication_button">
            <div class="col-12 col-style" style="padding: 0 10px;">
                 <div class="checkbox-toggle checkbox-choice">
                    <input 
                        :checked="pgTerminal.with3DsAuthentication" 
                        id="stripe-authentication" 
                        type="checkbox"
                        @change="changeAuthenticationLogic($event)" />
                    
                    <label for="stripe-authentication" class="checkbox-label"data-off="Off" data-on="On">
                        <span class="toggle-track"><span class="toggle-switch"></span></span>
                        <span class="toggle-title"></span>
                    </label>
                    
                </div>
                
                
                <span class="badge badge-success status-badge-align ml-2" data-placement="top"
                      data-toggle="tooltip" title="Safe card with 3DS authentication if required."
                      v-if="pgTerminal.with3DsAuthentication"><i class="fas fa-check-circle"></i>With 3DS Authentication</span>
                
                <span class="badge badge-danger status-badge-align ml-2" data-placement="top" data-toggle="tooltip"
                      title="Save card without 3DS authentication even if its required."
                      v-else><i class="fas fa-exclamation-triangle"></i>Without 3DS Authentication</span>
                      

            </div>
        </div>
        
    </div>
</template>

<script>
    export default {
            props: {
                pgTerminal: {
                    cc_form_name: 'stripe-add-card', //'dummy-add-card',
                    is_token: false,
                    is_redirect: false,
                    redirect_link: '',
                    public_key: '',
                    client_secret: '',
                    account_id: '',
                    first_name: '',
                    last_name: '',
                    booking_id: '',
                    with3DsAuthentication: true,
                    show_authentication_button: false
                }
            },
            data() {
                return {
                    stripe: null,
                    elements: null,
                    card: null,
                    form: null,
                    displayError: '',
                    style: {
                        base: {
                            color: '#32325d',
                            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                            fontSmoothing: 'antialiased',
                            fontSize: '16px', 
                            '::placeholder': {color: '#aab7c4'}
                        },
                        invalid: {color: '#fa755a', iconColor: '#fa755a'}
                    },
                    hasErrors: {
                        first_name: false,
                        last_name: false,
                    },
                    errorMessage: {
                        first_name: '',
                        last_name: '',
                    },
                };
            },
            
            created() {},
            
            mounted() {
                this.displayError = document.getElementById('card-errors');
                this.init();
            },

            computed: {},
            
            methods: {
                
                init() {
                    
                    let self = this;


                    if(this.pgTerminal.account_id !== '')
                        this.stripe = Stripe(this.pgTerminal.public_key, {stripeAccount: this.pgTerminal.account_id});
                    else
                        this.stripe = Stripe(this.pgTerminal.public_key);

                    this.elements = this.stripe.elements();

                    this.card = this.elements.create('card', {style: this.style});

                    this.card.mount('#card-element');

                    this.form = document.getElementById('payment-form');

                    this.card.addEventListener('change', function (event) {
                        
                        if (event.error) {
                            self.displayError.textContent = event.error.message;
                            throw new Error(event.error.message);
                        } else {
                            self.displayError.textContent = '';
                        }
                        
                        self.validate();
                        
                    });
                },
                
                validate() {
                    let _this = this;

                    _this.hasErrors = {
                        first_name: false,
                        last_name: false,
                    };

                    _this.errorMessage = {
                        first_name: '',
                        last_name: '',
                    };

                    let he = _this.hasErrors;
                    let em = _this.errorMessage;
                    
                    if(this.pgTerminal.first_name.length === 0) {
                        //this.displayError.textContent += ' Please enter First Name. ';
                        he.first_name=true;
                        em.first_name='Please enter First Name.';
                        throw new Error('Please enter First Name.');
                        return false;
                    }
                    
                    if(this.pgTerminal.last_name.length === 0) {
                        //this.displayError.textContent += ' Please enter Last Name. ';
                        he.last_name=true;
                        em.last_name='Please enter Last Name.';
                        throw new Error('Please enter Last Name.');
                        return false;
                    }
                    
                    return true;
                    
                },
                
                process() {
                    
                    let pgData = {
                        status: false,
                        token: '',
                        first_name: this.pgTerminal.first_name,
                        last_name: this.pgTerminal.last_name
                    };
                    
                    if(!this.validate())
                        return pgData;
                    
                    if(this.pgTerminal.with3DsAuthentication)
                        pgData = this.withAuthentication(pgData);
                    else
                        pgData = this.withoutAuthentication(pgData);
                    
//                    this.card.clear();
                    
                    return pgData;
                    
                },
                
                async withAuthentication(pgData) {
                    
                    /* https://stripe.com/docs/payments/save-and-reuse */
                    
                    let dataToSend = {
                        payment_method: {
                            card: this.card,
                            billing_details: {
                                name: this.pgTerminal.first_name + " " + this.pgTerminal.last_name
                            }
                        }
                    };
                    
                    let confirmCard = await this.stripe.confirmCardSetup(this.pgTerminal.client_secret, dataToSend);
                    this.checkResponseForError(confirmCard);
                    pgData.token = confirmCard.setupIntent.payment_method;
                    pgData.status = true;
                    return pgData;
                    
                },
                
                async withoutAuthentication(pgData) {
                    
                    /* https://stripe.com/docs/payments/save-card-without-authentication */
                    
                    let dataToSend = {
                        type: 'card',
                        card: this.card,
                        billing_details: {
                          name: this.pgTerminal.first_name + " " + this.pgTerminal.last_name
                        }
                      };
                      
                    let createPaymentMethod = await this.stripe.createPaymentMethod(dataToSend);
                    this.checkResponseForError(createPaymentMethod);
                    pgData.token = createPaymentMethod.paymentMethod.id;
                    pgData.status = true;
                    return pgData;
                    
                },
                
                checkResponseForError(result) {
                    
//                    console.log(result);
                    
                    if (result.error) {
                        
                        if(result.error.code === 'setup_intent_unexpected_state') {
                            toastr.error("Please refresh your page and try again. If problem persists please contact support.");
                        }
                        
                        this.displayError.textContent = result.error.message;
                        let error = new Error(result.error.message);
                        error.code = 'page-reload';
                        throw error;

                    } 
                },
                
                changeAuthenticationLogic(event) {
                 this.pgTerminal.with3DsAuthentication = !this.pgTerminal.with3DsAuthentication;
                },
                
                clearCard() {
                    if(this.card !== null)
                        this.card.clear();
                }
                
            }
            
    }
</script>