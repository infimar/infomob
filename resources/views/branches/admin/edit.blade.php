@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">{{ $branch->name }}: редактирование</h1>

<div class="row">
	<div class="col-md-12">
		<form class="form-horizontal" method="POST" action="/admin/branches/{{ $branch->id }}">
			<input type="hidden" name="_method" value="PUT">
			{{ csrf_field() }}

			{{-- GENERAL INFO --}}
			<fieldset>
				<legend>Общая информация</legend>

				<div class="form-group">
			    	<label for="inputName" class="col-sm-2 control-label">Наименование</label>
			    	<div class="col-sm-10">
			      		<input type="text" class="form-control" id="inputName" name="branch_name" placeholder="Наименование" value="{{ $branch->name }}">
			    	</div>
			  	</div>

			  	<div class="form-group">
			    	<label for="inputDescription" class="col-sm-2 control-label">Описание | Деятельность | Услуги |</label>
			    	<div class="col-sm-10">
			      		<textarea class="form-control" id="inputDescription" name="branch_description" placeholder="Описание" rows="10">{{ $branch->description }}</textarea>
			    	</div>
			  	</div>

				<div class="form-group">
			    	<label for="inputCategoryId" class="col-sm-2 control-label">Категория</label>
			    	<div class="col-sm-4">
			      		<select id="inputCategoryId" name="branch_categoryIds[]" class="form-control js-categories-multiple-select" multiple="multiple">
							<option></option>

							@foreach (App\Category::dropdown() as $section => $categories)
				            	<optgroup label="{{ $section }}">
									@foreach ($categories as $key => $name)
						                <option value="{{ $key }}"
											@if ($branch->categories->contains("id", $key)) selected="selected" @endif 
						                >
						                    {{ $name }}
						                </option>
					                @endforeach
				                </optgroup>
				            @endforeach
			      		</select>
			    	</div>
			  	</div>

				<div class="form-group">
			    	<label for="inputCityId" class="col-sm-2 control-label">Город</label>
			    	<div class="col-sm-4">
			      		<select id="inputCityId" name="branch_cityId" class="form-control js-cities-single-select">
			      			<option></option>

							@foreach (App\City::orderBy('order')->get() as $city)
								<option value="{{ $city->id }}" @if ($city->id == $branch->city_id) selected="selected" @endif>{{ $city->name }}</option>
							@endforeach
			      		</select>
			    	</div>
			  	</div>			  	

			  	<div class="form-group">
			    	<label for="inputAddress" class="col-sm-2 control-label">Адрес</label>
			    	<div class="col-sm-4">
			      		<input type="text" class="form-control" id="inputAddress" name="branch_address" placeholder="Адрес" value="{{ $branch->address }}">
			    	</div>
			  	</div>

			  	<div class="form-group">
			    	<label for="inputPostIndex" class="col-sm-2 control-label">Почтовый индекс</label>
			    	<div class="col-sm-4">
			      		<input type="text" class="form-control" id="inputPostIndex" name="branch_postIndex" placeholder="Почтовый индекс" value="{{ $branch->post_index }}">
			    	</div>
			  	</div>

			  	<div class="form-group">
			    	<label for="inputEmail" class="col-sm-2 control-label">Email</label>
			    	<div class="col-sm-4">
			      		<input type="email" class="form-control" id="inputEmail" name="branch_email" placeholder="Email" value="{{ $branch->email }}">
			    	</div>
			  	</div>

			  	<div class="form-group">
			    	<label for="inputGeo" class="col-sm-2 control-label">Геопозиция</label>
			    	<div class="col-sm-4">
			      		<input type="text" class="form-control" id="inputGeo" name="branch_lat" placeholder="0.00" value="{{ $branch->lat }}">
			    	</div>
			    	<div class="col-sm-4">
			      		<input type="text" class="form-control" id="inputGeo" name="branch_lng" placeholder="0.00" value="{{ $branch->lng }}">
			    	</div>
			  	</div>

			  	<div class="form-group">
			    	<label for="inputWorkingHours" class="col-sm-2 control-label">Часы работы</label>
			    	<div class="col-sm-4">
			      		<input type="text" class="form-control" id="inputWorkingHours" name="branch_workingHours" placeholder="Часы работы" value="{{ $branch->working_hours }}">
			    	</div>
			  	</div>

			  	<div class="form-group">
					<label for="inputBranchStatus" class="col-sm-2 control-label">Статус</label>
			    	<div class="col-sm-4">
			      		<select id="inputBranchStatus" name="branch_status" class="form-control">
							<option value="draft" @if ($branch->status == "draft") selected="true" @endif>Черновик</option>
							<option value="published" @if ($branch->status == "published") selected="true" @endif>Опубликовано</option>
			      		</select>
			    	</div>
		 		</div>
			</fieldset>

			{{-- PHONES --}}
			<fieldset>
				<legend>Контакты</legend>

				<div class="phones_toolbar">
					<a href="#" id="btn_addphone" class="btn btn-default"><i class="fa fa-plus"></i> добавить</a>
				</div>

				<div id="div_phones" style="margin-top:10px;">
					
					@foreach ($branch->phones as $phone)
						<div data-id="{{ $phone->id }}" class="form-group div_phone" style="margin-top: 10px;">
							<div class="col-sm-2">
								<select name="branch_phones[{{ $phone->id }}][type]" class="form-control">
									<option value="work" @if ($phone->type == "work") selected="true" @endif>Рабочий</option>
									<option value="mobile" @if ($phone->type == "mobile") selected="true" @endif>Мобильный</option>
									<option value="fax" @if ($phone->type == "fax") selected="true" @endif>Факс</option>
									<option value="whatsapp" @if ($phone->type == "whatsapp") selected="true" @endif>Whatsapp</option>
									<option value="viber" @if ($phone->type == "viber") selected="true" @endif>Viber</option>
									<option value="telegram" @if ($phone->type == "telegram") selected="true" @endif>Telegram</option>
								</select>
							</div>
							<div class="col-sm-2">
								<input type="text" class="form-control" name="branch_phones[{{ $phone->id }}][code_country]" placeholder="Код страны" value="+7" value="{{ $phone->code_country }}">
							</div>
							<div class="col-sm-2">
								<input type="text" class="form-control" name="branch_phones[{{ $phone->id }}][code_operator]" placeholder="Код оператора" value="{{ $phone->code_operator }}">
							</div>
							<div class="col-sm-3">
								<input type="text" class="form-control" name="branch_phones[{{ $phone->id }}][number]" placeholder="Номер" value="{{ $phone->number }}">
							</div>
							<div class="col-sm-2">
								<input type="text" class="form-control" name="branch_phones[{{ $phone->id }}][contact_person]" placeholder="Контактное лицо" value="{{ $phone->contact_person }}">
							</div>
							<div class="col-sm-1">
								<a href="#" data-id="{{ $phone->id }}" class="btn btn-danger btn_removephone"><i class="fa fa-trash"></i></a>
							</div>
						</div>					
					@endforeach

				</div>
			</fieldset>
	
			{{-- SOCIALS --}}
			<fieldset>
				<legend>Вебсайт и Социальные сети</legend>

				<div class="socials_toolbar">
					<a href="#" id="btn_addsocial" class="btn btn-default"><i class="fa fa-plus"></i> добавить</a>
				</div>

				<div id="div_socials" style="margin-top:10px;">
					@foreach ($branch->socials as $social)
						<div data-id="{{ $social->id }}" class="form-group div_social" style="margin-top: 10px;">
							<div class="col-sm-3">
								<select name="branch_socials[{{ $social->id }}][type]" class="form-control">
									<option value="website" @if ($social->type == "website") selected="selected" @endif>Вебсайт</option>
									<option value="facebook" @if ($social->type == "facebook") selected="selected" @endif>Facebook</option>
									<option value="vk" @if ($social->type == "vk") selected="selected" @endif>VK</option>
									<option value="googleplus" @if ($social->type == "googleplus") selected="selected" @endif>Google +</option>
									<option value="instagram" @if ($social->type == "instagram") selected="selected" @endif>Instagram</option>
									<option value="youtube" @if ($social->type == "youtube") selected="selected" @endif>Youtube</option>
									<option value="twitter" @if ($social->type == "twitter") selected="selected" @endif>Twitter</option>
									<option value="foursquare" @if ($social->type == "foursquare") selected="selected" @endif>Foursquare</option>
									<option value="linkedin" @if ($social->type == "linkedin") selected="selected" @endif>Linkedin</option>
								</select>
							</div>
							<div class="col-sm-5">
								<input type="text" class="form-control" name="branch_socials[{{ $social->id }}][name]" placeholder="Адрес / Название" value="{{ $social->name }}">
							</div>
							<div class="col-sm-3">
								<input type="text" class="form-control" name="branch_socials[{{ $social->id }}][contact_person]" placeholder="Контактное лицо" value="{{ $social->contact_person }}">
							</div>
							<div class="col-sm-1">
								<a href="#" data-id="{{ $social->id }}" class="btn btn-danger btn_removesocial"><i class="fa fa-trash"></i></a>
							</div>
						</div>
					@endforeach
				</div>
			</fieldset>

			<fieldset>
				<legend>Тэги</legend>

				<div class="row">
					<col-md-6>
						<input class="form-control" type="text" id="tags" name="tags" placeholder="Тэги" class="tm-input"/>
						<input type="hidden" id="branch_tags" name="branch_tags">
					</col-md-6>
				</div>				
			</fieldset>

			<fieldset>
				<legend>&nbsp;</legend>
		  		<div class="form-group">
			    	<div class="col-sm-offset-2 col-sm-10">
			    		<a href="{{ $backUrl }}" class="btn btn-default">Отмена</a>
			      		<button type="submit" class="btn btn-primary">Сохранить изменения</button>
			    	</div>
		  		</div>
		  	</fieldset>
		</form>
	</div>
