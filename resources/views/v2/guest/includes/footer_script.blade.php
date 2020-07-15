
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<script>
    jQuery(document).ready(function($) {
        var alterClass = function() {
            var ww = document.body.clientWidth;
            if (ww < 1199) {
                $('#wrapper').removeClass('sidebar-displayed');
            } else if (ww >= 1200) {
                //$('#wrapper').addClass('sidebar-displayed');
            };
        };

        $('#menu-toggle').click(function(e) {
            e.preventDefault();
            $('#wrapper').toggleClass('sidebar-displayed');
        });

        $(window).resize(function(){
            alterClass();
        });
        //Fire it when the page first loads:
        alterClass();
    });

</script>

@if (config('app.debug') == false && config('app.url') == 'https://app.chargeautomation.com')
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-124409336-1"></script>
    <script src="{{ asset('v2/js/google_analytics_code.js') }}"></script>
@endif