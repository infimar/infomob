@extends('layouts.frontend.template')

@section('title')
@endsection

@section('search')
    <section class="well_search bg1">
        <div class="container">

            <div class="h1 clr-black text-center">
                404<br>
                Страница которую вы ищете, не найдена<br>
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

@endsection