</div>


@endsection


@section('scripts_body')

$('#tags').tagsManager({
	prefilled: tags,
    CapitalizeFirstLetter: true,
});

$(".js-cities-single-select").select2({
  placeholder: "Выберите город",
  allowClear: true
});

$(".js-categories-multiple-select").select2({
  placeholder: "Выберите категорию",
  //allowClear: true
});

// add phone
$('#btn_addphone').click(function(e) {
	e.preventDefault();
	
	//alert($inc);
	phoneId += 1;

	$('<div data-id="' + phoneId + '" class="form-group div_phone" style="margin-top: 10px;"><div class="col-sm-2"><select name="branch_phones[' + phoneId + '][type]" class="form-control"><option value="work" selected="selected">Рабочий</option><option value="mobile">Мобильный</option><option value="fax">Факс</option><option value="whatsapp">Whatsapp</option><option value="viber">Viber</option><option value="telegram">Telegram</option></select></div><div class="col-sm-2"><input type="text" class="form-control" name="branch_phones[' + phoneId + '][code_country]" placeholder="Код страны" value="+7"></div><div class="col-sm-2"><input type="text" class="form-control" name="branch_phones[' + phoneId + '][code_operator]" placeholder="Код оператора"></div><div class="col-sm-3"><input type="text" class="form-control" name="branch_phones[' + phoneId + '][number]" placeholder="Номер"></div><div class="col-sm-2"><input type="text" class="form-control" name="branch_phones[' + phoneId + '][contact_person]" placeholder="Контактное лицо"></div><div class="col-sm-1"><a href="#" data-id="' + phoneId + '" class="btn btn-danger btn_removephone"><i class="fa fa-trash"></i></a></div></div>').appendTo('#div_phones');
});

