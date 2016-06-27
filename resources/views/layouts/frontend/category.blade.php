@extends('layouts.frontend.template')

@section('title')
    Категории
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
                <div class="left_block">
                    <h3>Все категории</h3>
                    <div>
                        <ul class="sf-menu1">
                            <li class="active1">
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
                                        <a href="#" class="sub-menu1">Ctetur adipisicing elit</a>
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
                    </div>
                </div>
                <div class="right_block">
                    <div class="organization_item">
                        <a href="#"><img src="{{ asset('images/organization_logo/1.png') }}"></a>
                        <div class="organization_item_text">
                            <div class="organization_name_title">
                                <a href="#">Название компании</a>
                            </div>
                            <div class="organization_short_description">
                                Краткое описание компании, описание предприятия
                            </div>
                        </div>
                    </div>
                    <div class="organization_item">
                        <a href="#"><img src="{{ asset('images/organization_logo/1.png') }}"></a>
                        <div class="organization_item_text">
                            <div class="organization_name_title">
                                <a href="#">Название компании</a>
                            </div>
                            <div class="organization_short_description">
                                Краткое описание компании, описание предприятия
                            </div>
                        </div>
                    </div>
                    <div class="organization_item">
                        <a href="#"><img src="{{ asset('images/organization_logo/1.png') }}"></a>
                        <div class="organization_item_text">
                            <div class="organization_name_title">
                                <a href="#">Название компании</a>
                            </div>
                            <div class="organization_short_description">
                                Краткое описание компании, описание предприятия
                            </div>
                        </div>
                    </div>
                    <div class="organization_item">
                        <a href="#"><img src="{{ asset('images/organization_logo/1.png') }}"></a>
                        <div class="organization_item_text">
                            <div class="organization_name_title">
                                <a href="#">Название компании</a>
                            </div>
                            <div class="organization_short_description">
                                Краткое описание компании, описание предприятия
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection