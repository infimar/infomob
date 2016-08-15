
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>@yield ('title') | Infomob.kz</title>
    <meta charset="utf-8">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/icons/favicon.ico') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('css/grid.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('css/touch-touch.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsiveslides.css') }}">



    <!--[if lt IE 9]>
    <html class="lt-ie9">
    <div style=' clear: both; text-align:center; position: relative;'>
        <a href="http://windows.microsoft.com/en-US/internet-explorer/..">
            <img src="{{ asset('images/ie8-panel/warning_bar_0000_us.jpg') }}" border="0" height="42" width="820"
                 alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today."/>
        </a>
    </div>
    <script src="{{ asset('js/html5shiv.js') }}"></script>
    <![endif]-->
</head>
<body>
<div class="page">
    <!--========================================================
                              HEADER
    =========================================================-->
    <header>


        @include('layouts.frontend.partials._nav')

        @yield('search')
        @yield('slider')

    </header>

    <main>
        <!-- BREADCRUMBS -->
        @yield('breadcrumbs')

        <!--========================================================
                                  CONTENT
        =========================================================-->
        @yield('content')
    </main>
    <!--========================================================
                              FOOTER
    =========================================================-->
    @include('layouts.frontend.partials._footer')

</div>

<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('js/jquery-migrate-1.2.1.js') }}"></script>
<script src="{{ asset ('js/bootstrap.min.js') }}"></script>
<script src='{{ asset('js/device.min.js') }}'></script>
<script src="{{ asset('js/script.js') }}"></script>
<script src="{{ asset ('js/core.min.js') }}"></script>
<script src="{{ asset ('js/responsiveslides.min.js') }}"></script>

@include ('layouts.js')

<script>
$(document).ready(function() {
    @yield('scripts')
});

</script>


<!-- Google Analytics -->
<script>
(function(i,s,o,g,r,a,m){
    i['GoogleAnalyticsObject']=r;
    i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();
    a=s.createElement(o),m=s.getElementsByTagName(o)[0];
    a.async=1;
    a.src=g;
    m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-69962808-5', 'auto');
ga('send', 'pageview');
</script>
<!-- End Google Analytics -->
</body>
</html>