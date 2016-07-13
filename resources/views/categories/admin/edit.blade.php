@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Редактирование категории</h1>

<div class="row">
	<div class="col-md-8">
		<form class="form-horizontal" method="POST" action="/admin/categories/{{ $category->id }}">
			<input type="hidden" name="_method" value="PUT">
			{{ csrf_field() }}

		  	<div class="form-group">
		    	<label for="inputName" class="col-sm-2 control-label">Наименование</label>
		    	<div class="col-sm-10">
		      		<input type="text" class="form-control" id="inputName" name="name" placeholder="Наименование" value="{{ $category->name }}">
		    	</div>
		  	</div>

		  	<div class="form-group">
	    		<label for="inputStatus" class="col-sm-2 control-label">Статус</label>
		    	<div class="col-sm-4">
		      		<select id="inputStatus" class="form-control" name="status">
						@foreach (App\Category::statusesDropdown() as $value => $label)
							<option value="{{ $value }}" @if ($value == $category->status) selected="selected" @endif>{{ $label }}</option>
						@endforeach
		      		</select>
		    	</div>
		  	</div>

		  	<div class="form-group">
	    		<label for="inputIcon" class="col-sm-2 control-label">Иконка</label>
		    	<div class="col-sm-10">
		      		<div id="media_icons">
		      			<input type="hidden" id="inputIcon" name="icon" value="{{ $category->icon }}">

						@foreach ($icons as $icon)
						<div class="icon clickableIcon @if ($icon == $category->icon) activeIcon @endif" data-icon="{{ $icon }}">
							<img src="{{ asset('images/icons/' . $icon) }}"><br>
							{{-- <span>{{ $icon }}</span> --}}
						</div>
						@endforeach

						<div style="clear:both"></div>
					</div>
		    	</div>
		  	</div>
		  	
		  	<hr>
		  	<div class="form-group">
		    	<div class="col-sm-offset-2 col-sm-10">
					<a href="{{ $prevUrl }}" class="btn btn-default">Отмена</a>
		      		<button type="submit" class="btn btn-primary">Сохранить изменения</button>
		    	</div>
		  	</div>
		</form>
	</div>
</div>


@stop