Email Verified: 
	@if($account_verified_at) 
		<i class="fa fa-check" style="color: green"></i>
	@else
		<i class="fa fa-times" style="color: red"></i>
	@endif
<br>
Status: <span class="{{ config('db_const.user_account.status_button_color.'.$status) }}">
	@php $new_arr = array_column(config('db_const.user_account.status'), 'label', 'value'); @endphp
	{{ $new_arr[$status] }}
</span>