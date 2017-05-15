@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Редактирование организации</h1>

<div class="row">
	<form class="form-horizontal" method="POST" action="/admin/organizations/{{ $organization->id }}" enctype="multipart/form-data">
		<input type="hidden" name="_method" value="PUT">
		{{ csrf_field() }}
		
		<div class="col-md-8">
		  	<fieldset>
   				<legend>Организация</legend>

			  	<div class="form-group">
			    	<label for="inputName" class="col-sm-2 control-label">Наименование организации</label>
			    	<div class="col-sm-10">
			      		<input type="text" class="form-control" id="inputName" name="name" value="{{ $organization->name }}" placeholder="Наименование организации">
			    	</div>
			  	</div>

			  	<div class="form-group">
			    	<label for="inputDescription" class="col-sm-2 control-label">Описание | Деятельность | Услуги |</label>
			    	<div class="col-sm-10">
			      		<textarea class="form-control" id="inputDescription" name="description" placeholder="Описание" rows="10">{{ $organization->description }}</textarea>
			    	</div>
			  	</div>

		 		<div class="form-group">
					<label for="inputStatus" class="col-sm-2 control-label">Статус</label>
			    	<div class="col-sm-4">
			      		<select id="inputStatus" name="status" class="form-control">
							<option value="draft" @if ($organization->status == "draft") selected="selected" @endif>Черновик</option>
							<option value="published" @if ($organization->status == "published") selected="selected" @endif>Опубликовано</option>
			      		</select>
			    	</div>
		 		</div>
		 	</fieldset>

			<fieldset>
				<legend>&nbsp;</legend>
		  		<div class="form-group">
			    	<div class="col-sm-offset-2 col-sm-10">
			    		<a href="{{ route('admin.organizations.index') }}" class="btn btn-default">Отмена</a>
			      		<button type="submit" class="btn btn-primary">Сохранить изменения</button>
			    	</div>
		  		</div>
		  	</fieldset>
		</div>
		<div class="col-md-4">
			<div class="form-group">
		    	<label for="logo" class="col-sm-2 control-label">Логотип</label>
				<br><br>

				{{-- logo if exists --}}
				<div style="padding-left: 15px;">
					<img src="{{ asset('images/logos/' . $organization->logo) }}" alt="" style="width:128px;">
				</div>

		    	{{-- file input --}}
		    	<div class="col-sm-10" style="margin-top: 16px;">
		      		<input type="file" class="form-control" id="logo" name="logo">
		    	</div>
		  	</div>
		</div>

	</form>
</div>


<h3 class="page-header">Филиалы</h3>

<a href="/admin/organizations/{{ $organization->id }}/createbranch" class="btn btn-primary" title="Добавить филиал"><i class="fa fa-plus"></i> Добавить филиал</a>
<br><br>

<hr>
<div class="row">
	<div class="col-md-12">
		{{-- City Picker --}}
		@if (isset($chosenCity))
		<div id="div_admin_citypicker">
			<select id="organization_citypicker" class="js-single-select">
				<option value="0">Все города</option>

	            @foreach (App\City::dropdown() as $key => $name)
	                <option value="{{ $key }}"
	                    @if ($pickedCityId == $key) selected @endif 
	                >
	                    {{ $name }}
	                </option>
	            @endforeach
	        </select>
		</div>
		@endif

		{{-- Category Picker --}}
		<div id="div_admin_citypicker">
			<select id="organization_categorypicker" class="js-single-select">
				<option value="0">Все категории</option>

	            @foreach (App\Category::dropdown() as $section => $categories)
	            	<optgroup label="{{ $section }}">
						@foreach ($categories as $key => $name)
							@if ($count[$key] > 0)
			                <option value="{{ $key }}"
			                	@if ($pickedCategoryId == $key) selected @endif 
			                >
			                    {{ $name }} ({{ $count[$key] }})
			                </option>
			                @endif
		                @endforeach
	                </optgroup>
	            @endforeach
	        </select>
	    </div>
	</div>
</div>
<hr>

