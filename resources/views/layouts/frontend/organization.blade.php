@extends('layouts.frontend.template')

@section('title')
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
    <main>
        <section class="well2">
            <div class="container">
                <h2>Название компании</h2>
                <hr>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8">
                        <div class="row">
                            <div class="col-xs-12">
                                <h4>Description</h4>
                                <p>405 Canyon Vista Dr Spectacular mountain view, sublime privacy & endless flexibility converge right here on 1 of Mt Washington's favorite streets. Sunrise breakfast vistas, discreet gated entry & the luxury of an open loft office/den plus the 2/2 practicality let you love it just as it is or spend happy veranda hours contemplating expansive Dwell or HGTV dreams. Cars to pamper or creative space high on your list? The enclosed carport is ready and waiting. Longing to build some raised garden beds? The generous Meyer lemon will dress your fresh veggies in style. Contemplating happy gatherings? The indoor ope...n flow welcomes cozy dinners or that long summer feast under the stars. Lovingly cared for, wonderfully sited for all that brings you to Mt Washington, handy to NELA & beyond.</p>
                            </div>
                        </div>
                        <br>
                        <div data-lightbox="gallery" class="product-carousel">
                            <!-- Slick Carousel-->
                            <div class="carousel-slider slider">
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-1.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-2.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-3.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-4.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-5.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-6.jpg') }}" alt="" width="770" height="520"></div>
                            </div>
                            <div class="carousel-thumbnail slider">
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-1.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-2.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-3.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-4.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-5.jpg') }}" alt="" width="770" height="520"></div>
                                <div class="item"><img src="{{ asset ('images/gallery/catalog_single-6.jpg') }}" alt="" width="770" height="520"></div>
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
                                            <td>ул.Тауке хана 200, офис 300</td>
                                        </tr>
                                        <tr>
                                            <td>Телефон</td>
                                            <td>+7 7252 555666</td>
                                        </tr>
                                        <tr>
                                            <td>Факс</td>
                                            <td>+7 7252 555666</td>
                                        </tr>
                                        <tr>
                                            <td>Email</td>
                                            <td>example@mail.com</td>
                                        </tr>
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
    </main>
@endsection