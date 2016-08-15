@extends('layouts.frontend.template')

@section('title')
    Поиск
@endsection

@section('breadcrumbs')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                {!! Breadcrumbs::render('search', $query) !!}
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
            <div class="row">
                <div class="subcategories_links col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                    
                    </div>
                </div>
            </div>

            @if ($noQuery)
                <div class="row">Введите запрос</div>
            @elseif (count($result) > 0 || count($categories) > 0)
                <div class="row">
                    {{-- Филиалы --}}
                    <div class="col-md-8">
                        <h3>Организации и филиалы ({{ count($branches) }})</h3>
                        @if (count($result) > 0)
                        <ul>
                            @foreach ($result as $branch)
                            <li>
                                <a href="/branch/{{ $branch['id'] }}">
                                    {{ $branch['name'] }}
                                </a>
                            </li>
                            @endforeach
                            
                            <ul class="links">
                            @for ($i = 1; $i <= $pagesCount; $i++)
                                <li @if ($i == $pageNum) class="active" @endif>
                                    <a href="?query={{ $query }}&page={{ $i }}">{{ $i }}</a>
                                </li>
                            @endfor
                            </ul>
                        </ul>
                        @else
                            Ничего не найдено
                        @endif
                    </div>
                    
                    {{-- Категории --}}
                    <div class="col-md-4">
                        <h3>Категории ({{ count($categories) }})</h3>
                        @if (count($categories) > 0)
                        <ul>
                            @foreach ($categories as $category)
                            <li>
                                <a href="/category/{{ $category->slug }}">
                                {!! preg_replace("/($query)/i", sprintf('<span style="color: %s; font-weight:bold;">$1</span>', '#2196F3'), mb_strtolower($category->name)); !!}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @else
                            Ничего не найдено
                        @endif
                    </div>
                </div>            
            @else
                <div class="row">Не найдено</div>
            @endif
        </div>
    </section>

@endsection