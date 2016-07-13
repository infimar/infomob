@extends('layouts.admin.template')

@section('content')


<h1 class="page-header"><img src="{{ asset('images/icons/' . $category->icon ) }}" style="width:64px;height:64px"> {{ $category->name }} (подкатегории)</h1>

<a href="/admin/categories/{{ $category->id }}/createchild" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить подкатегорию</a>

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
				@foreach ($children as $child)
					<tr>
						<td>
							<img style="width: 24px; height: 24px;" src="{{ asset('images/icons/' . $child->icon) }}"> 
							{{ $child->name }}
						</td>
						<td>
							<img src="{{ asset("images/imageloader.gif") }}" class="imageLoader gone" data-id="{{ $child->id }}">
							
							<span data-id="{{ $child->id }}" data-model="category" 
								@if ($child->status == "draft")
									class="btn_toggleStatus label label-danger"
								@elseif ($child->status == "published")
									class="btn_toggleStatus label label-success"
								@else
									class="btn_toggleStatus label label-default"
								@endif
							>
								{{ App\Category::statuses($child->status) }}
							</span>
						</td>
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