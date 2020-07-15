<notification-sidebar
        :notifications-fa-class='{!! json_encode(config("db_const.notifications.fa_class"), JSON_HEX_TAG) !!}'
        :notifications-icon-color-class='{!! json_encode(config("db_const.notifications.icon_color_class"), JSON_HEX_TAG) !!}'
        :notifications-type-text='{!! json_encode(config("db_const.notifications.alert_type_text"), JSON_HEX_TAG) !!}'
        :notifications-redirect-to-tab='{!! json_encode(config("db_const.notifications.redirect_to_tab"), JSON_HEX_TAG) !!}'
		is_manager="{{ $permittedForNotification }}"
></notification-sidebar>

@push('below_script')

	<!-- This script is responsible small round running loader when your click on load more notifications -->
	<script>!function(){function t(t){this.element=t,this.animationId,this.start=null,this.init()}if(!window.requestAnimationFrame){var i=null;window.requestAnimationFrame=function(t,n){var e=(new Date).getTime();i||(i=e);var a=Math.max(0,16-(e-i)),o=window.setTimeout(function(){t(e+a)},a);return i=e+a,o}}t.prototype.init=function(){var t=this;this.animationId=window.requestAnimationFrame(t.triggerAnimation.bind(t))},t.prototype.reset=function(){var t=this;window.cancelAnimationFrame(t.animationId)},t.prototype.triggerAnimation=function(t){var i=this;this.start||(this.start=t);var n=t-this.start;504>n||(this.start=this.start+504),this.element.setAttribute("transform","rotate("+Math.min(n/1.4,360)+" 12 12)");if(document.documentElement.contains(this.element))window.requestAnimationFrame(i.triggerAnimation.bind(i))};var n=document.getElementsByClassName("nc-loop_circle-02-24"),e=[];if(n)for(var a=0;n.length>a;a++)!function(i){e.push(new t(n[i]))}(a);document.addEventListener("visibilitychange",function(){"hidden"==document.visibilityState?e.forEach(function(t){t.reset()}):e.forEach(function(t){t.init()})})}();
	</script>
@endpush
