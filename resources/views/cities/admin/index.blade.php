@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Города</h1>

<a href="/admin/cities/create" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить город</a>

<div class="row">
	<div class="col-md-8">
		
		<table id="myTable" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>NAME</th>
					<th>STATUS</th>
					<th>ACTIONS</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($cities as $city)
					<tr>
						<td>
							{{ $city->name }}
						</td>
						<td width="120px">
							<img src="{{ asset("images/imageloader.gif") }}" class="imageLoader gone" data-id="{{ $city->id }}">
							
							<span data-id="{{ $city->id }}" data-model="city" 
								@if ($city->status == "draft")
									class="btn_toggleStatus label label-danger"
								@elseif ($city->status == "published")
									class="btn_toggleStatus label label-success"
								@else
									class="btn_toggleStatus label label-default"
								@endif
							>
								{{ App\Category::statuses($city->status) }}
							</span>
						</td>
						<td width="200px">
							<a href="/admin/cities/{{ $city->id }}/edit" class="btn btn-sm btn-default" title="Редактировать"><i class="fa fa-pencil"></i></a>
							<a href="/admin/cities/{{ $city->id }}/remove" class="btn_remove btn btn-sm btn-default" title="Удалить"><i class="fa fa-trash"></i></a>
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
		paging: false,
		aaSorting:[]
	});
@stop