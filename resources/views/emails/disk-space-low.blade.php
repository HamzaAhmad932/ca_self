@component('mail::layout')

<p>Dear Developers</p>,

<p>Disk space is getting low on server. Remaining free space is:- <strong>{{ $data['space_available'] }}GB</strong></p>