<table id="tableBranches" class="display" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th>NAME</th>
			<th>CITY</th>
			<th>CATEGORY</th>
			<th>STATUS</th>
			<th>ADDED ON</th>
			<th>ACTIONS</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($organization->branches as $branch)
			<tr>
				<td>
					<a href="/admin/branches/{{ $branch->id }}/edit">
						{{ $branch->name }}
					</a>
				</td>
				<td>{{ $branch->city->name }}</td>
				<td>{{ $branch->categoryLabel }}</td>
				<td width="120px">
					<img src="{{ asset("images/imageloader.gif") }}" class="imageLoader gone" data-id="{{ $branch->id }}">
					
					<span data-id="{{ $branch->id }}" data-model="branch" 
						@if ($branch->status == "draft")
							class="btn_toggleStatus label label-danger"
						@elseif ($branch->status == "published")
							class="btn_toggleStatus label label-success"
						@else
							class="btn_toggleStatus label label-default"
						@endif
					>
						{{ App\Category::statuses($branch->status) }}
					</span>
				</td>
				<td>@if ($branch->created_at) {{ $branch->created_at->format('d/m/Y H:i:s') }} @endif</td>
				<td width="200px">
					<a href="/admin/branches/{{ $branch->id }}/edit" class="btn btn-sm btn-default" title="Редактировать"><i class="fa fa-pencil"></i></a>
					<a href="/admin/branches/{{ $branch->id }}/remove" class="btn_remove btn btn-sm btn-default" title="Удалить"><i class="fa fa-trash"></i></a>

					<a href="/admin/branches/{{ $branch->id }}/gallery" class="btn btn-sm @if (count($branch->photos) > 0) btn-info @else btn-default @endif" title="Удалить"><i class="fa fa-picture-o"></i></a>
					<a href="#" data-id="{{ $branch->id }}" @if ($branch->type == "main") class="btn btn-sm btn-warning btn_makeMain" @else class="btn btn-sm btn-default btn_makeMain" @endif title="Сделать главным филиалом"><i class="fa fa-star"></i></a>

					<a href="#" data-id="{{ $branch->id }}" @if ($branch->is_featured == 1) class="btn btn-sm btn-info btn_makeFeatured" @else class="btn btn-sm btn-default btn_makeFeatured" @endif title="Добавить в популярные"><i class="fa fa-asterisk"></i></a>	
				</td>
			</tr>
		@endforeach
	</tbody>
</table>

@endsection

@section('scripts_body')

	$('.btn_makeMain').click(function(e) {
		e.preventDefault();
	
		var id = $(this).data('id');

		$.post('/ajax/branches/makemain', { id: id }, function(response) {
			if (response.code == 200) {
				//$('.btn_makeMain').removeClass().addClass('btn btn-sm btn-default btn_makeMain');
				$('.btn_makeMain[data-id=' + id + ']').removeClass().addClass('btn btn-sm btn-warning btn_makeMain');
			}
		});
	});

	$('.btn_makeFeatured').click(function(e) {
		e.preventDefault();
	
		var id = $(this).data('id');

		$.post('/ajax/branches/makefeatured', { id: id }, function(response) {
			if (response.code == 200 && response.is_featured == 1) {
				$('.btn_makeFeatured[data-id=' + id + ']').removeClass().addClass('btn btn-sm btn-info btn_makeFeatured');
			} else {
				$('.btn_makeFeatured[data-id=' + id + ']').removeClass().addClass('btn btn-sm btn-default btn_makeFeatured');
			}
		});
	});

	$('#tableBranches').DataTable({
		paging: true,
		aaSorting:[]
	});

	$('#organization_citypicker').change(function(e) {
    	var cityId = $('#organization_citypicker').val();
		var url = "/admin/organizations/{{ $organization->id }}/edit?city_id=" + cityId + "&category_id=" + pickedCategoryId;
		url += "#tableBranches";

		location.href = url;
    });

    $('#organization_categorypicker').change(function(e) {
    	var categoryId = $('#organization_categorypicker').val();
		var url = "/admin/organizations/{{ $organization->id }}/edit?city_id=" + pickedCityId + "&category_id=" + categoryId;
		url += "#tableBranches";

		location.href = url;
    });
@endsection