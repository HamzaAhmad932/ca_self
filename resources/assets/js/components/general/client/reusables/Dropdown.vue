<template>
    <div id="vue-template-dropdown">
        <div :class="{'open': open}" class="dropdown">
            <a @click="open = !open">{{selectedValue}}<b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li v-for="(option,i) in options"><a @click="handleClick(i)">{{option.value}}</a></li>
            </ul>
        </div>
    </div>
</template>
<script>

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        props: {
            open: {
                type: Boolean,
                default: false
            },
            emptyLabel: {
                type: String,
                default: 'Select'
            },
            options: {
                type: Array,
                required: true
            },
        },
        data: function () {
            return {
                selectedOption: {
                    type: Number,
                    default: null
                }
            }
        },
        computed: {
            selectedValue: function () {
                return this.selectedOption >= 0 ? this.options[this.selectedOption].value : this.emptyLabel
            }
        },
        methods: {
            handleClick: function (i) {
                this.selectedOption = i;
                this.open = false;
                this.$emit('change', this.options[i]);
            }
        }
    }
</script>