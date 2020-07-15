<template>
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <upsell-types-add-form serve_id="0"></upsell-types-add-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import UpsellTypesAddForm from "./upsell-types-add-form";
    export default {
        name: "upsell-types-add",
        components: {UpsellTypesAddForm}
    }
</script>

<style scoped>

</style>
