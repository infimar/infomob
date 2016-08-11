@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Организации без категорий</h1>

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
@stop