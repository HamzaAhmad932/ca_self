let general_root_state = {

    guest_upload_doc_id: 0,
    guest_credit_card_id: 0,
    booking_id_action_chat: {
        data: []
    },
    reduce_amount_data: {
        booking_id: '',
        tran_id: '',
        current_amount: 0,
        new_amount: false,
        new_balance: false
    },
    additional_charge_active_booking_id: 0,
    refund_amount_active_booking_id: 0,
    refund_amount_active_transaction_id: 0,
    amount_valid_to_refund: 0,
    capture_amount_active_booking_id: 0,
    capture_amount_active_cc_auth_id: 0,
    amount_valid_to_capture: 0,
    capture_amount: {
        error_message: {
            amount: '',
            description: ''
        },
        error_status: {
            amount: false,
            description: false
        },
    },
    images: {},

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
    documents: {
        documents_to_check: [],
        accepted_documents: [],
        rejected_documents: [],
        deleted_documents:[],
    },

    pms_prefix: 'general'
};

export default general_root_state;