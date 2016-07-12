@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Новая категория</h1>

<div class="row">
	<div class="col-md-8">
		<form class="form-horizontal" method="POST" action="/admin/categories">
			{{ csrf_field() }}

		  <div class="form-group">
		    <label for="inputName" class="col-sm-2 control-label">Name</label>
		    <div class="col-sm-10">
		      <input type="text" class="form-control" id="inputName" name="name" placeholder="Name">
		    </div>
		  </div>
		  
		  <div class="form-group">
		    <div class="col-sm-offset-2 col-sm-10">
		      <button type="submit" class="btn btn-default">Create</button>
		    </div>
		  </div>
		</form>
	</div>
</div>


@stop