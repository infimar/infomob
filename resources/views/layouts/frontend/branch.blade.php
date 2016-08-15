@extends('layouts.frontend.template')

@section('title')
    {{ $branch->name }}
@endsection

@section('breadcrumbs')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                {!! Breadcrumbs::render('branch_category', $parentCategory, $category, $branch->organization, $branch) !!}
            </div>
        </div>
    </div>
@endsection

@section('search')
    @include('layouts.frontend.partials._search')
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
                            {{-- <h4>Деятельность</h4> --}}
                            <p>{!! nl2br($branch->description) !!}</p>
                        </div>
                    </div>
                    <br>
                    <div data-lightbox="gallery" class="product-carousel">
                        <!-- Slick Carousel-->
                        <div class="carousel-slider slider">
                            @foreach ($branch->photos as $photo)
                                <div class="item">
                                    <img src="{{ asset ('images/photos/' . $photo->path) }}" alt="" class="img-responsive">
                                </div>
                            @endforeach
                        </div>
                        <div class="carousel-thumbnail slider">
                            @foreach ($branch->photos as $photo)
                                <div class="item">
                                    <img src="{{ asset ('images/photos/' . $photo->path) }}" alt="">
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

                                    @if (!empty($branch->working_hours))
                                    <tr>
                                        <td>Часы работы</td>
                                        <td>{!! nl2br($branch->working_hours) !!}</td>
                                    </tr>
                                    @endif

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
                            @if ($branch->lat != "0.00" && $branch->lng != "0.00" || $branch->lat != "0.0" && $branch->lng != "0.0")
                            <div class="rd-google-map rd-google-map-mod-1" id="map">
                                <script>

                                    var map;
                                    function initMap() {
                                        //Координаты точки
                                        var position = {lat: lat, lng: lng};

                                        map = new google.maps.Map(document.getElementById('map'), {
                                            center: position,
                                            zoom: 14
                                        });

                                        var marker = new google.maps.Marker({
                                            map: map,
                                            position: position,
                                            icon: "" //Смена иконки маркера, указать путь к иконке
                                        });
                                    }

                                </script>
                                <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAWHECcodIoJcQKStYSpRkfwrG9R7xRgYQ&callback=initMap" async defer></script>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection