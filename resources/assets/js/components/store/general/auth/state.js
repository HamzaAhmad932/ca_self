let state = {

    login: {
        email: '',
        password: '',
        remember: true,
        recaptchaToken: '',
        error_status: {
            email: false,
            password: false,
            recaptchaToken: false
        },

        error_message: {
            email: '',
            password: '',
            recaptchaToken: ''
        }
    },

    register: {
        name: '',
        companyname: '',
        phone: '',
        email: '',
        code: '',
        current_pms: '',
        recaptchaToken: '',
        password_confirmation: '',
        password: '',
        agree: '',
        error_status: {
            name: false,
            companyname: false,
            phone: false,
            email: false,
            code: false,
            current_pms: false,
            recaptchaToken: false,
            password_confirmation: false,
            password: false,
            agree: false,
        },

        error_message: {
            name: '',
            companyname: '',
            phone: '',
            email: '',
            code: '',
            current_pms: '',
            recaptchaToken: '',
            password_confirmation: '',
            password: '',
            agree: '',
        }

    },

    password_reset: {
        email: '',
        error_status: {
            email: false,
        },

        error_message: {
            email: '',
        }
    }
};

export default state;