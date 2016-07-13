@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Новый город</h1>

<div class="row">
	<div class="col-md-8">
		<form class="form-horizontal" method="POST" action="/admin/cities">
			{{ csrf_field() }}

		  	<div class="form-group">
		    	<label for="inputName" class="col-sm-2 control-label">Название</label>
		    	<div class="col-sm-10">
		      		<input type="text" class="form-control" id="inputName" name="name" placeholder="Наименование">
		    	</div>
		  	</div>

		  	<div class="form-group">
	    		<label for="inputStatus" class="col-sm-2 control-label">Статус</label>
		    	<div class="col-sm-4">
		      		<select id="inputStatus" class="form-control" name="status">
						@foreach (App\Category::statusesDropdown() as $value => $label)
							<option value="{{ $value }}">{{ $label }}</option>
						@endforeach
		      		</select>
		    	</div>
		  	</div>
		  	
		  	<hr>
		  	<div class="form-group">
		    	<div class="col-sm-offset-2 col-sm-10">
					<a href="{{ $prevUrl }}" class="btn btn-default">Отмена</a>
		      		<button type="submit" class="btn btn-primary">Создать</button>
		    	</div>
		  	</div>
		</form>
	</div>
</div>


@endsection


@section('scripts_body')

@endsection