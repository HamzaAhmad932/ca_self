<template>

    <div>
        <label for="status">Publish Status</label>
        <div>

            <div class="checkbox-toggle checkbox-choice">
                <input checked="" id="status" name="status" type="checkbox" v-model="status"/>
                <label class="checkbox-label" data-off="OFF" data-on="ON" for="status"><span class="toggle-track"><span
                        class="toggle-switch"></span></span><span class="toggle-title"></span></label>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "SelectStatus",
        data: function () {
            return {
                status: ""
            }

        }
    }
</script>

<style scoped>

</style>