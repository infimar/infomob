@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Подкатегории</h1>

<a href="/admin/categories/{{ $category->id }}/createchild" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить подкатегорию</a>

<div class="row">
	<div class="col-md-8">
		
		<table id="myTable" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>ID</th>
					<th>NAME</th>
					<th>SLUG</th>
					<th>ACTIONS</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($children as $child)
					<tr>
						<td>{{ $child->id }}</td>
						<td>{{ $child->name }}</td>
						<td>{{ $child->slug }}</td>
						<td width="200px">
							<a href="/admin/categories/{{ $child->id }}/editchild" class="btn btn-sm btn-default" title="Редактировать"><i class="fa fa-pencil"></i></a>
							<a href="/admin/categories/{{ $child->id }}/removechild" class="btn_remove btn btn-sm btn-default" title="Удалить"><i class="fa fa-trash"></i></a>
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
		paging: false
	});
@stop