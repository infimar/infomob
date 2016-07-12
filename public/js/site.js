$('#citypicker').change(function(e) {
	var cityId = $('#citypicker').val();

	location.href = "/utils/changecity/" + cityId;
});