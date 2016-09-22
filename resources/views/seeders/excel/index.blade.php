@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Excel seeder</h1>
<hr>

<div class="row">
	<div class="col-md-12">
		<form action="{{ route('seeders.excel.seed') }}" method="POST" enctype="multipart/form-data">
			{{ csrf_field() }}

			<div class="col-md-4">	
			
				<label for="category_id">Category</label><br>
				{{-- Category Picker --}}
				<div id="">
					<select id="admin_categorypicker" name="category_id" class="js-single-select form-control">
			            @foreach (App\Category::dropdown() as $section => $categories)
			            	{{-- <option value="0">Все категории</option> --}}
			            	<optgroup label="{{ $section }}">
								@foreach ($categories as $key => $name)
					                <option value="{{ $key }}">{{ $name }}</option>
				                @endforeach
			                </optgroup>
			            @endforeach
			        </select>
		    	</div>
		    	<br>

				<label for="file">Input file</label><br>
				<input type="file" name="file" class="form-control">
				<br><br>

				<input type="submit">
			</div>
		</form>
	</div>
</div>

@endsection


@section('scripts_body')
    // select 2
    $(".js-single-select").select2();
@endsection