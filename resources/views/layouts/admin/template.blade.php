<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="../../favicon.ico">

    <title>Админ панель | Infomob.kz</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="{{ asset('css/ie10-viewport-bug-workaround.css') }}" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">

    <!-- Data tables -->
    <link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">

    <link href="{{ asset('css/dropzone.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tagmanager.css') }}" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
      @yield('styles')  
    </style>
    
  </head>

  <body>

    @include('layouts.admin.partials._top_nav')

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          @include('layouts.admin.partials._side_nav')
        </div>
        
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          @include('flash::message')

          @yield('content')
        </div>
      </div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
    
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="{{ asset('js/ie10-viewport-bug-workaround.js') }}"></script>

    <!-- Data tables -->
    <script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>

    <!-- Drop zone -->
    <script src="{{ asset('js/dropzone.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('js/select2.full.min.js') }}"></script>

    <!-- Tagmanager -->
    <script src="{{ asset('js/tagmanager.js') }}" type="text/javascript" charset="utf-8"></script>

    @yield('scripts_import')
    @include ('layouts.js')
    
    <script>
      @yield('scripts_global')
      
      $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        @yield('scripts_body')

        $('.btn_remove').click(function(e) {
          var yesno = confirm("Are you sure you want to remove?");

          if (!yesno) {
            e.preventDefault();
            return;
          }
        });

        $('#' + activeLink).addClass("active");

        $('#admin_citypicker').change(function(e) {
          var cityId = $('#admin_citypicker').val();

          location.href = "/utils/changecity/" + cityId;
        });

        $('#admin_categorypicker').change(function(e) {
          var categoryId = $('#admin_categorypicker').val();

          location.href = "/utils/changecategory/" + categoryId;
        });

        $('.clickableIcon').click(function() {
          var icon = $(this).data('icon');
          //console.log(icon);

          $('#inputIcon').val(icon);

          $('.icon').removeClass('activeIcon');
          $(this).addClass('activeIcon');
        });

        $('body').on('click', '.btn_toggleStatus', function(e) {
          e.preventDefault();

          var id = $(this).data('id');
          var model = $(this).data('model');

          var data = {
            id: id, 
            model: model
          };

          $('.imageLoader[data-id=' + id + ']').removeClass("visible", "gone").addClass("visible");

          $.post('/ajax/togglestatus', { data: data }, function(result) {
            $('.imageLoader[data-id=' + id + ']').removeClass("visible", "gone").addClass("gone");

            if (result.status == "error") {
              alert("Ошибка: " + result.response);
            } else {
              $('.btn_toggleStatus[data-id=' + id + ']').removeClass().addClass("btn_toggleStatus " + result.response.class).html(result.response.statusLabel);
            }
          });
        });

        // select 2
        $(".js-single-select").select2();
      });
    </script>
  </body>
</html>