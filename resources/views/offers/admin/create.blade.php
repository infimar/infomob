@extends('layouts.admin.template')


@section('content')

	<h1 class="page-header">Новая акция</h1>

	<div class="row">
		<div class="col-md-12">
			<form class="form-horizontal" method="POST" action="{{ route('admin.offers.store') }}" enctype="multipart/form-data">
				{{ csrf_field() }}
				
				<div class="col-md-6">
					{{-- organization_id --}}
					<label for="organization_id">Организация</label>
					<select id="js-data-organizations-ajax" name="organization_id" class="form-control"></select>
					<br><br>
					
					{{-- cities --}}
					<label for="cities">Город(-а)</label>
					<select name="cities[]" id="js-data-cities" id="cities" class="form-control" multiple="multiple"></select>
					<br>
					<a href="#" id="chooseAllCities">Выбрать все города</a>
					<br><br>

					{{-- image --}}
					<label for="image">Изображение / Картинка</label>
					<input type="file" name="image" class="form-control">
					<br><br>
					
					{{-- description --}}
					<label for="description">Описание акции</label>
					<textarea name="description" id="description" class="form-control"></textarea>
					<br><br>

					{{-- date --}}
					<div class="row">
						<div class="col-md-6">
							<label for="date_start">Дата начала</label>
							<input type="date" name="date_start" class="form-control">
						</div>
						<div class="col-md-6">
							<label for="date_end">Дата завершения</label>
							<input type="date" name="date_end" class="form-control">
						</div>
					</div>

					<br><br>
					<input type="submit" value="Создать">
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
console.log(cityIds);

$('#js-data-cities').select2({
	data: cities,
	placeholder: 'Выберите город'
});

$('#chooseAllCities').click(function(e) {
	e.stopPropagation();

	$('#js-data-cities').val(cityIds).trigger('change');
})

// ORGANIZATIONS
function formatOrganization (org) {
  	var markup = org.name + " - " + org.status;

  	return markup;
}

function formatOrganizationSelection (org) {
	return org.name + " - " + org.status;
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