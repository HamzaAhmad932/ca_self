<template>
    <div>
        <div class="page-header header-of-pms-integration-pages account-setup-title text-center mb-3">
            <h1 class="page-title">Set Up Your Account </h1>
            <div class="preference-stack account-enable-disable-button-wrapper" v-if="$can('full client') && integration_status">
                <div class="d-flex text-muted button-title-portion">ChargeAutomation Status:&nbsp;&nbsp;</div>
                <div class="checkbox-toggle checkbox-choice">
                    <input :checked="account_status == 1" @click="onOffUserAccount($event)" id="check-1" name="onOffUserAccount"
                           type="checkbox">
                    <label class="checkbox-label" data-off="OFF" data-on="ON" for="check-1">
                        <span class="toggle-track">
                            <span class="toggle-switch"></span>
                        </span>
                        <span class="toggle-title"></span>
                    </label>
                </div>

                <span class="badge badge-success status-badge-align pull-right" data-placement="top" data-toggle="tooltip" title="Account Active"
                      v-if="account_status == 1">
                    <i class="fas fa-check-circle"></i>
                    Active
                </span>
                <span class="badge badge-danger status-badge-align pull-right" data-placement="top" data-toggle="tooltip" title="Account Inactive"
                      v-else>
                    <i class="fas fa-exclamation-triangle"></i>
                    Inactive
                </span>
                <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "account-enable-disable-button",
        created() {
            this.fetchPreferences();
        },
        data() {
            return {
                msg: 'Please Wait',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
                account_status: 2,
                integration_status: false
            }
        },
        methods: {
            fetchPreferences() {
                //this.block = true;
                let self = this;
                axios({
                    url: '/client/v2/fetch-preferences',
                    method: 'GET',
                    headers: {
                        'content-type': 'application/json'
                    }
                })
                    .then((resp) => {
                        if (resp.data.status_code == '200') {
                            this.account_status = resp.data.account_status;
                            this.integration_status = resp.data.integration_status;
                        }
                        //self.block = false;
                    })
                    .catch((err) => {
                        console.log(err);
                        //self.block = false;
                    });
            },
            onOffUserAccount(ev) {

                let self = this;
                var msg = ev.target.checked === true ? 'Enable ChargeAutomation?' : 'Are you sure want to disable the Charge Automation account?';

                swal.fire({
                    title: msg,
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes"
                }).then(function (e) {
                    if (e.value == true) {
                        self.block = true;
                        axios({
                            url: '/client/v2/company-status',
                            method: 'POST',
                            data: {status: ev.target.checked}
                        }).then((resp) => {
                            if (resp.data.status_code == 200) {
                                toastr.success(resp.data.message);
                                self.account_status = 1;
                                self.block = false;
                            }
                            if (resp.data.status_code == 422) {
                                toastr.success(resp.data.message);
                                self.account_status = 2;
                                ev.target.checked = !ev.target.checked;
                                self.block = false;
                            }
                            window.location.reload();
                        }).catch((err) => {
                            console.log(err);
                            self.block = false;
                        });
                    } else {
                        ev.target.checked = !ev.target.checked;
                    }
                });
            },
        }
    }
</script>
