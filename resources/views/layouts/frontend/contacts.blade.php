@extends('layouts.frontend.template')

@section('title')
    Наши контакты
@endsection

@section('breadcrumbs')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                {!! Breadcrumbs::render('contacts') !!}
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
            <h2>Наши контакты</h2>
            <hr>
            
            <div class="row">
                <div class="col-md-8">
                    <p style="font-size: 16px;">Мы находимся по адресу: {{ $address }}</p>

                    @foreach ($contacts as $contact)
                        <ul class="contacts">
                            <li>
                                <img src="{{ asset('images/' . $contact[0] . '.png') }}"> {{ $contact[1] }}
                            </li>
                        </ul>
                    @endforeach 
                </div>

                <div class="col-md-4">
                    
                </div>
            </div>

        </div>
    </section>
@endsection