<template>
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <terms-and-conditions-add-form :serve_id="'0'"></terms-and-conditions-add-form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TermsAndConditionsAddForm from "./terms-and-conditions-add-form";

    export default {
        name: "terms-and-conditions-add",
        components: {TermsAndConditionsAddForm}
    }
</script>

<style scoped>

</style>