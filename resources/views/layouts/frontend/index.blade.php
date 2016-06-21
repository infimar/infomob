@extends('layouts.frontend.template')

@section('slider')
    class="parallax" data-url="{{ asset('images/parallax1.jpg') }}" data-mobile="true" data-speed="0.8"
@endsection
@section('search')
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
@endsection

@section('content')
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
@endsection