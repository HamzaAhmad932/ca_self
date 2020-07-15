<template>
    <div>
        <!----Modals Components----------->
        <chat-panel-right calling_id="chat_panel_right"></chat-panel-right>
        <a data-target="#chat_panel_right" ref="openChatPanel" style="display: none">-</a>

        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">

                        <div aria-hidden="true" aria-labelledby="confirmationModal" class="modal fade" id="confirmationModal"
                             role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Resend Confirmation Email</h4>
                                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                            <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="form-group">
                                                <label for="emailMessage">Message:</label>
                                                <textarea class="form-control" id="emailMessage">Type message</textarea>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                                                type="button">Cancel
                                        </button>
                                        <button class="btn btn-sm btn-success px-3" type="button">Send Message</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div aria-hidden="true" aria-labelledby="uploadDoc" class="modal fade" id="uploadDoc" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Add New Document</h4>
                                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                            <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="form-group">
                                                <label class="mb-0" for="uploadFile">Upload a government issued
                                                    document:</label><small class="form-text text-muted mb-2">Passport,
                                                ID or Driver License</small>
                                                <div class="custom-file">
                                                    <input class="custom-file-input" id="uploadFile" type="file"/>
                                                    <label class="custom-file-label" for="uploadFile">Choose
                                                        file</label><small class="form-text text-muted mb-2">Max file
                                                    size: 5MB (JPG or PNG)</small>
                                                </div>
                                            </div>
                                        </form>
                                        <hr/>
                                        <a class="btn btn-sm btn-outline-secondary" href=""> <i
                                                class="fas fa-plus-circle"> </i> Add More</a>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                                                type="button">Cancel
                                        </button>
                                        <button class="btn btn-sm btn-success px-3" type="button">Upload Files</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div aria-hidden="true" aria-labelledby="addCard" class="modal fade" id="addCard" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Add New Payment Method</h4>
                                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                            <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="alert alert-primary text-center">
                                                <p class="mb-0">This card will be charged on <strong> May 17,
                                                    2019</strong></p>
                                            </div>
                                            <div class="form-group">
                                                <label for="nameOnCard">Name on card:</label>
                                                <input class="form-control" id="nameOnCard" placeholder="Name on Card"
                                                       type="text"/>
                                            </div>
                                            <div class="form-group">
                                                <label for="cardNumber">Card number:</label>
                                                <input class="form-control" id="cardNumber" placeholder="Card Number"
                                                       type="text"/>
                                            </div>
                                            <div class="form-group">
                                                <label for="dateExpiry">Date expiry:</label>
                                                <div class="form-row">
                                                    <div class="col-8">
                                                        <select class="custom-select" id="dateExpiry">
                                                            <option>01 - January</option>
                                                            <option>02 - February</option>
                                                            <option>03 - March</option>
                                                            <option>04 - April</option>
                                                            <option>05 - May</option>
                                                            <option>06 - June</option>
                                                            <option>07 - July</option>
                                                            <option>08 - August</option>
                                                            <option>09 - September</option>
                                                            <option>10 - October</option>
                                                            <option>11 - November</option>
                                                            <option>12 - December</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <select class="custom-select" id="dateExpiryYear">
                                                            <option>2019</option>
                                                            <option>2020</option>
                                                            <option>2021</option>
                                                            <option>2022</option>
                                                            <option>2023</option>
                                                            <option>2024</option>
                                                            <option>2025</option>
                                                            <option>2026</option>
                                                            <option>2027</option>
                                                            <option>2028</option>
                                                            <option>2029</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="securityCode">Security code (CVV/CVC):</label>
                                                <input class="form-control" id="securityCode" placeholder="3-digits on your card"
                                                       type="text"/>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                                                type="button">Cancel
                                        </button>
                                        <button class="btn btn-sm btn-success px-3" type="button">Save Card</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div aria-hidden="true" aria-labelledby="additionalCharge" class="modal fade" id="additionalCharge"
                             role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Charge Additional Payment</h4>
                                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                            <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="form-group">
                                                <label for="chargeName">Payment Name:</label>
                                                <input class="form-control" id="chargeName" placeholder="Charge Name" type="text"/>
                                            </div>
                                            <div class="form-group">
                                                <label for="chargeAmount">Amount (USD):</label>
                                                <input class="form-control" id="chargeAmount" placeholder="Charge Amount" type="text"/>
                                            </div>
                                            <div class="form-group">
                                                <label for="chargeDescription">Description:</label>
                                                <input class="form-control" id="chargeDescription" placeholder="Optional" type="text"/>
                                            </div>
                                            <div class="form-group">
                                                <label for="chargeDate">Payment Date:</label>
                                                <input class="form-control" id="chargeDate" placeholder="Charge Date" type="date"/>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                                                type="button">Cancel
                                        </button>
                                        <button class="btn btn-sm btn-success px-3" type="button">Save Payment</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div aria-hidden="true" aria-labelledby="refundModal" class="modal fade" id="refundModal"
                             role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Make Refund</h4>
                                        <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                                            <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="form-group">
                                                <label for="refundAmount">Refund Amount (USD):</label>
                                                <input class="form-control" id="refundAmount" placeholder="Refund Amount"
                                                       type="text"/>
                                            </div>
                                            <div class="form-group">
                                                <label for="refundNote">Description (optional):</label>
                                                <textarea class="form-control" id="refundNote" rows="3"></textarea>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-secondary mr-auto px-3" data-dismiss="modal"
                                                type="button">Cancel
                                        </button>
                                        <button class="btn btn-sm btn-success px-3" type="button">Refund Money</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="page-header has-border-bottom mb-4">
                            <div class="row">
                                <div class="col-6">
                                    <a class="text-muted d-block text-md mb-1" href="/client/v2/bookings">← <span class="hidden-xs">Back to </span> all bookings</a>
                                </div>
                                <div class="col-6">
                                    <a v-if="header.chat_active"
                                       id="pop_chat_button"
                                       @click.prevent="makeBookingIdReactiveForCommunication(header.booking_info_id, header.pms_booking_id)"
                                       class="btn btn-success btn-sm text-white chat-open chat-btn-v2 float-right mb-3"
                                       data-target="#chat_panel_right">
                                        <i class="far fa-comment"> </i>
                                        <span>Chat</span>
                                    </a>
                                </div>
                            </div>
                            <h1 class="page-title">ID-{{header.pms_booking_id}}</h1>
                            <span :class="header.badge_color">{{header.pms_booking_Status}}</span>
                            <div class="booking-pagination">
                                <a :href="header.previous" class="btn btn-outline-secondary btn-sm"
                                   v-if="header.previous != null">← <span class="hidden-xs">Prev</span></a>
                                <a :href="header.next" class="btn btn-outline-secondary btn-sm"
                                   v-if="header.next != null"> <span class="hidden-xs">Next </span>→ </a>
                            </div>
                        </div>
                        <div class="page-body">
                            <div class="content-box">
                                <div class="content-box-body bg-light p-0">
                                    <div class="booking-legend">
                                        <div class="booking-legend-content">
                                            <h3>{{header.guest_name}}</h3>
                                            <span class="badge badge-danger">{{header.id_verification_status}}</span>
                                        </div>
                                    </div>
                                    <div class="booking-details-tabs">
                                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                                            <li class="nav-item" v-for="tab in tab_section.tabs[prefix]">
                                                <a
                                                        :class="{'active': tab_section.current_tab.component_name === tab.component_name}"
                                                        @click.prevent="tab_section.current_tab = tab"
                                                        aria-selected="true"
                                                        class="nav-link"
                                                        role="tab"
                                                        :id="'tab_'+tab.component_name"
                                                >
                                                    <span class="mt-sm-15">{{tab.label}}</span>
                                                    <span class="responsive-icon-tab"><i :class="tab.icon"></i></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content" id="myTabContent">
                                            <div class="tab-pane fade show active" role="tabpanel">
                                                <component :booking_id="booking_id" v-bind:is="toggleTab"></component>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <BlockUI :html="loader.html" :message="loader.msg" v-if="loader.block === true"></BlockUI>
    </div>
