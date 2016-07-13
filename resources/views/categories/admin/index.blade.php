@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Категории</h1>

<a href="/admin/categories/create" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить категорию</a>

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
				@foreach ($categories as $category)
					<tr>
						<td>
							<img style="width: 24px; height: 24px;" src="{{ asset('images/icons/' . $category->icon) }}"> 
							{{ $category->name }}
						</td>
						<td width="120px">
							<img src="{{ asset("images/imageloader.gif") }}" class="imageLoader gone" data-id="{{ $category->id }}">
							
							<span data-id="{{ $category->id }}" data-model="category" 
								@if ($category->status == "draft")
									class="btn_toggleStatus label label-danger"
								@elseif ($category->status == "published")
									class="btn_toggleStatus label label-success"
								@else
									class="btn_toggleStatus label label-default"
								@endif
							>
								{{ App\Category::statuses($category->status) }}
							</span>
						</td>
						<td width="200px">
							<a href="/admin/categories/{{ $category->id }}/edit" class="btn btn-sm btn-default" title="Редактировать"><i class="fa fa-pencil"></i></a>
							<a href="/admin/categories/{{ $category->id }}/remove" class="btn_remove btn btn-sm btn-default" title="Удалить"><i class="fa fa-trash"></i></a>
							<a href="/admin/categories/{{ $category->id }}/children" class="btn btn-sm btn-default" title="Подкатегории"><i class="fa fa-bars"></i></a>
							<a href="/admin/categories/{{ $category->id }}/createchild" class="btn btn-sm btn-default" title="Добавить подкатегорию"><i class="fa fa-plus"></i></a>
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