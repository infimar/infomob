@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Редактирование категории</h1>

<div class="row">
	<div class="col-md-8">
		<form class="form-horizontal" method="POST" action="/admin/categories/{{ $category->id }}">
			<input type="hidden" name="_method" value="PUT">
			{{ csrf_field() }}

		  <div class="form-group">
		    <label for="inputName" class="col-sm-2 control-label">Name</label>
		    <div class="col-sm-10">
		      <input type="text" class="form-control" id="inputName" name="name" placeholder="Name" value="{{ $category->name }}">
		    </div>
		  </div>
		  
		  <div class="form-group">
		    <div class="col-sm-offset-2 col-sm-10">
		      <button type="submit" class="btn btn-default">Update</button>
		    </div>
		  </div>
		</form>
	</div>
</div>


@stop