// remove phone
$('body').on('click', 'a.btn_removephone', function(e) {
	e.preventDefault();
	var id = $(this).data('id');

	$('.div_phone[data-id=' + id + ']').remove();
});

// add social
$('#btn_addsocial').click(function(e) {
	e.preventDefault();
	
	//alert($inc);
	socialId += 1;

	$('<div data-id="' + socialId + '" class="form-group div_social" style="margin-top: 10px;"><div class="col-sm-3"><select name="branch_socials[' + socialId + '][type]" class="form-control"><option value="website" selected="selected">Вебсайт</option><option value="facebook">Facebook</option><option value="vk">VK</option><option value="googleplus">Google +</option><option value="instagram">Instagram</option><option value="youtube">Youtube</option><option value="twitter">Twitter</option><option value="foursquare">Foursquare</option><option value="linkedin">Linkedin</option></select></div><div class="col-sm-5"><input type="text" class="form-control" name="branch_socials[' + socialId + '][name]" placeholder="Адрес / Название"></div><div class="col-sm-3"><input type="text" class="form-control" name="branch_socials[' + socialId + '][contact_person]" placeholder="Контактное лицо"></div><div class="col-sm-1"><a href="#" data-id="' + socialId + '" class="btn btn-danger btn_removesocial"><i class="fa fa-trash"></i></a></div></div>').appendTo('#div_socials');
});

// remove social
$('body').on('click', 'a.btn_removesocial', function(e) {
	e.preventDefault();
	var id = $(this).data('id');

	$('.div_social[data-id=' + id + ']').remove();
});


@endsection