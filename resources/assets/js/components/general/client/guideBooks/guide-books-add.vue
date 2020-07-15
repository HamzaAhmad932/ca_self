<template>
    <div>
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <guide-books-add-form :serve_id="'0'"></guide-books-add-form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import GuideBooksAddForm from "./guide-books-add-form";
    export default {
        components: {GuideBooksAddForm}
    }
</script>

<style scoped>

</style>