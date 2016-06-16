
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Home</title>
    <meta charset="utf-8">
    <meta name="format-detection" content="telephone=no"/>
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('css/grid.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">

    <link rel="stylesheet" href="{{ asset('css/touch-touch.css') }}">

    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/jquery-migrate-1.2.1.js') }}"></script>

    <!--[if lt IE 9]>
    <html class="lt-ie9">
    <div style=' clear: both; text-align:center; position: relative;'>
        <a href="http://windows.microsoft.com/en-US/internet-explorer/..">
            <img src="{{ asset('images/ie8-panel/warning_bar_0000_us.jpg') }}" border="0" height="42" width="820"
                 alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today."/>
        </a>
    </div>
    <script src="js/html5shiv.js"></script>
    <![endif]-->

    <script src='{{ asset('js/device.min.js') }}'></script>
</head>
<body>
<div class="page">
    <!--========================================================
                              HEADER
    =========================================================-->
    <header class="parallax" data-url="{{ asset('images/parallax1.jpg') }}" data-mobile="true" data-speed="0.8">
        <div id="stuck_container" class="stuck_container">
            <div class="container">
                <div class="brand">
                    <h1 class="brand_name">
                        <a href="./">Office Rent</a>
                    </h1>
                </div>

                <nav class="nav">
                    <ul class="sf-menu" data-type="navbar">
                        <li class="active">
                            <a href="./">Home</a>
                        </li>
                        <li>
                            <a href="index-1.html">About Us</a>
                            <ul>
                                <li>
                                    <a href="#">Lorem ipsum dolor</a>
                                </li>
                                <li>
                                    <a href="#">Ait amet conse</a>
                                </li>
                                <li>
                                    <a href="#" class="sub-menu">Ctetur adipisicing elit</a>
                                    <ul>
                                        <li>
                                            <a href="#">Latest</a>
                                        </li>
                                        <li>
                                            <a href="#">Archive</a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#">Sed do eiusmod</a>
                                </li>
                                <li>
                                    <a href="#">Tempor incididunt</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="index-2.html">Our listings</a>
                        </li>
                        <li>
                            <a href="index-3.html">Requests</a>
                        </li>
                        <li>
                            <a href="index-4.html">Contact Us</a>
                        </li>
                    </ul>
                </nav>

                <div class="contact-info">
                    <dl class="text-right">
                        <dt>Call our helpline:</dt>
                        <dd><a href="callto:#">800-1234-5678</a></dd>
                    </dl>
                </div>
            </div>
        </div>

        <section class="well1">
            <div class="container">
                <div class="h1 clr-white text-center">
                    Find office space for small businesses,<br>
                    <small>
                        startups &amp; freelancers - search now!
                    </small>
                </div>

                <form class="search-form" action="search.php" method="GET" accept-charset="utf-8">
                    <label class="search-form_label">
                        <input class="search-form_input" type="text" name="s" autocomplete="off" placeholder="Location,  Size,  Cost "/>
                        <span class="search-form_liveout"></span>
                    </label>
                    <button class="search-form_submit btn btn-primary" type="submit">Search</button>
                </form>
            </div>
        </section>

    </header>
    <!--========================================================
                              CONTENT
    =========================================================-->
    <main>
        <section class="well2">
            <div class="container">
                <h2 class="text-center">
                    Quick guide to finding <span class="clr-primary">office space</span> with Office Rent
                </h2>
                <ul class="row row_offs1 index-list">
                    <li class="col-md-4 col-sm-6 col-xs-12">
                        <h3>
                            Lorem ipsum dolor sit
                        </h3>
                        <p>
                            Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.
                        </p>
                    </li>

                    <li class="col-md-4 col-sm-6 col-xs-12">
                        <h3>
                            Veniam quis nostrud
                        </h3>
                        <p>
                            Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.
                        </p>
                    </li>

                    <li class="col-md-4 col-md-release col-sm-6 col-sm-clear col-xs-12">
                        <h3>
                            Laboris nisi ut aliquip
                        </h3>
                        <p>
                            Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.
                        </p>
                    </li>

                    <li class="col-md-4 col-md-clear col-sm-6 col-xs-12">
                        <h3>
                            Ut enim ad minim
                        </h3>
                        <p>
                            Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.
                        </p>
                    </li>

                    <li class="col-md-4 col-md-release col-sm-6 col-sm-clear col-xs-12">
                        <h3>
                            Exercitation ullamco
                        </h3>
                        <p>
                            Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.
                        </p>
                    </li>

                    <li class="col-md-4 col-sm-6 col-xs-12">
                        <h3>
                            Ex ea commodo co
                        </h3>
                        <p>
                            Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.
                        </p>
                    </li>
                </ul>
            </div>
        </section>

        <section class="well2 bg1 text-center">
            <div class="container">
                <h2>
                    Hot properties
                </h2>

                <div class="row text-left text-xs-center wow fadeIn" data-wow-duration='3s'>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="thumbnail">
                            <a class="thumb" href="{{ asset('images/page-1_img1_original.jpg') }}">
                                <img  src="{{ asset('images/page-1_img1.jpg') }}" alt="">
                                <span class="thumb_overlay"></span>
                            </a>
                            <div class="caption">
                                <p>
                                    Lorem ipsum dolor sit amet conse
                                </p>
                                <p class="price">
                                    150 ft<sup>2</sup>  <span>|</span> $2000 / <small>month</small>
                                </p>
                                <a href="#" class="btn btn-primary">Learn more <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="thumbnail">
                            <a class="thumb" href="{{ asset('images/page-1_img2_original.jpg') }}">
                                <img  src="{{ asset('images/page-1_img2.jpg') }}" alt="">
                                <span class="thumb_overlay"></span>
                            </a>
                            <div class="caption">
                                <p>
                                    Lorem ipsum dolor sit amet conse
                                </p>
                                <p class="price">
                                    150 ft<sup>2</sup>  <span>|</span> $2000 / <small>month</small>
                                </p>
                                <a href="#" class="btn btn-primary">Learn more <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-md-release col-sm-6 col-sm-clear col-xs-12">
                        <div class="thumbnail">
                            <a class="thumb" href="{{ asset('images/page-1_img3_original.jpg') }}">
                                <img  src="{{ asset('images/page-1_img3.jpg') }}" alt="">
                                <span class="thumb_overlay"></span>
                            </a>
                            <div class="caption">
                                <p>
                                    Lorem ipsum dolor sit amet conse
                                </p>
                                <p class="price">
                                    150 ft<sup>2</sup>  <span>|</span> $2000 / <small>month</small>
                                </p>
                                <a href="#" class="btn btn-primary">Learn more <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="thumbnail">
                            <a class="thumb" href="{{ asset('images/page-1_img4_original.jpg') }}">
                                <img  src="{{ asset('images/page-1_img4.jpg') }}" alt="">
                                <span class="thumb_overlay"></span>
                            </a>
                            <div class="caption">
                                <p>
                                    Lorem ipsum dolor sit amet conse
                                </p>
                                <p class="price">
                                    150 ft<sup>2</sup>  <span>|</span> $2000 / <small>month</small>
                                </p>
                                <a href="#" class="btn btn-primary">Learn more <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row text-left text-xs-center wow fadeIn" data-wow-duration='3s'>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="thumbnail">
                            <a class="thumb" href="{{ asset('images/page-1_img5_original.jpg') }}">
                                <img  src="{{ asset('images/page-1_img5.jpg') }}" alt="">
                                <span class="thumb_overlay"></span>
                            </a>
                            <div class="caption">
                                <p>
                                    Lorem ipsum dolor sit amet conse
                                </p>
                                <p class="price">
                                    150 ft<sup>2</sup>  <span>|</span> $2000 / <small>month</small>
                                </p>
                                <a href="#" class="btn btn-primary">Learn more <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="thumbnail">
                            <a class="thumb" href="{{ asset('images/page-1_img6_original.jpg') }}">
                                <img  src="{{ asset('images/page-1_img6.jpg') }}" alt="">
                                <span class="thumb_overlay"></span>
                            </a>
                            <div class="caption">
                                <p>
                                    Lorem ipsum dolor sit amet conse
                                </p>
                                <p class="price">
                                    150 ft<sup>2</sup>  <span>|</span> $2000 / <small>month</small>
                                </p>
                                <a href="#" class="btn btn-primary">Learn more <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-md-release col-sm-6 col-sm-clear col-xs-12">
                        <div class="thumbnail">
                            <a class="thumb" href="{{ asset('images/page-1_img7_original.jpg') }}">
                                <img  src="{{ asset('images/page-1_img7.jpg') }}" alt="">
                                <span class="thumb_overlay"></span>
                            </a>
                            <div class="caption">
                                <p>
                                    Lorem ipsum dolor sit amet conse
                                </p>
                                <p class="price">
                                    150 ft<sup>2</sup>  <span>|</span> $2000 / <small>month</small>
                                </p>
                                <a href="#" class="btn btn-primary">Learn more <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="thumbnail">
                            <a class="thumb" href="{{ asset('images/page-1_img8_original.jpg') }}">
                                <img  src="{{ asset('images/page-1_img8.jpg') }}" alt="">
                                <span class="thumb_overlay"></span>
                            </a>
                            <div class="caption">
                                <p>
                                    Lorem ipsum dolor sit amet conse
                                </p>
                                <p class="price">
                                    150 ft<sup>2</sup>  <span>|</span> $2000 / <small>month</small>
                                </p>
                                <a href="#" class="btn btn-primary">Learn more <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="#" class="btn btn-default btn-lg">See all</a>
            </div>
        </section>

        <section class="well3">
            <div class="container">
                <h2 class="text-center">
                    Popular office searches
                </h2>
                <div class="row">

                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <ul class="marked-list wow fadeInLeft" data-wow-duration='2s'>
                            <li>
                                <a href="#">Desk space</a>
                            </li>
                            <li>
                                <a href="#">Office London</a>
                            </li>
                            <li>
                                <a href="#">Executive office space</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <ul class="marked-list">
                            <li>
                                <a href="#">Flexible office space</a>
                            </li>
                            <li>
                                <a href="#">Serviced offices London</a>
                            </li>
                            <li>
                                <a href="#">Business centres</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <ul class="marked-list wow fadeInRight" data-wow-duration='2s'>
                            <li>
                                <a href="#">Office share</a>
                            </li>
                            <li>
                                <a href="#">Co-working</a>
                            </li>
                            <li>
                                <a href="#">Desk space London</a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <!--========================================================
                              FOOTER
    =========================================================-->
    <footer>
        <section class="well4">
            <div class="container">
                <div class="row">

                    <div class="col-md-3 col-sm-6 col-xs-6">
                        <h3 class="clr-white">
                            About
                        </h3>
                        <img src="{{ asset('images/page-1_img9.jpg') }}" alt="" class="img-full">
                        <p class="clr-darken word-wrap">
                            Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore. Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud.
                        </p>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-6">
                        <h3 class="clr-white">
                            Latest news
                        </h3>

                        <article>
                            <time datetime="2014-11-04">04 November 2014</time>
                            <p class="clr-primary">
                                <a href="#">
                                    Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod
                                </a>
                            </p>
                        </article>
                        <article>
                            <time datetime="2014-11-04">04 November 2014</time>
                            <p class="clr-primary">
                                <a href="#">
                                    Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod
                                </a>
                            </p>
                        </article>
                        <article>
                            <time datetime="2014-11-04">04 November 2014</time>
                            <p class="clr-primary">
                                <a href="#">
                                    Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod
                                </a>
                            </p>
                        </article>
                        <article>
                            <time datetime="2014-11-04">04 November 2014</time>
                            <p class="clr-primary">
                                <a href="#">
                                    Lorem ipsum dolor sit amet conse ctetur adipisicing elit, sed do eiusmod
                                </a>
                            </p>
                        </article>

                        <a href="#" class="btn btn-primary btn-md">See all <span class='fa-angle-double-right'></span></a>
                    </div>

                    <div class="col-md-3 col-md-release col-sm-6 col-sm-clear col-xs-6 col-xs-clear">
                        <h3 class="clr-white">
                            Services
                        </h3>
                        <ul class="marked-list marked-list__mod1">
                            <li>
                                <a href="#"><span>Lorem ipsum dolor sit amet </span></a>
                            </li>
                            <li>
                                <a href="#"><span>Conse ctetur adipisicing </span></a>
                            </li>
                            <li>
                                <a href="#"><span>Elit sed do eiusmod tempor</span></a>
                            </li>
                            <li>
                                <a href="#"><span>Incididunt ut labore</span></a>
                            </li>
                        </ul>

                        <h3 class="clr-white">
                            Events
                        </h3>
                        <ul class="marked-list marked-list__mod1">
                            <li>
                                <a href="#"><span>Lorem ipsum dolor sit amet </span></a>
                            </li>
                            <li>
                                <a href="#"><span>Conse ctetur adipisicing </span></a>
                            </li>
                            <li>
                                <a href="#"><span>Elit sed do eiusmod tempor</span></a>
                            </li>
                            <li>
                                <a href="#"><span>Incididunt ut labore</span></a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-6">

                        <h3 class="clr-white">
                            Facebook
                        </h3>

                        <div class="fb-page" data-href="https://www.facebook.com/TemplateMonster" data-width="270" data-height="230" data-hide-cover="false" data-show-facepile="true" data-show-posts="false">
                            <div class="fb-xfbml-parse-ignore">
                                <blockquote cite="https://www.facebook.com/TemplateMonster">
                                    <a href="https://www.facebook.com/TemplateMonster">TemplateMonster</a>
                                </blockquote>
                            </div>
                        </div>

                        <h3 class="clr-white">
                            Follow Us
                        </h3>

                        <ul class="inline-list">
                            <li><a href="#" class="fa fa-facebook"></a></li>
                            <li><a href="#" class="fa fa-twitter"></a></li>
                            <li><a href="#" class="fa fa-skype"></a></li>
                        </ul>

                    </div>


                </div>
            </div>
        </section>

        <section class="rights">
            <div class="container">
                <p>
                    Office Rent &#169; <span id="copyright-year"></span>.
                    <a href="index-5.html">Privacy Policy</a>
                    <!-- {%FOOTER_LINK} -->
                </p>
            </div>
        </section>
    </footer>
</div>

<script src="{{ asset('js/script.js') }}"></script>
<!-- begin olark code -->
<script data-cfasync="false" type='text/javascript'>
    /*<![CDATA[*/window.olark||(function(c){var f=window,d=document,l=f.location.protocol=="https:"?"https:":"http:",z=c.name,r="load";var nt=function(){
        f[z]=function(){
            (a.s=a.s||[]).push(arguments)};var a=f[z]._={
        },q=c.methods.length;while(q--){(function(n){f[z][n]=function(){
            f[z]("call",n,arguments)}})(c.methods[q])}a.l=c.loader;a.i=nt;a.p={
            0:+new Date};a.P=function(u){
            a.p[u]=new Date-a.p[0]};function s(){
            a.P(r);f[z](r)}f.addEventListener?f.addEventListener(r,s,false):f.attachEvent("on"+r,s);var ld=function(){function p(hd){
            hd="head";return["<",hd,"></",hd,"><",i,' onl' + 'oad="var d=',g,";d.getElementsByTagName('head')[0].",j,"(d.",h,"('script')).",k,"='",l,"//",a.l,"'",'"',"></",i,">"].join("")}var i="body",m=d[i];if(!m){
            return setTimeout(ld,100)}a.P(1);var j="appendChild",h="createElement",k="src",n=d[h]("div"),v=n[j](d[h](z)),b=d[h]("iframe"),g="document",e="domain",o;n.style.display="none";m.insertBefore(n,m.firstChild).id=z;b.frameBorder="0";b.id=z+"-loader";if(/MSIE[ ]+6/.test(navigator.userAgent)){
            b.src="javascript:false"}b.allowTransparency="true";v[j](b);try{
            b.contentWindow[g].open()}catch(w){
            c[e]=d[e];o="javascript:var d="+g+".open();d.domain='"+d.domain+"';";b[k]=o+"void(0);"}try{
            var t=b.contentWindow[g];t.write(p());t.close()}catch(x){
            b[k]=o+'d.write("'+p().replace(/"/g,String.fromCharCode(92)+'"')+'");d.close();'}a.P(2)};ld()};nt()})({
        loader: "static.olark.com/jsclient/loader0.js",name:"olark",methods:["configure","extend","declare","identify"]});
    /* custom configuration goes here (www.olark.com/documentation) */
    olark.identify('7830-582-10-3714');/*]]>*/</script>
<noscript>
    <a href="https://www.olark.com/site/7830-582-10-3714/contact" title="Contact us" target="_blank">Questions? Feedback?</a> powered by <a href="http://www.olark.com?welcome" title="Olark live chat software">Olark live chat software</a>
</noscript>
<!-- end olark code -->

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
<!-- Google Tag Manager -->
<noscript>
    <iframe src="//www.googletagmanager.com/ns.html?id=GTM-K4KCT2" height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<script>
(function(w,d,s,l,i){
    w[l]=w[l]||[];
    w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
    var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
    j.async=true;
    j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;
    f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-K4KCT2');
</script>
<!-- End Google Tag Manager -->
</html>