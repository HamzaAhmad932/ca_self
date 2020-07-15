<template>
    <div>
        <span>Loading...</span>
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
                    last_name: ''
                }
            },
            
            data() {return {};},
            
            created() {},
            
            mounted() {this.init()},
            
            computed: {},
            
            methods: {
                init() {},
                process(){
                    let pgData = {
                        status: true,
                        token: '',
                        first_name: '',
                        last_name: ''
                    };
                    return pgData;
                },
                validate(){}
            }
            
    }
</script>