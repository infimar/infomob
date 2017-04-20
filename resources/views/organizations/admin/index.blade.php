@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">Организации</h1>

<a href="/admin/organizations/create" class="btn btn-lg btn-primary"><i class="fa fa-plus"></i> Добавить организацию</a>
<br><br>

<hr>
<div class="row">
	<div class="col-md-12">
		{{-- Organization search --}}
		<div id="div_admin_organization_search">
			<select id="js-data-organizations-ajax" name="organization_id" class=""></select>
		</div>

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
			                <option value="{{ $key }}"
			                	@if ($chosenCategory->id == $key) selected @endif 
			                >
			                    {{ $name }}
			                </option>
		                @endforeach
	                </optgroup>
	            @endforeach
	        </select>
	    </div>
	</div>
</div>
<hr>

<div class="row">
	<div class="col-md-12">
		
		<table id="myTable" class="table table-stripped table-hover table-condensed" cellspacing="0" width="100%">
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
						<td width="33%">
							<a href="/admin/organizations/{{ $organization->id }}/edit">
								{{ $organization->name }}
							</a>
						</td>
						<td>{{ str_limit($organization->description, 100) }}</td>
						<td width="160px">
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
						<td>@if ($organization->created_at) {{ $organization->created_at->format('d/m/Y H:i:s') }} @else не указано @endif</td>
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
		
		{{ $organizations->links() }}
	</div>
</div>


@stop


@section('scripts_body')
// TODO: DRY!
// ORGANIZATIONS
function formatOrganization (org) {
  	var markup = org.name;

  	return markup;
}

function formatOrganizationSelection (org) {
	return org.name;
}

var select2 = $("#js-data-organizations-ajax");

$("#js-data-organizations-ajax").select2({
  	placeholder: 'Выберите организацию',
  	ajax: {
	    url: "{{ route('ajax.organizations.by_name') }}",
	    dataType: 'json',
	    delay: 250,
	    data: function (params) {
	  		return {
	        	q: params.term, // search term
	        	page: params.page
	      	};
	    },
	    processResults: function (data, params) {
	      // parse the results into the format expected by Select2
	      // since we are using custom formatting functions we do not need to
	      // alter the remote JSON data, except to indicate that infinite
	      // scrolling can be used
	     	params.page = params.page || 1;

	      	return {
	        	results: data.items,
	        	pagination: {
	          		more: (params.page * 30) < data.total_count
	        	}
	      	};
	    },
	    cache: true,
  	},
  	minimumInputLength: 2,
  	escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
  	templateResult: formatOrganization, // omitted for brevity, see the source of this page
  	templateSelection: formatOrganizationSelection // omitted for brevity, see the source of this page
}).on("select2:select", function(e) { 
   var id = $(this).val();
   location.href = "/admin/organizations/" + id + "/edit";
});
@stop