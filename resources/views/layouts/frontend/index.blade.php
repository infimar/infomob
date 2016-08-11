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

    
@endsection