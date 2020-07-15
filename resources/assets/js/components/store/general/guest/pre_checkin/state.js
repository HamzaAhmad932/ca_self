let state = {

    header: {
        property_name: '',
        property_logo: {
            property_initial: '',
            property_image: '',
            is_initial: false
        },
        booking_source: '',
        booking_source_logo: {
            booking_source_initial: '',
            booking_source_image: '',
            is_initial: false
        },
        external_link: '',
    },

    meta: {
        current_step: 0,
        to_be_completed_steps: 0,
        routes: [

            {
                title: '',
                count: 0,
                default_step_name: "",
            }
        ],
        is_credit_card_scan_required: true,
        is_passport_scan_required: true,
        next_link: '',
        is_completed: false
    },

    // step_0: {
    //     guest_name: '',
    //     property: {
    //         name: '',
    //         logo: '/storage/uploads/property_logos/no_image.png'
    //     },
    //     booking_source: {
    //         name: 'Expedia',
    //         logo: '/storage/uploads/booking_souce_logo/expedia-logo.png'
    //     },
    //     amount: '',
    //     arrival_time: '',
    //     guest: '',
    //     checkin_date: 'Nov 11, 2019',
    //     checkout_date: 'Nov 15, 2019',
    //     reference: '15403565',
    //     pre_step: '',
    //     next_step: '',
    //     next_link: '',
    //     is_completed: false
    // },

    step_1: {
        email: '',
        phone: '',
        guests: 0,
        adults: 0,
        childern: 0,
        status: false,
        error_message: {
            email: '',
            phone: '',
            adults: ''
        },
        error_status: {
            email: false,
            phone: false,
            adults: false
        },
    },

    step_2: {
        arriving_by: '',
        plane_number: '',
        other: '',
        arrival_time: '',
        status: false,
        error_message: {
            arriving_by: '',
            plane_number: '',
            arrival_time: '',
            other: ''
        },
        error_status: {
            arriving_by: false,
            plane_number: false,
            arrival_time: false,
            other: false
        },
    },

    step_3: {
        images: [],
        next_link: '',
        is_completed: false,
    },
    guest_images_status: {},

    add_on_service: {
        data: {
            purchased: [],
            available: []
        },
        in_cart_due_amount: 0,
    },
    credit_card_step: {

        _3ds_modal: false,
        upsells: [],
        upsell_total: 0,
        upsell_paid: 0,
        upsell_amount_due: 0,
        symbol: '$',
        payments:[],
        card: {
            card_type: '',
            cc_last_digit: '',
            card_available: false,
            expiry: '',
            sd_auth_present: false,
            sd_msg: '',
            need_to_update_card:true,
        },
        new_card: {
            name: '',
            number: '',
            expiry: '',
            cvv: '',
            pgTerminal: {
                    cc_form_name: 'dummy-add-card', //'dummy-add-card',
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
        error_message: {
            name: '',
            number: '',
            expiry: '',
            cvv: ''
        },
        error_status: {
            name: false,
            number: false,
            expiry: false,
            cvv: false
        },
        next_link: '',
        is_completed: false,
    },

    summary: {
        reference: '',
        check_in: '',
        amount: '',
        check_out: '',
        cc_last_4_digit: '',
        arrival_time: '',
        arriving_by: '',
        flight_no: '',
        guest_images: [],
        full_name: '',
        email: '',
        phone: '',
        adults: '',
        childern: '',
        links: {
            step_1: '',
            step_2: '',
            step_3: '',
            step_4: '',
        },
        arrival_info: true,
        verification: true,
        card_info: true,
        contact_info: true,
        signature_pad: false,
        signature_type: '',
        terms_link:'#',

    },
    tac:{
        has_required_tac:false,
        is_accepted_tac:false,
    },
    step_7: {
        selfie: ''
    },

    step_position: {
        0: 'step_0',
        1: 'step_1',
        2: 'step_2',
        3: 'step_3',
        4: 'add_on_service',
        5: 'credit_card_step',
        6: 'step_7',
        7: 'summary',
    },

    _3ds: {
        meta: {
            next_link: '',
            is_completed: false,
        },
        guest_name: '',
        isPaid: false,
        fName: '',
        lName: '',
        email: '',
        phone: '',
        guestPortalLink: '',
        client_secret: '',
        button_text: '',
        public_key: '',
        account_id: '',
        b_info: '',
        id: '',
        type: '',
        checkout_post_url: '',
        postal_code: '',
        country: '',
        address_line1: '',
        city: '',
        state: ''
    }
};

export default state;
