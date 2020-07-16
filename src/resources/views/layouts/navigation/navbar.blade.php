<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                @foreach($menu as $page)
                    <li class="nav-item {{ $page->has_sub_pages ? 'dropdown' : '' }}">
                        <a class="nav-link {{ $page->has_sub_pages ? 'dropdown-toggle' : '' }}"
                           href="{{ url($page->slug) }}"
                           title="{{ $page->url }} Page"
                           id="{{ $page->has_sub_pages ? $page->title : '' }}"
                           data-toggle="{{ $page->has_sub_pages ? 'dropdown' : '' }}"
                           aria-haspopup="{{ $page->has_sub_pages ? 'true' : 'false' }}"
                           aria-expanded="{{ $page->has_sub_pages ? 'false' : '' }}">
                            {{ $page->title }}
                        </a>

                        @if ($page->has_sub_pages)
                            <div class="dropdown-menu" aria-labelledby="{{ $page->title }}">
                                @foreach ($page->subpages as $subpage)
                                    <a class="dropdown-item"
                                       href="{{ $subpage->url }}"
                                       title="{{ $subpage->title }} Page">
                                        {{ $subpage->title }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="#" role="button">
                            {{ Auth::user()->name }}
                        </a>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
