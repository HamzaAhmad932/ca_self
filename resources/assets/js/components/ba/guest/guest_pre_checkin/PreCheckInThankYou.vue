<template>
    <div>
        <div class="precheckin-success" style="margin-top:130px;margin-bottom:100px;">
            <i class="far fa-check-circle text-success mb-3" style="font-size:5rem;"></i>
            <h3 class="text-success">You have successfully completed your Pre Check-in</h3>
<!--            <p class="text-muted">-->
<!--                Please check your inbox, an email was just sent to you with additional instructions.-->
<!--            </p>-->
            <p class="text-muted">
                You will be redirected to your personalized Guest Portal in a few seconds.
            </p>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['booking_id', 'next_url'],
        watch: {
            booking_id: {
                deep: true,
                immediate: true,
                handler(new_value, old_value) {
                    setTimeout(() => {
                        window.location.href = this.next_url;
                    }, 2000);
                }
            }
        }
    }
</script>
