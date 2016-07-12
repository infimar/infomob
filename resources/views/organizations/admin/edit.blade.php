@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Редактирование организации</h1>

<div class="row">
	<div class="col-md-8">
		<form class="form-horizontal" method="POST" action="/admin/organizations/{{ $organization->id }}">
			<input type="hidden" name="_method" value="PUT">
			{{ csrf_field() }}

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
			    		<a href="{{ $backUrl }}" class="btn btn-default">Отмена</a>
			      		<button type="submit" class="btn btn-primary">Сохранить изменения</button>
			    	</div>
		  		</div>
		  	</fieldset>
		</form>
	</div>
</div>


<h3 class="page-header">Филиалы</h3>

<a href="/admin/organizations/{{ $organization->id }}/createbranch" class="btn btn-primary" title="Добавить филиал"><i class="fa fa-plus"></i> Добавить филиал</a>
<br><br>

<hr>
<div class="row">
	<div class="col-md-12">
		<div id="div_admin_citypicker">
			<select id="organization_citypicker">
				<option value="0">Все города</option>

	            @foreach (App\City::orderBy("order")->get() as $city)
	                <option value="{{ $city->id }}"
	                    @if ($pickedCityId == $city->id) selected @endif 
	                >
	                    {{ $city->name }}
	                </option>
	            @endforeach
	        </select>
		</div>
	</div>
</div>
<hr>

<table id="tableBranches" class="display" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th>ID</th>
			<th>NAME</th>
			<th>CITY</th>
			<th>CATEGORY</th>
			<th>STATUS</th>
			<th>ACTIONS</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($organization->branches as $branch)
			<tr>
				<td>{{ $branch->id }}</td>
				<td>
					<a href="/admin/branches/{{ $branch->id }}/edit">
						@if ($branch->type == "main") <i class="fa fa-star"></i> @endif {{ $branch->name }}
					</a>
				</td>
				<td>{{ $branch->city->name }}</td>
				<td>{{ $branch->categoryLabel }}</td>
				<td>{{ $branch->status }}</td>
				<td width="200px">
					<a href="/admin/branches/{{ $branch->id }}/edit" class="btn btn-sm btn-default" title="Редактировать"><i class="fa fa-pencil"></i></a>
					<a href="/admin/branches/{{ $branch->id }}/remove" class="btn_remove btn btn-sm btn-default" title="Удалить"><i class="fa fa-trash"></i></a>					
				</td>
			</tr>
		@endforeach
	</tbody>
</table>

@endsection

@section('scripts_body')
	$('#tableBranches').DataTable({
		paging: true,
		aaSorting:[]
	});

	$('#organization_citypicker').change(function(e) {
    	var cityId = $('#organization_citypicker').val();
		location.href = "/admin/organizations/{{ $organization->id }}/edit?city_id=" + cityId;
    });
@endsection