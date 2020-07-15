export const general_components_init = ()=> {
    return [
        {
            component_name: 'general-guest-experience',
            icon: 'fas fa-people-carry',
            label: 'Guest Experience'
        },
        {
            component_name: 'general-upsell-detail',
            icon: 'fas fa-cart-arrow-down',
            label: 'Upsell'
        },
        {
            component_name: 'general-documents-detail',
            icon: 'fas fa-passport',
            label: 'Documents'
        },
        {
            component_name: 'general-payment-attempts-activity-log',
            icon: 'fas fa-stream',
            label: 'Activity Log'
        },
        {
            component_name: 'general-sent-email-detail',
            icon: 'fas fa-envelope',
            label: 'Sent Email',
        }
    ];
};

let state = {

    header: {
        pms_booking_id: '',
        pms_booking_Status: '',
        previous: '',
        next: '',
        guest_name: '',
        id_verification_status: '',
    },

    tab_section: {
        current_tab: {
            component_name: '',
            icon: ''
        },
        tabs:{
            general: [
                {
                    component_name: 'BookingDetails',
                    icon: 'fas fa-address-card',
                    label: 'Booking Detail',
                },
                {
                    component_name: 'Payments',
                    icon: 'fas fa-donate',
                    label: 'Payments',
                },
                ...general_components_init()
            ],
            ba: [
                {
                    component_name: 'ba-booking-detail',
                    icon: 'fas fa-address-card',
                    label: 'Booking Detail',
                },
                {
                    component_name: 'ba-payment-detail',
                    icon: 'fas fa-donate',
                    label: 'Payments',
                },
                ...general_components_init()
            ],
        },
    },

    booking_details: {
        pms_booking_id: '',
        property_heading: '',
        property_sub_heading: '',
        property_logo: '',
        source_link: '#',
        source_heading: '',
        booking_date: '',
        channel_reference: '',
        source: '',
        left_days: '4 days left',
        checkin_date: '',
        checkout_date: '',
        arrival_time: '',
        adults: '',
        children: '',
        guest_comments: '',
        internal_notes: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
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

    guest_experience: {

        arrival_time: '',
        arriving_by: '',
        plane_number: '',
        other_detail: '',
        is_precheckin_completed: false,
        is_confirmation_sent: false,
        route: {
            guest_portal: '',
            precheckin: ''
        },
        scans: [],
        visit: {
            guest_portal: '',
            guest_portal_class: 'text-danger',
            precheckin: '',
            precheckin_class: 'text-danger'
        },

        error_message: {
            arrival_time: '',
            arriving_by: '',
            plane_number: '',
            other_detail: ''
        },
        error_status: {
            arrival_time: false,
            arriving_by: false,
            plane_number: false,
            other_detail: false
        },
    },

    payments: {

        capabilities: [],
        is_payment_gateway_found: false,
        is_credit_card_available: false,
        payment_summary: {
            show_refund: false,
            charges: '',
            extras: '',
            extras_details: [],
            sub_total: '',
            paid: '',
            amount_due: '',
        },
        pending_payments: [],
        declined_payments: [],
        accepted_payments: [],
        other_payments: [],
    },

    documents: {
        documents_to_check: [],
        accepted_documents: [],
        rejected_documents: [],
        deleted_documents:[],
    },

    upsells : {},

    document_upload: {
        error_message: {
            credit_card: '',
            passport: '',
            selfie: '',
        },
        error_status: {
            credit_card: false,
            passport: false,
            selfie: false
        },
    },

    document_description: {},

    activity_log: [],
    payment_status: '',
    sent_emails: []

};


export default state;