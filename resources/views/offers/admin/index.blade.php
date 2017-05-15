@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Акции</h1>

<a href="{{ route('admin.offers.create') }}" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить акцию</a>
<br><br>

<hr>
<div class="row">
	<div class="col-md-12">
		{!! $table->render() !!}
	</div>
</div>

@endsection


@section('scripts_body')

	$('.sure').click(function(e) {
		var yesNo = confirm("Вы действительно хотите удалить эту акцию?");
		if (yesNo == true) {
			return true;
		}

		e.stopPropagation();
		return false;
	})

@endsection