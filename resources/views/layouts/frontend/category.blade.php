@extends('layouts.frontend.template')

@section('title')
    {{ $category->name }}
@endsection

@section('breadcrumbs')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                {!! Breadcrumbs::render('category', $category, $activeSubcategory) !!}
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
                    @foreach ($subcategories as $subcategory)
                        <div class="col-xs-6 col-sm-4">
                            <a href="/category/{{ $category->slug }}?subcategory={{ $subcategory->category_slug }}"
                                @if (!is_null($activeSubcategory) && $activeSubcategory->category_slug == $subcategory->category_slug)
                                    style="color: red"
                                @endif
                            >{{ $subcategory->category_name }}</a> ({{ $subcategory->orgs_count }})
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>

            @if (count($organizations) > 0)
            <div class="row">
                @foreach ($organizations as $organization)
                    <div class="organization_item col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <a href="/organization/{{ $organization->org_id}}/{{ $activeSubcategory->category_id }}">
                            <div class="thumbnail_100">
                                @if (!empty($organization->org_photo))
                                    <img src="{{ asset('images/photos/' . $organization->org_photo) }}">
                                @else
                                    <img src="{{ asset('images/photos/nologo.png') }}">
                                @endif
                            </div>
                        </a>

                        <div class="organization_item_text">
                            <div class="organization_name_title">
                                <a href="/organization/{{ $organization->org_id }}/{{ $activeSubcategory->category_id }}">{{ $organization->org_name }}</a>
                            </div>
                            <div class="organization_short_description">
                                <div>{{ str_limit($organization->org_description, 200) }}</div>

                                {{-- phones --}}
                                @if (!empty($organization->org_phones))
                                    <div>Контакты: 
                                    <?php $phones = explode(';', $organization->org_phones); ?>
                                    @foreach ($phones as $key => $phone)
                                    <?php if ($key > 1) break; ?>
                                        {{ $phone }}
                                    @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <div style="clear:both"></div> 
                {{ $organizations->appends(['subcategory' => $activeSubcategory->category_slug])->links() }}
            </div>
            @else
                <div class="row">Организаций нет.</div>
            @endif
        </div>
    </section>
@endsection