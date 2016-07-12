@extends('layouts.frontend.template')

@section('title')
    {{ $branch->name }}
@endsection

@section('breadcrumbs')
    {!! Breadcrumbs::render('branch', $parentCategory, $category, $branch->organization, $branch) !!}
@endsection

@section('search')
    <section class="well_search bg1">
        <div class="container">
            <div class="h1 clr-black text-center">
                Найдите то, что искали
            </div>

            {!! Form::open(array('url' => '', 'class' => 'search-form-all')) !!}
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
    <section class="well2">
        <div class="container">
            <h2>{{ $branch->name }}</h2>
            <hr>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-8">
                    <div class="row">
                        <div class="col-xs-12">
                            <h4>Деятельность</h4>
                            <p>{{ nl2br($branch->description) }}</p>
                        </div>
                    </div>
                    <br>
                    <div data-lightbox="gallery" class="product-carousel">
                        <!-- Slick Carousel-->
                        <div class="carousel-slider slider">
                            @foreach ($branch->photos as $photo)
                                <div class="item">
                                    <img src="{{ asset ('images/photos/' . $photo->path) }}" alt="" width="770" height="520">
                                </div>
                            @endforeach
                        </div>
                        <div class="carousel-thumbnail slider">
                            @foreach ($branch->photos as $photo)
                                <div class="item">
                                    <img src="{{ asset ('images/photos/' . $photo->path) }}" alt="" width="770" height="520">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-12 col-md-4">
                    <div class="catalog-sidebar range">
                        <div class="sidebar-module cell-sm-6 cell-md-12 cell-md-push-5">
                            <div class="table-responsive">
                                <table class="table">
                                    <colgroup>
                                        <col class="col-xs-2">
                                        <col class="col-xs-2">
                                    </colgroup>
                                    <thead>
                                    <tr class="bg-gray">
                                        <th colspan="2">Контакты</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Адрес</td>
                                        <td>{{ $branch->address }}</td>
                                    </tr>

                                    @if (!$branch->phones->isEmpty())
                                        @foreach ($branch->phones as $phone)
                                            <tr>
                                                <td>{{ $types[$phone->type] }}</td>
                                                <td>
                                                    {{ $phone->code_country }} ({{ $phone->code_operator }}) {{ $phone->number }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

                                    @if (!empty($branch->email))
                                        <tr>
                                            <td>Email</td>
                                            <td>{{ $branch->email }}</td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>

                            <!-- RD Google Map-->
                            <div class="rd-google-map rd-google-map-mod-1">

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection