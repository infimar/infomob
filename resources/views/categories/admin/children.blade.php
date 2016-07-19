@extends('layouts.admin.template')


@section('styles')

.select2-dropdown.select2-dropdown--below {
	margin-left: 30px;
	width: 300px !important;
}

.select2-container--default .select2-selection--single {
    margin-left: 30px;
    width: 300px !important;
    position: relative;
}

.parentCategory {
	display: none;
}

.show_categoryParent:hover {
	cursor: pointer;
}

@endsection


@section('content')

<h1 class="page-header"><img src="{{ asset('images/icons/' . $category->icon ) }}" style="width:64px;height:64px"> {{ $category->name }} (подкатегории)</h1>

<a href="/admin/categories/{{ $category->id }}/createchild" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить подкатегорию</a>
<br><br>

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
							{{ $child->name }} <a href="#" data-id="{{ $child->id }}" class="show_categoryParent"><i class="fa fa-arrow-circle-o-down"></i></a>

							<div class="parentCategory" data-id="{{ $child->id }}">
								<select style="margin-left: 28px;" data-id="{{ $child->id }}" class="js-categories-single-select change_parentCategory">
									@foreach (App\Category::roots()->get() as $root)
						                <option value="{{ $root->id }}" @if ($root->id == $child->parent_id) selected="selected" @endif>
						                    {{ $root->name }}
						                </option>
						            @endforeach
					      		</select>
					      	</div>
							<br>
					      	<span style="margin-left: 28px; color: #aaa; font-size: smaller;">{{ $count[$child->id] }} организаций</span>
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
	$('body').on('click', 'a.show_categoryParent', function(e) {
		e.preventDefault();

		var id = $(this).data('id');
		$('.parentCategory[data-id=' + id + ']').toggle();
	});
	
	$('body').on('change', '.change_parentCategory', function(e) {
		e.preventDefault();
		
		var id = $(this).data('id');
		var parentId = $(this).val();

		$.post('/ajax/category/changeparent', { id: id, parentId: parentId }, function(response) {
			console.log(response);

			if (response.code == 200) {
				location.reload();
			} else {
				alert(response.error);
			}
		});
	});

	$(".js-categories-single-select").select2();

	$('#myTable').DataTable({
		paging: true,
		aaSorting:[]
	});
@stop