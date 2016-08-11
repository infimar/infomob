<section class="well1">
    <div class="container">
        <div class="h1 clr-white text-center">
            Найдите то, что искали
        </div>

        {!! Form::open(array('url' => '/search', 'class' => 'search-form', 'method' => 'GET')) !!}
            <label class="search-form_label">
                {{ Form::text('query', $query, array('class' => 'search-form_input', 'placeholder' => 'Компании,  Сервисы,  Банкоматы')) }}
                <span class="search-form_liveout"></span>
            </label>
            {{ Form::submit('Поиск', array('class' => 'search-form_submit btn btn-primary')) }}
        {!! Form::close() !!}
    </div>
</section>