</template>

<script>

    import {mapActions, mapState, mapGetters, mapMutations} from 'vuex';

    export default {
        props: ['booking_id', 'pms_prefix'],
        created() {

            if(typeof this.pms_prefix != "undefined"){
                this.setPmsPrefix(this.pms_prefix);
            }
        },
        mounted() {
            this.fetchBookingDetailHeader(this.booking_id);
            this.openUpsellTabFun();
            this.openChat();

            // OPEN UPSELL TAB
            if (window.location.hash.length > 0 && window.location.hash === '#upsell')
                this.openUpsellTab();
        },
        data() {
            return {
                img: '/storage/uploads/user_images/no_image.png',
            }
        },
        methods: {
            makeBookingIdReactiveForCommunication(booking_id, pms_booking_id) {
                let payload = {
                    booking_id,
                    pms_booking_id
                };
                this.$refs.openChatPanel.click();
                this.$store.dispatch('general/booking_id_action_chat', payload);
            },
            ...mapActions('general/', [
                'fetchBookingDetailHeader'
            ]),

            ...mapMutations('general/', {
                openUpsellTab: 'OPEN_UPSELL_TAB',
                setPmsPrefix: 'SET_PMS_PREFIX',
            }),
            openUpsellTabFun() {
                let self = this;
                var upsell_purchased = '';
                var url = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                    if (key == 'upsell-purchased' && value == 'upsell') {
                        setTimeout(function(){
                            document.getElementById("tab_Upsell").click();
                        }, 1000);
                    }
                });
            },
            openChat() {
                window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                    console.log(key +' -- '+ value);
                    if (key == 'chat' && value == 'open') {
                        setTimeout(function() {
                            document.getElementById("pop_chat_button").click();
                        }, 2000);
                    }
                });
            },
        },
        filters: {
            tab_title_splitting_filter: function (value) {
                return value.replace(/([A-Z])/g, ' $1').trim();
            }
        },
        computed: {
            ...mapGetters('general/',[
                'toggleTab'
            ]),

            ...mapState({
                loader: function (state) {
                    return state.loader;
                },
                header: function (state) {
                    return state.general.booking_detail.header;
                },
                tab_section: function (state) {
                    return state.general.booking_detail.tab_section;
                },
                prefix: function (state) {
                    return state.general.pms_prefix;
                }
            })

        }
    }
</script>
<style>
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 80%;
        color: #EB3B5A;
    }
</style>