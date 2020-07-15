<template>
    <div>
        <div class="addon-item">
            <div class="addon-item-header">
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" id="customCheck1" type="checkbox">
                    <label class="custom-control-label" for="customCheck1"></label>
                </div>
                <div class="addon-item-header-content">
                    <div class="addon-item-header-text">
                        <h4>Early Checkin</h4>
                        <p class="text-muted">We provide early checkin. You can just pay $40 and arrive by 4 hours.</p>
                    </div>
                    <div class="addon-price"><span class="text-success h5">$40  </span><span>per booking one-time</span>
                    </div>
                </div>
                <a aria-controls="addonCollapse1" aria-expanded="false" class="link-overlay" data-toggle="collapse"
                   href="#addonCollapse1" role="button">
                    <div class="addon-collapse-btn"><i class="fas fa-chevron-up"></i></div>
                </a>
            </div>
            <div class="addon-body collapse show" id="addonCollapse1">
                <div class="addon-body-content">
                    <div class="addon-section-item">
                        <div class="icon"><i class="fas fa-clock"></i></div>
                        <h6>Time Frame</h6>12:00PM to 4:00
                    </div>
                    <div class="addon-section-item">
                        <div class="icon"><i class="fas fa-info"></i></div>
                        <h6>Info</h6>We provide early checkin. Some very long description to take a look how it acts in
                        small screens.
                    </div>
                    <div class="addon-section-item active">
                        <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                        <h6>Cancelation Policy</h6>Non-refundable once purchased.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ViewUpsell"
    }
</script>

<style scoped>

</style>