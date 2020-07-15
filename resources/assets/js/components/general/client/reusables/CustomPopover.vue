<template>
    <div class="inline">
        <div v-on:mouseover="hover" v-on:mouseout="hoverOut">
            <slot name="trigger"></slot>
        </div>
        <div class="custom-popover" v-if="showPopup" transition="fade" v-on:mouseover="hoverInfo" v-on:mouseout="hoverOutInfo">
            <div class="card">
                <div class="card-header" v-if="show_header"><strong>{{header}}</strong></div>
                <slot name="cardBody"></slot>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        props: ['show_header', 'header'],
        data: function () {
            return {
                showPopup: false,
                timer: '',
                isInInfo: false
            }
        },
        methods: {

            hover: function()
            {
                let vm = this;
                this.timer = setTimeout(function() {
                    vm.showPopover();
                }, 300);
            },

            hoverOut: function()
            {
                let vm = this;
                clearTimeout(vm.timer);
                this.timer = setTimeout(function() {
                    if(!vm.isInInfo)
                    {
                        vm.closePopover();
                    }
                }, 200);
            },

            hoverInfo: function()
            {
                this.isInInfo = true;
            },

            hoverOutInfo: function()
            {
                this.isInInfo = false;
                this.hoverOut();
            },

            showPopover: function()
            {
                this.showPopup = true;
            },

            closePopover: function()
            {
                this.showPopup = false;
            }
        }

    }
</script>