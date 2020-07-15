<template>
    <div>
        <div class="modal fade" :id="calling_id" tabindex="-1" role="dialog" :aria-labelledby="calling_id" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Verfiy 3D secure card</h4>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                    </div>
                    <div class="modal-body" v-if="_3ds.public_key != ''">
                        <checkout-3ds
                                :guest-name = "_3ds.guest_name"
                                :is-paid = "_3ds.isPaid ? 1 : 0"
                                :f-name = "_3ds.fName"
                                :l-name = "_3ds.lName"
                                :email = "_3ds.email"
                                :phone = "_3ds.phone"
                                :guest-portal-link = "source == 'guest_portal' ? '' : _3ds.meta.next_link"
                                :client-secret = "_3ds.client_secret"
                                :button-text = "_3ds.button_text"
                                :public-key = "_3ds.public_key"
                                :account-id = "_3ds.account_id"
                                :b-info = "_3ds.b_info"
                                :id = "_3ds.id"
                                :type = "_3ds.type"
                                :checkout-post-url = "_3ds.checkout_post_url"
                                :precheckin="true"
                                :postal_code = "_3ds.postal_code"
                                :country = "_3ds.country"
                                :address_line1 = "_3ds.address_line1"
                                :city = "_3ds.city"
                                :state = "_3ds.state"
                        ></checkout-3ds>
                    </div>
                </div>
                <div class="modal-footer" style="display: none;">
                    <button class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                            id="force_close_3ds_modal" type="button">Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import {mapState, mapActions} from "vuex";

    export default {

        props: ['calling_id', 'booking_id', 'trigger', 'source'],
        methods: {
            ...mapActions([
                'fetchPaymentDetail'
            ])
        },
        mounted() {
            //console.log(this._3ds);
        },

        computed: {

            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                _3ds: (state) => {
                    return state.pre_checkin._3ds;
                },
                meta: (state)=> {
                    return state.pre_checkin.meta;
                }
            })
        },

        watch:{
            trigger: {
                deep: true,
                handler(new_value, old_value){
                    if(new_value){
                        this.fetchPaymentDetail({'id': this.booking_id, 'meta': this.meta});
                    }
                }
            }
        }
    }
</script>