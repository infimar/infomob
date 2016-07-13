@extends('layouts.frontend.template')

@section('title')
    {{ $category->name }}
@endsection

@section('breadcrumbs')
    {!! Breadcrumbs::render('category', $category, $activeSubcategory) !!}
@endsection

@section('search')
    <section class="well_search bg1">
        <div class="container">
            <div class="h1 clr-black text-center">
                Найдите то, что искали
            </div>

            {!! Form::open(array('url' => '', 'class' => 'search-form-all')) !!}
            <label class="search-form_label">
                {{ Form::text('s', $first_name = null, array('class' => 'search-form_input', 'placeholder' => 'Компании,  Сервисы,  Банкоматы')) }}
                <span class="search-form_liveout"></span>
            </label>
            {{ Form::submit('Поиск', array('class' => 'search-form_submit btn btn-primary')) }}
            {!! Form::close() !!}
        </div>
    </section>
@endsection

@section('content')
    <section class="well2">
        <div class="container">
            <div class="left_block">
                <h3>{{ $category->name }}</h3>
                <div>
                    <ul class="sf-menu1">
                        @foreach ($children as $child)
                            <li class="active">
                                <a href="/category/{{ $category->slug }}?subcategory={{ $child->slug }}"
                                    @if ($activeSubcategory->slug == $child->slug)
                                        style="color: #2196F3;"
                                    @endif
                                >
                                    {{ $child->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="right_block">
                @if (count($organizations) > 0)
                    @foreach ($organizations as $organization)
                        <div class="organization_item">
                            <a href="/organization/{{ $organization->id }}">
                                @if (!$organization->branches->isEmpty() && !$organization->branches[0]->photos->isEmpty())
                                    <div class="thumbnail_100">
                                        <img src="{{ asset('images/photos/' . $organization->branches[0]->photos[0]->path) }}">
                                    </div>
                                @else
                                    <img src="{{ asset('images/photos/nologo.png') }}">
                                @endif
                            </a>

                            <div class="organization_item_text">
                                <div class="organization_name_title">
                                    <a href="/organization/{{ $organization->id }}/{{ $activeSubcategory->id }}">{{ $organization->name }}</a>
                                </div>
                                <div class="organization_short_description">
                                    {{ $organization->description }} <br>

                                    @if (!$organization->branches->isEmpty() && !$organization->branches[0]->phones->isEmpty())
                                        Контакты:<br>
                                        @foreach ($organization->branches[0]->phones as $phone)
                                            {{ $phone->code_country }} ({{ $phone->code_operator }}) {{ $phone->number }} @if (!empty($phone->contact_person)) - {{ $phone->contact_person }} @endif<br>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div style="clear: both;"></div>
                        </div>
                    @endforeach

                    {{ $organizations->appends(['subcategory' => $activeSubcategory->slug])->links() }}
                @else
                    <span style="margin-left: 20px">Нет совпадений</span>
                @endif
            </div>
        </div>
    </section>
@endsection