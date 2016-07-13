<footer>

    <section class="well4">
        <div class="container">
            <div class="row">

                <div class="col-md-3 col-sm-6 col-xs-6">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="img-full">
                    <h3 class="clr-white">
                        О нас
                    </h3>
                    <p class="clr-darken word-wrap">
                        «INFOMOB» - это и сайт, и удобное мобильное приложение,  облегчающее  поиск вашего запроса в вашем городе! Преимущество данного продукта в том, что он может быть всегда под рукой в вашем мобильном телефоне.
                    </p>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-6">
                    <h3 class="clr-white">
                        Последние добавленные
                    </h3>
                    
                    @foreach (App\Branch::take(7)->orderBy("id", "DESC")->get() as $branch)
                        <article>
                            <time datetime="{{ $branch->created_at->format("d M y") }}">
                                {{ $branch->created_at->format("d M y") }}
                            </time>
                            <p class="clr-primary">
                                <a href="#">
                                    {{ $branch->name }}
                                </a>
                            </p>
                        </article>
                    @endforeach
                </div>

                <div class="col-md-3 col-md-release col-sm-6 col-sm-clear col-xs-6 col-xs-clear">
                    <h3 class="clr-white">
                        Категории
                    </h3>
                    <ul class="marked-list marked-list__mod1">
                        @foreach (App\Category::roots()->get() as $category)
                        <li>
                            <a href="/category/{{ $category->slug }}"><span>{{ $category->name }} </span></a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-6">
                    <h3 class="clr-white">
                        Подпишитесь на нас
                    </h3>

                    <ul class="inline-list">
                        <li><a href="https://www.facebook.com/infomob.kazakhstan" target="_blank" class="fa fa-facebook"></a></li>
                        <li><a href="https://vk.com/infomobkaz" target="_blank" class="fa fa-vk"></a></li>
                        <li><a href="https://www.instagram.com/infomobkz" target="_blank" class="fa fa-instagram"></a></li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <section class="rights">
        <div class="container">
            <p>
                Infomob.kz &#169; <span id="copyright-year"></span>.
                <!-- {%FOOTER_LINK} -->
            </p>
        </div>
    </section>

</footer>