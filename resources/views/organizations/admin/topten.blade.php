@extends('layouts.admin.template')


@section('content')

<h1 class="page-header">ТОП-10</h1>

<div class="row">
	<div class="col-md-12">
		<div id="div_admin_citypicker">
			<a class="btn btn-info" href="/admin/organizations">Назад</a>
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
	<div class="col-md-1">
		<ul id="" style="list-style-type: none; margin: 0; padding: 0;">
			@foreach ($toptens as $key => $topten)
		  		<li style="text-align: center; font-size: 32px; height: 48px; line-height: 48px; border: 1px solid #000; margin: 0 5px 5px 5px;">{{ $key + 1 }}</li>
		  	@endforeach
		</ul>
	</div>

	<div class="col-md-9">
		{{-- TOP Organizations --}}
		<ul id="sortable">
			@foreach ($toptens as $key => $topten)
		  		<li id="item-{{ $topten->organization_id }}" class="ui-state-default">
		  			{{ $topten->name }}
		  			<button data-id="{{ $topten->organization_id }}" class="btn_removeFromTop btn btn-danger" style="float: right"><i class="fa fa-trash"></i></button>
	  			</li>
		  	@endforeach
		</ul>
	</div>
</div>

@endsection


@section('scripts_body')

$( "#sortable" ).sortable({
	placeholder: "ui-state-highlight",
	axis: 'y',

	update: function (event, ui) {
        var input = $(this).sortable('serialize');
		var data = {
			cityId: chosenCity.id,
			categoryId: chosenCategory.id,
			input: input
		};
        
        $.post('/ajax/topten/reorder', { data: data }, function(response) {
			console.log(response);
        });
    }
});
    
$( "#sortable" ).disableSelection();

$('.btn_removeFromTop').click(function(e) {
	e.preventDefault();
	
	var id = $(this).data('id');

	var data = {
		cityId: chosenCity.id,
		categoryId: chosenCategory.id,
		organizationId: id
	};

	$.post('/ajax/topten/remove', { data: data }, function(response) {
		if (response.code == 200) {
			location.reload();
		}
	});
})

@endsection