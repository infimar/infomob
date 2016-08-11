@extends('layouts.frontend.template')

@section('title')
    Главная
@endsection

@section('breadcrumbs')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                {!! Breadcrumbs::render('home') !!}
            </div>
        </div>
    </div>
@endsection

@section('slider')
    <div id="carousel-example-generic" class="carousel slide" data-ride="carousel" style="position: absolute; top: 0; z-index: -1;">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
            <li data-target="#carousel-example-generic" data-slide-to="1"></li>
            <li data-target="#carousel-example-generic" data-slide-to="2"></li>
        </ol>

        <!-- Wrapper for slides -->
        <div class="carousel-inner" role="listbox">

            <div class="item active">
                <img src="{{ asset('images/slider/shymkent1.jpg') }}" alt="" class="img-responsive">
            </div>

            <div class="item">
                <img src="{{ asset('images/slider/shymkent2.jpg') }}" alt="" class="img-responsive">
            </div>

            <div class="item">
                <img src="{{ asset('images/slider/shymkent3.jpg') }}" alt="" class="img-responsive">
            </div>

        </div>

    </div>
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
    @if (count($categories) > 0)
    <section class="well2">
        <div class="container">
            <h2 class="text-center">
                Выберите категорию
            </h2>
            <div class="row text-center">
                @foreach ($categories as $key => $category)
                    <div class="col-xs-6 col-sm-4 col-md-2 category_item">
                        <a href="/category/{{ $category->slug }}">
                            <img src="{{ asset("images/icons/" . $category->icon) }}" class="wow fadeInRight" data-wow-duration='1s'>
                            <div class="category_name wow fadeInRight" data-wow-duration='2s'>
                                {{ $category->name }}
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if (count($featured) > 0)
    <section class="well2 bg1 text-center">
        <div class="container">
            <h2>
                Популярные
            </h2>

            <div class="row text-left text-xs-center wow fadeIn" data-wow-duration='3s'>
            @foreach ($featured as $branch)
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="thumbnail">
                        @if (isset($photos[$branch->id]))
                        <a class="thumb featured_photo" href="{{ asset('images/photos/' . $photos[$branch->id]) }}">
                            <img src="{{ asset('images/photos/' . $photos[$branch->id]) }}" alt="{{ $branch->name }}" width="270px" height="190px" class="">
                            <span class="thumb_overlay"></span>
                        </a>
                        @endif
                        <div class="caption">
                            <p>
                                {{ $branch->name }}<br>
                            </p>
                            <p class="price">
                                {{ $subcatNames[$branch->category_id] }}
                            </p>
                            <a href="/branch/{{ $branch->id }}/{{ $branch->category_id }}" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
                        </div>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
    </section>
    @endif

    @if (count($latest) > 0)
    <section class="well3">
        <div class="container">
            <h2 class="text-center">
                Недавно добавленные
            </h2>
            <div class="row text-left text-xs-center wow fadeIn" data-wow-duration='3s'>
                @foreach ($latest as $branch)
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="thumbnail height_160">
                        <div class="caption">
                            <p>
                                {{ $branch->name }}<br>
                            </p>
                            <p class="price">
                                {{ $subcatNames[$branch->category_id] }}
                            </p>
                            <a href="/branch/{{ $branch->id }}/{{ $branch->category_id }}" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endsection