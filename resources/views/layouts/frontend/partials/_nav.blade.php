<div id="stuck_container" class="stuck_container">
    <div class="container">

        <div class="brand">
            <h1 class="brand_name">
                <a href="/">Infomob</a>
            </h1>
        </div>

        @if (isset($chosenCity))
        <div id="div_citypicker">
            <select id="citypicker">
                @foreach (App\City::dropdown() as $key => $value)
                    <option value="{{ $key }}"
                        @if ($chosenCity->id == $key) selected @endif 
                    >
                        {{ $value }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <nav class="nav">
            <ul class="sf-menu" data-type="navbar">
                <li class="active">
                    <a href="/">Главная</a>
                </li>
                <li>
                    <a href="#">Категории</a>
                    <ul>
                        @foreach (App\Category::roots()->published()->get() as $category)                    
                        <li>
                            <a href="/category/{{ $category->slug }}">{{ $category->name }}</a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                {{-- <li>
                    <a href="index-2.html">Our listings</a>
                </li>
                <li>
                    <a href="index-3.html">Requests</a>
                </li> --}}
                <li>
                    <a href="/contacts">Наши контакты</a>
                </li>
            </ul>
        </nav>

        <!--<div class="contact-info">
            <dl class="text-right">
                <dt>Call our helpline:</dt>
                <dd><a href="callto:#">800-1234-5678</a></dd>
            </dl>
        </div>-->

    </div>
</div>