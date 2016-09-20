@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Акции</h1>

<a href="{{ route('admin.offers.create') }}" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить акцию</a>
<br><br>

<hr>
<div class="row">
	<div class="col-md-12">
		<!-- TODO: FILTER: organization name, city, date_start, date_end -->
		{{-- <p>[Cities dropdown for filtering]</p> --}}

		{!! $table->render() !!}
	</div>
</div>

@endsection