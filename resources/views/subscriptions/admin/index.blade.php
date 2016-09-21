@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Подписки</h1>

<a href="{{ route('admin.subscriptions.create') }}" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Новая подписка</a>
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


@section('scripts_body')

	$('.sure').click(function(e) {
		var yesNo = confirm("Вы действительно хотите удалить эту подписку?");
		if (yesNo == true) {
			return true;
		}

		e.stopPropagation();
		return false;
	})

@endsection