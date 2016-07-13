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
            <div class="row">
                <div class="subcategories_links col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    <div class="row">
                    @foreach ($children as $child)
                        <div class="col-xs-6 col-sm-4">
                            <a href="/category/{{ $category->slug }}?subcategory={{ $child->slug }}"
                                @if ($activeSubcategory->slug == $child->slug)
                                    style="color: #2196F3;"
                                @endif
                            >{{ $child->name }}</a> ({{ DB::table('branch_category')->where('category_id', $child->id)->count() }})
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
            <div class="row">
                @if (count($organizations) > 0)
                    @foreach ($organizations as $organization)
                        <div class="organization_item col-lg-6 col-md-6 col-sm-6 col-xs-6">
                            <a href="/organization/{{ $organization->id}}/{{ $activeSubcategory->id }}">
                                @if (!$organization->branches->isEmpty() && !$organization->branches[0]->photos->isEmpty())
                                    <div class="thumbnail_100">
                                        <img src="{{ asset('images/photos/' . $organization->branches[0]->photos[0]->path) }}">
                                    </div>
                                @else
                                    <div class="thumbnail_100">
                                        <img src="{{ asset('images/photos/nologo.png') }}">
                                    </div>
                                @endif
                            </a>

                            <div class="organization_item_text">
                                <div class="organization_name_title">
                                    <a href="/organization/{{ $organization->id }}/{{ $activeSubcategory->id }}">{{ $organization->name }}</a>
                                </div>
                                <div class="organization_short_description">
                                    <div>{{ str_limit($organization->description, 100) }}</div>

                                    @if (!$organization->branches->isEmpty() && !$organization->branches[0]->phones->isEmpty())
                                        <div>Контакты: |
                                        @foreach ($organization->branches[0]->phones as $phone)
                                            {{ $phone->code_country }} ({{ $phone->code_operator }}) {{ $phone->number }} @if (!empty($phone->contact_person)) - {{ $phone->contact_person }} @endif |
                                        @endforeach
                                            </div>
                                    @endif
                                </div>
                            </div>
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