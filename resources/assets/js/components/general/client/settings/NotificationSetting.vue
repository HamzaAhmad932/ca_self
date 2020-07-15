<template>
    <div class="row">
        <!-- Account Settings -->
        <div class="col-md-12" style="font-weight: bold; margin-top: 2rem;">
            ACCOUNT RELATED ACTIVITIES
        </div>
        <template
                v-for="setting in all_notification_settings.account_notifications">
            <div class="col-md-12"
                 style="padding-left: 1.2em; margin-top: 1em;">
                <span style="font-weight: bold; font-size: 0.875rem">{{ setting.name }}</span>
                <p class="small">{{ setting.desc }}</p>
            </div>
            <div class="col-md-12" style="padding-left: 3em">
                <input :id="setting.id"
                       class="form-control make_it_token_input	"
                       ref="make_it_token_input" v-model="setting.to_email"/>
            </div>
        </template>
        <!-- Account Settings ends -->

        <!-- Guest Settings -->
        <div class="col-md-12" style="font-weight: bold; margin-top: 2rem;">
            GUEST RELATED ACTIVITIES
        </div>

        <template
                v-for="setting in all_notification_settings.guest_notification">
            <div class="col-md-12" style="padding-left: 1.2em;margin-top: 1em;">
                <span style="font-weight: bold; font-size: 0.875rem">{{ setting.name }}</span>
                <p class="small">{{ setting.desc }}</p>
            </div>
            <div class="col-md-12" style="padding-left: 3em">
                <input :id="setting.id"
                       class="form-control make_it_token_input	"
                       ref="make_it_token_input" v-model="setting.to_email"/>
            </div>
        </template>
        <!-- Guest Settings ends -->

        <!-- Payment Activity Settings -->
        <div class="col-md-12" style="font-weight: bold; margin-top: 2rem;">
            PAYMENT RELATED ACTIVITIES
        </div>

        <template
                v-for="setting in all_notification_settings.payment_activity_notification">
            <div class="col-md-12" style="padding-left: 1.2em;margin-top: 1em;">
                <span style="font-weight: bold; font-size: 0.875rem">{{ setting.name }}</span>
                <p class="small">{{ setting.desc }}</p>
            </div>
            <div class="col-md-12" style="padding-left: 3em">
                <input :id="setting.id"
                       class="form-control make_it_token_input	"
                       ref="make_it_token_input" v-model="setting.to_email"/>
            </div>
        </template>
        <!-- Payment Activity Settings ends -->
        <BlockUI :html="'<i class=`fa fa-spinner fa-spin fa-3x fa-fw`></i>'" :message="'Please Wait'" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    export default {
        name: "NotificationSetting.vue",
        data() {
            return {
                block:false,
                all_notification_settings: {
                    account_notifications: {},
                    guest_notification: {},
                    payment_activity_notification: {}
                },

                mailbxvl: {
                    tovl: '',
                    ccvl: '',
                    bccvl: '',
                    activityId: '',
                    error: ''
                },
            }
        },
        methods: {
            fetchNotifications() {
                this.block = true;
                let self = this;
                axios({
                    url: '/client/v2/fetch-notification-settings',
                    method: 'GET',
                    headers: {
                        'content-type': 'application/json'
                    }
                })
                    .then((resp) => {
                        if (resp.data.status_code == '200') {
                            self.all_notification_settings.account_notifications = resp.data.data.account_notifications;
                            self.all_notification_settings.guest_notification = resp.data.data.guest_notification;
                            self.all_notification_settings.payment_activity_notification = resp.data.data.payment_activity_notification;

                            this.$nextTick(() => {
                                $(this.$refs.make_it_token_input)
                                    .tokenfield()
                                    .on('tokenfield:createtoken', function (e) {
                                        // Über-simplistic e-mail validation
                                        var re = /\S+@\S+\.\S+/;
                                        var valid = re.test(e.attrs.value);
                                        if (!valid) {
                                            toastr.error('Please enter valid email');
                                            return false;
                                        } else {
                                            self.addMails(e.currentTarget.value + ',' + e.attrs.value, e.currentTarget.id)
                                                .then(() => {
                                                    return true;
                                                })
                                                .catch(() => {
                                                    return false;
                                                });

                                        }
                                    })
                                    .on('tokenfield:removedtoken', function (e) {
                                        self.addMails(e.currentTarget.value, e.currentTarget.id);
                                    });
                            });

                        }
                        self.block = false;
                    })
                    .catch((err) => {
                        console.log(err);
                        self.block = false;
                    });
            },
            addMails(emails, activityId) {
                this.block = true;

                let th = this;

                th.mailbxvl.tovl = emails;
                th.mailbxvl.activityId = activityId;

                return axios.post('/client/v2/mailsettings', th.mailbxvl)
                    .then(function (response) {

                        //stop loader
                        th.block = false;

                        if (response.data.status == true) {
                            toastr.success(response.data.msg);
                            return Promise.resolve();
                        } else if (response.data.status == false) {
                            toastr.error(response.data.msg);
                            return Promise.reject();
                        } else {
                            return Promise.reject();
                        }

                    }).catch(function (error) {
                        //stop loader
                        th.block = false;
                        toastr.error('Failed to save Settings!');
                        return Promise.reject();
                    });
            },
        },

        mounted() {
            this.fetchNotifications();
        }
    }
</script>
<style scoped>

</style>