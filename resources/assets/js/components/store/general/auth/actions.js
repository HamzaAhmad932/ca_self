let actions = {

    attemptLogin: async function ({commit, state}) {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: 'login',
            method: 'POST',
            data: state.auth.login
        }).then((resp) => {
            if(resp.status == 200){
                window.location = "client/v2/dashboard";
                commit('HIDE_LOADER', null, {root: true});
            }else{
                commit('HIDE_LOADER', null, {root: true});
            }
        }).catch((err) => {

            let errors = err.response;
            let error_message = {};
            let error_status = {};
            if (errors.status == 422 || errors.status == 429) {
                if (errors.data) {
                    for (let k1 in errors.data) {
                        if (typeof errors.data[k1] == "object") {
                            let validation_errors = errors.data[k1];
                            for (let k2 in validation_errors) {
                                error_message[k2] = validation_errors[k2][0];
                                error_status[k2] = true;
                            }
                        }
                    }
                }
            }
            let source_state = 'login'; //sub state name i.e state.login will be created at "SHOW_AUTH_ERRORS" mutation
            commit('SHOW_AUTH_ERRORS', {error_message, error_status, source_state});
            commit('HIDE_LOADER', null, {root: true});
        });
    },

    attemptRegister: async function ({commit, state}) {

        commit('SHOW_LOADER', null, {root: true});

        await axios({
            url: 'register',
            method: 'POST',
            data: state.auth.register
        }).then((resp) => {
            console.log(resp);
            // window.location = "client/v2/dashboard";
            if (resp.data.registered == 1) {
                // create user on intercom
                createUserOnIntercom(resp.data.user, resp.data.user_account, resp.data.user_hash);

                setTimeout(function() {
                    window.location = resp.data.url;
                    commit('HIDE_LOADER', null, {root: true});
                }, 2000);
            }else{
                commit('HIDE_LOADER', null, {root: true});
            }
        }).catch((err) => {
            console.log(err);
            let errors = err.response;
            let error_message = {};
            let error_status = {};
            if (errors.status == 422) {
                if (errors.data) {
                    for (let k1 in errors.data) {
                        if (typeof errors.data[k1] == "object") {
                            let validation_errors = errors.data[k1];
                            for (let k2 in validation_errors) {
                                error_message[k2] = validation_errors[k2][0];
                                error_status[k2] = true;
                            }
                        }
                    }
                }
            }
            let source_state = 'register'; //sub state name i.e state.register will be created at "SHOW_AUTH_ERRORS" mutation
            commit('SHOW_AUTH_ERRORS', {error_message, error_status, source_state});
            commit('HIDE_LOADER', null, {root: true});
        });
    },
};

export default actions;