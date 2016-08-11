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
    class="parallax" data-url="{{ asset('images/parallax1.jpg') }}" data-mobile="true" data-speed="0.8"
@endsection


@section('search')
    @include('layouts.frontend.partials._search_index')
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
                        <a href="/category/{{ $category->category_slug }}">
                            <img src="{{ asset("images/icons/" . $category->category_icon) }}" class="wow fadeInRight" data-wow-duration='1s'>
                            <div class="category_name wow fadeInRight" data-wow-duration='2s'>
                                {{ $category->category_name }}
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
                        @if (!empty($branch->photo))
                        <a class="thumb featured_photo" href="{{ asset('images/photos/' . $branch->photo) }}">
                            <img src="{{ asset('images/photos/' . $branch->photo) }}" alt="{{ $branch->name }}" width="270px" height="190px" class="">
                            <span class="thumb_overlay"></span>
                        </a>
                        @endif
                        <div class="caption">
                            <p>
                                {{ $branch->name }}<br>
                            </p>
                            <p class="price">
                                {{ $branch->categories }}
                            </p>
                            <a href="/branch/{{ $branch->branch_id }}/" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
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
                                
                            </p>
                            <a href="/branch/{{ $branch->id }}" class="btn btn-primary">Подробнее <span class='fa fa-angle-double-right'></span></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

@endsection