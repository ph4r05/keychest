<li><a href="{{ url('/roca') }}">ROCA test</a></li>

<li><a href="{{ url('/content') }}">Stories</a></li>

<!-- Authentication Links -->
@if (Auth::guest())
    <li><a href="{{ route('login') }}"><b>My dashboard</b></a></li>
@else
    <li><a
                class="tc-rich-electric-blue"
                href="{{ route('home') }}"><b>{{ Auth::user()->name }} dashboard</b></a></li>
    <li>
@endif
