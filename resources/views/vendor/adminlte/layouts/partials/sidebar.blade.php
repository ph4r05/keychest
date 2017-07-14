<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        @if (! Auth::guest())
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="{{ Gravatar::get($user->email) }}" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>{{ Auth::user()->name }}</p>
                    <!-- Status -->
                    <a href="#"><i class="fa fa-circle text-success"></i> {{ trans('adminlte_lang::message.online') }}</a>
                </div>
            </div>
        @endif

        {{--<!-- search form (Optional) -->--}}
        {{--<form action="#" method="get" class="sidebar-form">--}}
            {{--<div class="input-group">--}}
                {{--<input type="text" name="q" class="form-control" placeholder="{{ trans('adminlte_lang::message.search') }}..."/>--}}
              {{--<span class="input-group-btn">--}}
                {{--<button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>--}}
              {{--</span>--}}
            {{--</div>--}}
        {{--</form>--}}
        {{--<!-- /.search form -->--}}

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="header">{{ trans('adminlte_lang::message.header') }}</li>

            <li class="{{ Request::path() ==  'home' ? 'active' : ''  }}">
                <a href="{{ url('home') }}"><i class='fa fa-dashboard'></i> <span>{{ trans('admin.dashboard') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  'home/scan' ? 'active' : ''  }}">
                <a href="{{ url('home/scan') }}"><i class='fa fa-wpexplorer'></i> <span>{{ trans('admin.scan') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  'home/servers' ? 'active' : ''  }}">
                <a href="{{ url('home/servers') }}"><i class='fa fa-server'></i> <span>{{ trans('admin.servers') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  'home/user-guide' ? 'active' : ''  }}">
                <a href="{{ url('home/user-guide') }}"><i class='fa fa-info'></i> <span>{{ trans('admin.userguide') }}</span></a>
            </li>
        </ul>

        <hr/>
        <ul class="sidebar-menu">
            <li class="{{ Request::path() ==  'home/users' ? 'active' : ''  }}">
                <a href="{{ url('home/users') }}"><i class='fa fa-users'></i> <span>{{ trans('admin.users') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  'home/agents' ? 'active' : ''  }}">
                <a href="{{ url('home/agents') }}"><i class='fa fa-anchor'></i> <span>{{ trans('admin.agents') }}</span></a>
            </li>

        </ul>

        <hr/>

        <ul class="sidebar-menu">
            <li class="{{ Request::path() ==  'home/enterprise' ? 'active' : ''  }}">
                <a href="{{ url('home/enterprise') }}"><i class='fa fa-money'></i> <span>{{ trans('admin.enterprise') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  '/content' ? 'active' : ''  }}">
                <a href="{{ url('/content') }}"><i class='fa fa-book'></i> <span>{{ trans('admin.content') }}</span></a>
            </li>



            {{--<li class="{{ Request::path() ==  '/intro' ? 'active' : ''  }}">--}}
                {{--<a href="{{ url('/intro') }}"><i class='fa fa-home'></i> <span>{{ trans('admin.home-page') }}</span></a>--}}
            {{--</li>--}}

            {{--<li class="treeview">--}}
                {{--<a href="#"><i class='fa fa-link'></i> <span>{{ trans('adminlte_lang::message.multilevel') }}</span> <i class="fa fa-angle-left pull-right"></i></a>--}}
                {{--<ul class="treeview-menu">--}}
                    {{--<li><a href="#">{{ trans('adminlte_lang::message.linklevel2') }}</a></li>--}}
                    {{--<li><a href="#">{{ trans('adminlte_lang::message.linklevel2') }}</a></li>--}}
                {{--</ul>--}}
            {{--</li>--}}

        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
