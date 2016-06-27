@extends('layouts.frontend.template')

@section('title')
    Главная
@endsection

@section('slider')
    class="parallax" data-url="{{ asset('images/parallax1.jpg') }}" data-mobile="true" data-speed="0.8"
@endsection
@section('search')
    <section class="well1">
        <div class="container">
            <div class="h1 clr-white text-center">
                Найдите то, что искали
            </div>


            {!! Form::open(array('url' => '', 'class' => 'search-form')) !!}
                <label class="search-form_label">
                    {{Form::text('s', $first_name = null, array('class' => 'search-form_input', 'placeholder' => 'Компании,  Сервисы,  Банкоматы'))}}
                    <span class="search-form_liveout"></span>
                </label>
                {{Form::submit('Поиск', array('class' => 'search-form_submit btn btn-primary'))}}
            {!! Form::close() !!}
        </div>
    </section>
@endsection

@section('content')
    <main>
        <section class="well2">
            <div class="container">
                <h2 class="text-center">
                    Выберите категорию
                </h2>
                <div class="category_main row text-xs-center">
                    <div class="category_row">
                        @for ($i = 1; $i <= 6; $i++)
                        <a href="#" >
                            <div class="category_item">
                                <img src="images/icons/{{ $i }}.png" class="wow fadeInRight" data-wow-duration='1s'><div class="category_name wow fadeInRight" data-wow-duration='2s'>Торговые комплексы, рынки и магазины</div>
                            </div>
                            <div class="borderRight"></div>
                            <div class="borderBottom"></div>
                        </a>
                        @endfor
                    </div>
                    <div class="category_row">
                        @for ($i = 7; $i <= 12; $i++)
                            <a href="#" >
                                <div class="category_item">
                                    <img src="images/icons/{{ $i }}.png" class="wow fadeInRight" data-wow-duration='1s'><div class="category_name wow fadeInRight" data-wow-duration='2s'>Торговые комплексы, рынки и магазины</div>
                                </div>
                                <div class="borderRight"></div>
                                <div class="borderBottom"></div>
                            </a>
                        @endfor
                    </div>
                    <div class="category_row">
                        @for ($i = 13; $i <= 18; $i++)
                            <a href="#" >
                                <div class="category_item">
                                    <img src="images/icons/{{ $i }}.png" class="wow fadeInRight" data-wow-duration='1s'><div class="category_name wow fadeInRight" data-wow-duration='2s'>Торговые комплексы, рынки и магазины</div>
                                </div>
                                <div class="borderRight"></div>
                                <div class="borderBottom"></div>
                            </a>
                        @endfor
                    </div>
                    <div class="category_row">
                        @for ($i = 19; $i <= 24; $i++)
                            <a href="#" >
                                <div class="category_item">
                                    <img src="images/icons/{{ $i }}.png" class="wow fadeInRight" data-wow-duration='1s'><div class="category_name wow fadeInRight" data-wow-duration='2s'>Торговые комплексы, рынки и магазины</div>
                                </div>
                                <div class="borderRight"></div>
                                <div class="borderBottom"></div>
                            </a>
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <section class="well2 bg1 text-center">
            <div class="container">
                <h2>
                    Популярные
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="well3">
            <div class="container">
                <h2 class="text-center">
                    Недавно добавленные
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                <a href="#" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
@endsection