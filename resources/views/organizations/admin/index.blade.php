@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Организации</h1>

<a href="/admin/organizations/create" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить организацию</a>
<br><br>

<hr>
<div class="row">
	<div class="col-md-12">
		@if (isset($chosenCity))
		<div id="div_admin_citypicker">
			<select id="admin_citypicker">
	            @foreach (App\City::orderBy("order")->get() as $city)
	                <option value="{{ $city->id }}"
	                    @if ($chosenCity->id == $city->id) selected @endif 
	                >
	                    {{ $city->name }}
	                </option>
	            @endforeach
	        </select>
		</div>
		@endif

		<div id="div_admin_topten">
			<a href="?topten=1" class="btn btn-default"><i class="fa fa-star"></i> ТОП организации</a>
		</div>
	</div>
</div>
<hr>

<div class="row">
	<div class="col-md-12">
		
		<table id="myTable" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>ID</th>
					<th>NAME</th>
					<th>DESCRIPTION</th>
					<th>STATUS</th>
					<th>ACTIONS</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($organizations as $organization)
					<tr>
						<td>{{ $organization->id }}</td>
						<td>
							<a href="/admin/organizations/{{ $organization->id }}/edit">
								{{ $organization->name }}
							</a>
						</td>
						<td>{{ $organization->description }}</td>
						<td>{{ $organization->status }}</td>
						<td width="200px">
							<a href="#" @if ($organization->order != 9999) data-topten="1" class="btn btn-sm btn-warning" @else data-topten="0" class="btn btn-sm btn-default"  @endif  title="В ТОП!" id="topit"><i class="fa fa-star"></i></a>
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

	$('#topit').click(function($e) {
		var topTen = $(this).data('topten');

		if (topTen == 1) {
			alert('Убрать из топа (ajax request)');
		} else {
			alert('Добавить в топ и перейти для установления порядка');
		}
	});
@stop