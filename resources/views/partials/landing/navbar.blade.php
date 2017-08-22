<li><a href="{{ url('/content') }}">Stories</a></li>

<!-- Authentication Links -->
@if (Auth::guest())
    <li><a href="{{ route('register') }}"><b>My dashboard</b></a></li>
@else
    <li><a
                class="tc-rich-electric-blue"
                href="{{ route('home') }}"><b>{{ Auth::user()->name }} dashboard</b></a></li>
    <li>
@endif
