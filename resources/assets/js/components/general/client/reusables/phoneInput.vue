<template>
    <div>
        <vue-tel-input :inputId="calling_id" v-bind="bindProps" v-model="phone"></vue-tel-input>
    </div>
</template>

<script>

    export default {
        props: ['phone', 'calling_id'],
        data() {
            return {
                /*available props
                https://github.com/EducationLink/vue-tel-input*/

                bindProps: {
                    autocomplete: "on",
                    autofocus: false,
                    defaultCountry: "",
                    disabled: false,
                    disabledFetchingCountry: false,
                    disabledFormatting: false,
                    dropdownOptions: {disabledDialCode: false, tabindex: 0},
                    dynamicPlaceholder: false,
                    enabledCountryCode: false,
                    enabledFlags: true,
                    ignoredCountries: [],
                    inputClasses: [],
                    inputOptions: {showDialCode: true, tabindex: 0},
                    maxLen: 18,
                    mode: "international",
                    name: "phone_input",
                    onlyCountries: [],
                    placeholder: "Enter Phone Number",
                    preferredCountries: [],
                    required: true,
                    validCharactersOnly: true,
                    wrapperClasses: [],
                },
            }
        },
    }
</script>