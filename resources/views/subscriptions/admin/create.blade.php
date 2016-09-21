@extends('layouts.admin.template')


@section('content')

	<h1 class="page-header">Новая подписка</h1>

	<div class="row">
		<div class="col-md-12">
			<form class="form-horizontal" method="POST" action="{{ route('admin.subscriptions.store') }}">
				{{ csrf_field() }}
				
				<div class="col-md-6">
					{{-- organization_id --}}
					<label for="organization_id">Организация</label>
					<select id="js-data-organizations-ajax" name="organization_id" class="form-control"></select>
					<br><br>
					
					{{-- type --}}
					<label for="type">Подписка</label>
					<select name="type" id="type" class="form-control">
						@foreach (App\Subscription::types() as $key => $type)
							<option value="{{ $key }}">{{ $type }}</option>
						@endforeach
					</select>
					<br><br>

					{{-- year --}}
					<label for="year">Год действия</label>
					<input type="number" name="year" class="form-control" value="{{ $thisYear }}">
					<br><br>

					<br><br>
					<input type="submit" value="Создать">
				</div>
			</form>
		</div>
	</div>

@endsection


@section('scripts_body')


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
   // set form hidden organization id value
});

@endsection