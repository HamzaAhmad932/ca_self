let state = {
    meta: {
        need_to_update_card: false,
        is_invalid_card: false,
        is_payment_failed: false,
        need_passport_scan: false,
        need_credit_card_scan: false,
    },
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
    address_1: '',
    address_2: '',
    booking_status: '',
    pms_booking_id: '',
    check_in: '',
    check_out: '',
    email: '',
    phone: '',
    arrival_time: '',
    arriving_by: '',
    flight_no: '',
    guest_name: '',
    booking_dates: '',
    show_map: false,
    map_query: '',
    card_info: {
        cc_last_digit: '',
        card_type: ''
    },
    card: {
        name: '',
        number: '',
        expiry: '',
        cvv: '',
        error_status: {
            name: false,
            number: false,
            expiry: false,
            cvv: false,
        },
        error_message: {
            name: '',
            number: '',
            expiry: '',
            cvv: '',
        }
    },
    basic_info: {
        email: '',
        phone: '',
        arrival_time: '',
        error_status: {
            email: false,
            phone: false,
            arrival_time: false
        },
        error_message: {
            email: '',
            phone: '',
            arrival_time: ''
        }
    },
    payments: [],
    deposits: [],
    images: {},
    is_auto_payment_or_security_supported: false,
    is_security_deposit_supported: false,
    auth_info: {
        cc_auth: false,
        security_auth: false,
        security_auth_alert: '',
        cc_auth_alert: '',
    },
    guide_book_types: [],
    guide_book_type: [],
    show_contact_info_form: false,
    show_update_card_form: false,
    upsells: [],
    _3ds_modal: false,
    is_pg_active: false,
    guest_images_status: {}

};


export default state;
