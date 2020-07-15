<template>
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="page-header header-of-setting-pages mb-3">
                            <h1 class="page-title">Preferences Settings</h1>
                        </div>

                        <div class="page-body">
                            <div class="content-box">
                                <div class="setup-box">

                                    <nav class=" nav setup-steps">
                                        <a v-for="nav in navigation"
                                           :class="nav.component === currentComponent ? 'nav-link setup-step-item active show' : 'nav-link setup-step-item'"
                                           :ref="nav.ref" href="javascript:void(0)"
                                           @click="toggle(nav.component, false)">
                                            <div class="step-icon"><i :class="nav.step_icon"></i></div>
                                            <div class="setup-title">{{nav.title}}</div>
                                        </a>
                                    </nav>

                                    <div class="tab-content setup-body" id="bookings-tabContent">
                                        <component v-bind:is="currentComponent"></component>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "preference-settings",
        data() {
            return {
                currentComponent: 'ba-pms-modification-settings',
                navigation: [
                    {
                        title: 'PMS Modification',
                        step_icon: 'fas fa-user-cog',
                        ref: 'preferences',
                        component: 'ba-pms-modification-settings',
                    },
                    {
                        title: 'Notifications',
                        step_icon: 'fas fa-envelope-open-text',
                        ref: 'notifications',
                        component: 'notification-settings',
                    },
                    {
                        title: 'Manage Emails',
                        step_icon: 'far fa-envelope',
                        ref: 'manageEmails',
                        component: 'email-types-nav',
                    },
                    {
                        title: 'Booking Sources',
                        step_icon: 'fas fa-cogs',
                        ref: 'bookingSources',
                        component: 'booking-channel-settings',
                    },
                ],
            }
        },
        methods: {
            toggle(component, url_hash = false) {
                if (url_hash) {
                    const URLSegment = window.location.href.substr(window.location.href.lastIndexOf('/') + 1);
                    let requested_nav = this.navigation.filter(nav => nav.ref === URLSegment);
                    if (requested_nav.length) {
                        this.currentComponent = requested_nav[0]['component'];
                    }
                } else {
                    this.currentComponent = component;
                }
            }
        },

        mounted() {
            this.toggle(null, true);
        }
    }
</script>
