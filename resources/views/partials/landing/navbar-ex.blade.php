<li><a href="{{ url('/content') }}">Stories</a></li>

<!-- Authentication Links -->
@if (Auth::guest())
    <li><a href="{{ route('login') }}"><b>My dashboard</b></a></li>
@else
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
            <b>{{ Auth::user()->name }}</b> <span class="caret"></span>
        </a>

        <ul class="dropdown-menu tc-rich-electric-blue" role="menu">
            <li><a
                        class="tc-rich-electric-blue"
                        href="{{ route('home') }}">My dashboard</a></li>
            <li>
                <a
                        class="tc-rich-electric-blue"
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </li>
        </ul>
    </li>
@endif
