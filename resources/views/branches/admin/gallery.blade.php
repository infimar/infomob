@extends('layouts.admin.template')

@section('content')

<h1 class="page-header">{{ $branch->name }}: фотографии</h1>

{{-- EXISTING PHOTOS --}}
<div class="row">
	<div class="col-md-8">
		@foreach ($photos as $photo)
			<div class="photo" data-id="{{ $photo->id }}" data-description="{{ $photo->description }}">
				<img src="{{ asset('images/photos/' . $photo->path) }}"><br>
				{{-- <span>{{ $icon }}</span> --}}
			</div>
		@endforeach
	</div>

	<div style="clear:both;"></div>
</div>


{{-- FORM --}}
<div class="row">
	<div class="col-md-8">
		<h3>Новые фотографии</h3>
		<hr>
		<form method="POST" action="/admin/mediamanager/upload/photo" class="dropzone" id="myAwesomeDropzone" enctype="multipart/form-data">
			{{ csrf_field() }}
			<input type="hidden" name="branch_id" value="{{ $branch->id }}">

			<div class="dz-message"></div>

	        <div class="fallback">
	            <input name="file" type="file" multiple />
	        </div>

	        <div class="dropzone-previews" id="dropzonePreview"></div>

	        <h4 style="text-align: center;color:#428bca;">Drop photos in this area  <span class="glyphicon glyphicon-hand-down"></span></h4>
		</form>
	</div>
</div>

<!-- Dropzone Preview Template -->
<div id="preview-template" style="display: none;">

    <div class="dz-preview dz-file-preview">
        <div class="dz-image"><img data-dz-thumbnail=""></div>

        <div class="dz-details">
            <div class="dz-size"><span data-dz-size=""></span></div>
            <div class="dz-filename"><span data-dz-name=""></span></div>
        </div>
        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
        <div class="dz-error-message"><span data-dz-errormessage=""></span></div>

        <div class="dz-success-mark">
            <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                <!-- Generator: Sketch 3.2.1 (9971) - http://www.bohemiancoding.com/sketch -->
                <title>Check</title>
                <desc>Created with Sketch.</desc>
                <defs></defs>
                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                    <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF" sketch:type="MSShapeGroup"></path>
                </g>
            </svg>
        </div>

        <div class="dz-error-mark">
            <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                <!-- Generator: Sketch 3.2.1 (9971) - http://www.bohemiancoding.com/sketch -->
                <title>error</title>
                <desc>Created with Sketch.</desc>
                <defs></defs>
                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                    <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475">
                        <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup"></path>
                    </g>
                </g>
            </svg>
        </div>

    </div>
</div>
<!-- End Dropzone Preview Template -->


<div id="photoModal" class="modal fade" tabindex="-1" role="dialog">
  	<div class="modal-dialog">
    	<div class="modal-content">
      		<div class="modal-header">
        		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        		<h4 class="modal-title">Редактирование фотографии</h4>
      		</div>

      		<div class="modal-body">
        		<form class="form-horizontal" method="POST" action="#">
					{{ csrf_field() }}

					<input type="hidden" name="_method" value="PUT">
					<input type="hidden" name="photo_id" id="photoId">

					<div class="form-group">
				    	<label for="inputDescription" class="col-sm-2 control-label">Описание</label>
				    	<div class="col-sm-10">
				      		<input type="text" class="form-control" id="inputDescription" name="description" placeholder="Описание">
				    	</div>
				  	</div>

				  	<div class="form-group">
				    	<label for="btnDelete" class="col-sm-2 control-label">&nbsp;</label>
				    	<div class="col-sm-10">				      		
				      		<button id="btnDelete" class="btn btn-danger"><i class="fa fa-trash"></i> Удалить</button>
				      		<img src="{{ asset("images/imageloader.gif") }}" class="imageLoader gone" id="progressBar">
				    	</div>
				  	</div>

				</form>
      		</div>

      		<div class="modal-footer">
       	 		<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        		<button type="button" class="btn btn-primary" id="btnSave">Сохранить изменения</button>
      		</div>
    	</div><!-- /.modal-content -->
  	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection


@section('scripts_body')

$('.photo').click(function(e) {
	var photoId = $(this).data('id');
	var description = $(this).data('description');

	$('#photoId').val(photoId);
	$('#inputDescription').val(description);

	$('#photoModal').modal('show');
});

$('#btnDelete').click(function(e) {
	e.preventDefault();

	var id = $('#photoId').val();

	$('#progressBar').removeClass('gone').addClass('visible');

	$.post('/admin/mediamanager/deletebyid/photo', { id: id }, function(response) {
		if (response.code == 200) {
			// removed successfully
			$('#progressBar').removeClass('visible').addClass('gone');
			$('#photoModal').modal('hide');
			$('.photo[data-id=' + id + ']').remove();
		}
	});
});

$('#btnSave').click(function(e) {
	e.preventDefault();

	var id = $('#photoId').val();
	var description = $('#inputDescription').val();
	
	$.post('/admin/mediamanager/update/photo', { id: id, description: description }, function(response) {
		if (response.code == 200) {
			$('.photo[data-id=' + id + ']').data('description', description);
			$('#photoModal').modal('hide');
		}
	});
});




Dropzone.options.myAwesomeDropzone = {
	acceptedFiles: "image/*",
	uploadMultiple: false,
    parallelUploads: 100,
    maxFilesize: 2,
    previewsContainer: '#dropzonePreview',
    previewTemplate: document.querySelector('#preview-template').innerHTML,
    dictFileTooBig: 'Image is bigger than 2 MB',

    // The setting up of the dropzone
    error: function(file, response) {
        if ($.type(response) === "string")
            var message = response; //dropzone sends it's own error messages in string
        else
            var message = response.message;

        file.previewElement.classList.add("dz-error");
        _ref = file.previewElement.querySelectorAll("[data-dz-errormessage]");
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i];
            _results.push(node.textContent = message);
        }

        return _results;
    },
    success: function(file, response) {
		console.log(response);

        var removeButton = Dropzone.createElement('<a class="dz-remove">Удалить</a>');
        var _this = this;

        removeButton.addEventListener("click", function(e) {
          	e.preventDefault();
          	e.stopPropagation();

          	_this.removeFile(file);

          	// If you want to the delete the file on the server as well,
          	// you can do the AJAX request here.
          	$.ajax({
                type: "POST",
                url: "/admin/mediamanager/delete/photo",
                data: { photo: response.photo, },
                beforeSend: function () {
                    // before send
                },
                success: function (response) { 
                    //if (response.code == 200) alert('deleted');
                },
                error: function () {
                    alert("Ошибка");
                }
            });
        });


        // Add the button to the file preview element.
        file.previewElement.appendChild(removeButton);
  	}
};

@endsection