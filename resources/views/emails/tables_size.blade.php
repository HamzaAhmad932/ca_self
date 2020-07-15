@component('mail::layout')

<p>Dear Developers</p>,

<p>Following Tables have size more than 500 MB:-</p>

@foreach($data['tables'] as $table)
	<p><strong>{{ $table->name }}:-</strong> {{ $table->size }}MB</p>
@endforeach