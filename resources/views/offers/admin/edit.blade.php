@extends('layouts.admin.template')


@section('content')

	<h1 class="page-header">Редактирование акции</h1>

	<div class="row">
		<div class="col-md-12">
			<form class="form-horizontal" method="POST" action="{{ route('admin.offers.update', ['id' => $offer->id]) }}" enctype="multipart/form-data">
				{{ csrf_field() }}
				{{ method_field('PUT') }}
				
				<div class="col-md-6">
					{{-- organization_id --}}
					<label for="organization_id">Организация</label>
					<select id="js-data-organizations-ajax" name="organization_id" class="form-control">
						<option value="{{ $offer->organization->id }}" selected="selected">
							{{ $offer->organization->name }}
						</option>
					</select>
					<br><br>
					
					{{-- cities --}}
					<label for="cities">Город(-а)</label>
					<select name="cities[]" id="js-data-cities" id="cities" class="form-control" multiple="true"></select>
					<br><br>

					{{-- image --}}
					<label for="image">Изображение / Картинка</label>
					<br>
					<img src="{{ asset('/images/offers/' . $offer->image) }}" alt="" style="height: 240px;">
					<br><br>
					<input type="file" name="image" class="form-control">
					<br><br>
					
					{{-- description --}}
					<label for="description">Описание акции</label>
					<textarea name="description" id="description" class="form-control">{{ $offer->description }}</textarea>
					<br><br>

					{{-- date --}}
					<div class="row">
						<div class="col-md-6">
							<label for="date_start">Дата начала</label>
							<input type="date" name="date_start" class="form-control" value="{{ $offer->date_start->format('Y-m-d') }}">
						</div>
						<div class="col-md-6">
							<label for="date_end">Дата завершения</label>
							<input type="date" name="date_end" class="form-control" value="{{ $offer->date_end->format('Y-m-d') }}">
						</div>
					</div>

					<br><br>
					<input type="submit" value="Сохранить изменения">
				</div>
			</form>
		</div>
	</div>

@endsection


@section('scripts_body')

// CITY
var cityIds = [];
for (var i in cities) {
	cityIds.push(cities[i].id);
}

$('#js-data-cities').select2({
	data: cities,
	placeholder: 'Выберите город'
});

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
});


// SET OFFER VALUES
$('#js-data-cities').val(chosenCities).trigger('change');

@endsection