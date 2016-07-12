@extends('layouts.frontend.template')

@section('title')
    {{ $organization->name }}
@endsection

@section('breadcrumbs')
    {!! Breadcrumbs::render('organization', $category, $subcategory, $organization) !!}
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
            <h2>{{ $organization->name }}</h2>
            <hr>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-8">
                    <div class="row">
                        <div class="col-xs-12">
                            <h4>Деятельность</h4>
                            <p>{{ nl2br($organization->description) }}</p>
                        </div>
                    </div>

                    @if (!$branches->isEmpty())
                        <div class="row">
                            <div class="col-xs-12">
                                <h4>Филиалы в городе {{ $city->name }}</h4>
                                <ul class="list-group">
                                    @foreach ($branches as $branch)
                                        <li class="list-group-item">
                                            <a href="/branch/{{ $branch->id }}/{{ $subcategory->id }}">{{ $branch->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (!$otherBranches->isEmpty())
                        <div class="row">
                            <div class="col-xs-12">
                                <h4>Другие филиалы</h4>
                                <ul class="list-group">
                                    @foreach ($otherBranches as $branch)
                                        <li class="list-group-item">
                                            <a href="/branch/{{ $branch->id }}/{{ $subcategory->id }}">{{ $branch->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection