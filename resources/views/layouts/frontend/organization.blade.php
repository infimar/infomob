@extends('layouts.frontend.template')

@section('title')
    {{ $organization->name }}
@endsection

@section('breadcrumbs')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                {!! Breadcrumbs::render('organization', $category, $subcategory, $organization) !!}
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
                                            <a href="/branch/{{ $branch->id }}/{{ $subcategory->id }}">{{ $branch->address }}</a>
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
                                            <a href="/branch/{{ $branch->id }}/{{ $subcategory->id }}">{{ $branch->address }} ({{ $branch->city->name }})</a>
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