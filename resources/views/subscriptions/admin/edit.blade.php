@extends('layouts.admin.template')


@section('content')

	<h1 class="page-header">Редактирование подписки</h1>

	<div class="row">
		<div class="col-md-12">
			<form class="form-horizontal" method="POST" action="{{ route('admin.subscriptions.update', ['id' => $subscription->id]) }}">
				{{ csrf_field() }}
				{{ method_field('PUT') }}
				
				<div class="col-md-6">
					{{-- organization_id --}}
					<label for="organization_id">Организация</label>
					<select id="js-data-organizations-ajax" name="organization_id" class="form-control">
						<option value="{{ $subscription->organization->id }}" selected="selected">
							{{ $subscription->organization->name }}
						</option>
					</select>
					<br><br>
					
					{{-- type --}}
					<label for="type">Подписка</label>
					<select name="type" id="type" class="form-control">
						@foreach (App\Subscription::types() as $key => $type)
							<option value="{{ $key }}" @if ($subscription->type == $key) selected="selected" @endif>
								{{ $type }}
							</option>
						@endforeach
					</select>
					<br><br>

					{{-- year --}}
					<label for="year">Год действия</label>
					<input type="number" name="year" class="form-control" value="{{ $subscription->year }}">
					<br><br>

					<br><br>
					<input type="submit" value="Сохранить изменения">
				</div>
			</form>
		</div>
	</div>

@endsection


@section('scripts_body')


// ORGANIZATIONS
function formatOrganization (org) {
	if (org.loading) return org.text;

  	var markup = org.name;
  	return markup;
}

function formatOrganizationSelection (org) {
	return org.name || org.text;
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
	        	page: params.page,
	        	city_id: {{ $chosenCity->id }}
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