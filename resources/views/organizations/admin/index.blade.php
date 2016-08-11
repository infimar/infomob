@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Организации</h1>

<a href="/admin/organizations/create" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить организацию</a>
<br><br>

<hr>
<div class="row">
	<div class="col-md-12">
		{{-- City Picker --}}
		@if (isset($chosenCity))
		<div id="div_admin_citypicker">
			<select id="admin_citypicker" class="js-single-select">
	            @foreach (App\City::dropdown(true) as $key => $name)
	                <option value="{{ $key }}"
	                    @if ($chosenCity->id == $key) selected @endif 
	                >
	                    {{ $name }}
	                </option>
	            @endforeach
	        </select>
		</div>
		@endif

		{{-- Category Picker --}}
		<div id="div_admin_citypicker">
			<select id="admin_categorypicker" class="js-single-select">
	            @foreach (App\Category::dropdown() as $section => $categories)
	            	{{-- <option value="0">Все категории</option> --}}
	            	<optgroup label="{{ $section }}">
						@foreach ($categories as $key => $name)
							@if ($count[$key] > 0)
			                <option value="{{ $key }}"
			                	@if ($chosenCategory->id == $key) selected @endif 
			                >
			                    {{ $name }} ({{ $count[$key] }}) 
			                </option>
			                @endif
		                @endforeach
	                </optgroup>
	            @endforeach
	        </select>
	    </div>

		{{-- TOP Organizations --}}
		<div id="div_admin_topten">
			<a href="/admin/topten" class="btn btn-default"><i class="fa fa-star"></i> ТОП организации</a>
		</div>
	</div>
</div>
<hr>

<div class="row">
	<div class="col-md-12">
		
		<table id="myTable" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>NAME</th>
					<th>DESCRIPTION</th>
					<th>STATUS</th>
					<th>DATE ADDED</th>
					<th>ACTIONS</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($organizations as $organization)
					<tr>
						<td>
							<a href="/admin/organizations/{{ $organization->id }}/edit">
								{{ $organization->name }}
							</a>
						</td>
						<td>{{ $organization->description }}</td>
						<td width="120px">
							<img src="{{ asset("images/imageloader.gif") }}" class="imageLoader gone" data-id="{{ $organization->id }}">
							
							<span data-id="{{ $organization->id }}" data-model="organization" 
								@if ($organization->status == "draft")
									class="btn_toggleStatus label label-danger"
								@elseif ($organization->status == "published")
									class="btn_toggleStatus label label-success"
								@else
									class="btn_toggleStatus label label-default"
								@endif
							>
								{{ App\Category::statuses($organization->status) }}
							</span>
						</td>
						<td>{{ $organization->created_at->format('d/m/Y H:i:s') }}</td>
						<td width="200px">
							<a href="/admin/organizations/{{ $organization->id }}/edit" class="btn btn-sm btn-default" title="Редактировать"><i class="fa fa-pencil"></i></a>
							<a href="/admin/organizations/{{ $organization->id }}/remove" class="btn_remove btn btn-sm btn-default" title="Удалить"><i class="fa fa-trash"></i></a>

							<a href="/admin/organizations/{{ $organization->id }}/edit#tableBranches" class="btn btn-sm btn-default" title="Филиалы"><i class="fa fa-bars"></i></a>
							<a href="/admin/organizations/{{ $organization->id }}/createbranch" class="btn btn-sm btn-default" title="Добавить филиал"><i class="fa fa-plus"></i></a>
							
							<a href="#" data-id="{{ $organization->id }}" @if (isset($topten_map[$organization->id])) class="btn btn-sm btn-warning btn_topIt" @else class="btn btn-sm btn-default btn_topIt" @endif title="В ТОП!"><i class="fa fa-star"></i></a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>

	</div>
</div>


@stop


@section('scripts_body')
	$('#myTable').DataTable({
		paging: true,
		aaSorting:[]
	});

	$('body').on('click', 'a.btn_topIt', function(e) {
		e.preventDefault();

		var id = $(this).data('id');
		var cityId = chosenCity.id;
		var categoryId = chosenCategory.id;

		var data = {
			id: id,
			cityId: cityId,
			categoryId: categoryId
		};

		//console.log(data);

		$.post('/ajax/organizations/topten', { data: data }, function(response) {
			//console.log(response);

			if (response.code == "added") {
				$('.btn_topIt[data-id=' + id + ']').removeClass().addClass('btn btn-sm btn-warning btn_topIt');
			} else if (response.code == "error") {
				alert(response.msg);
			} else {
				$('.btn_topIt[data-id=' + id + ']').removeClass().addClass('btn btn-sm btn-default btn_topIt');
			}
		});
	});
@stop