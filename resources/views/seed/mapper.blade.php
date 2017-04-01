@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Mapper</h1>

<br><br>

<div class="row">
	<div class="col-md-12">

		<h3>{{ $rubric->name }}</h3>
		<p>{{ $rubricsTotal }} left</p>
		<hr>

		<div class="form-group">
	    	<label for="inputCategoryId" class="col-sm-12 control-label">Категория</label>
	    	<div class="col-sm-6">
	      		<select id="inputCategoryId" name="branch_categoryIds[]" class="form-control js-categories-multiple-select" multiple="multiple">
					<option></option>

					@foreach (App\Category::dropdown() as $section => $categories)
		            	<optgroup label="{{ $section }}">
							@foreach ($categories as $key => $name)
				                <option value="{{ $key }}">
				                    {{ $name }}
				                </option>
			                @endforeach
		                </optgroup>
		            @endforeach
	      		</select>
	    	</div>
	  	</div>

	  	<br><br><br>
	  	<form action="{{ route('seed.map_category') }}" method="POST">
	  		<div class="col-md-6">
		  		<input type="text" name="cat_id" id="cat_id" value="Выберите категорию сверху" class="form-control">
		  		<input type="hidden" name="rubric_id" value="{{ $rubric->id }}">

		  		<br>
		  		{{ csrf_field() }}
		  		<input type="submit" name="delete" value="Delete">
		  		<input type="submit" name="next" value="Next">
	  		</div>
	  	</form>

	</div>
</div>

@endsection

@section('scripts_body')

var catSelect = $(".js-categories-multiple-select").select2({
  placeholder: "Выберите категорию",
  //allowClear: true
}).on("select2:select", function (e) { 
	console.log("select2:select", catSelect.val());

	$('#cat_id').val(catSelect.val());
});

@endsection