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

            <li class="treeview menu-open {{ Request::path() == 'home' || strstr(Request::path(), 'home/dashboard') !== false ? 'active' : ''  }}">
                <a href="{{ url('home') }}">
                    <i class='fa fa-dashboard'></i> <span>{{ trans('admin.dashboard') }}</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>

                <ul class="treeview-menu">
                    <li class="{{ Request::path() ==  'home' ? 'active' : ''  }}">
                        <a href="{{ url('home') }}">
                            <i class="fa fa-circle-o"></i> Full
                        </a>
                    </li>

                    <li class="{{ Request::path() ==  'home/dashboard/management' ? 'active' : ''  }}">
                        <a href="{{ url('home/dashboard/management') }}">
                            <i class="fa fa-circle-o"></i> Management
                        </a>
                    </li>

                    <li class="{{ Request::path() ==  'home/dashboard/finance' ? 'active' : ''  }}">
                        <a href="{{ url('home/dashboard/finance') }}">
                            <i class="fa fa-circle-o"></i> Financial
                        </a>
                    </li>

                    <li class="{{ Request::path() ==  'home/dashboard/ops' ? 'active' : ''  }}">
                        <a href="{{ url('home/dashboard/ops') }}">
                            <i class="fa fa-circle-o"></i> Operations
                        </a>
                    </li>

                    <li class="{{ Request::path() ==  'home/dashboard/sec' ? 'active' : ''  }}">
                        <a href="{{ url('home/dashboard/sec') }}">
                            <i class="fa fa-circle-o"></i> Security
                        </a>
                    </li>

                </ul>
            </li>

            <li class="{{ Request::path() ==  'home/scan' ? 'active' : ''  }}">
                <a href="{{ url('home/scan') }}"><i class='fa fa-wpexplorer'></i> <span>{{ trans('admin.scan') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  'home/servers' ? 'active' : ''  }}">
                <a href="{{ url('home/servers') }}"><i class='fa fa-server'></i> <span>{{ trans('admin.servers') }}</span></a>
            </li>

            @if(config('keychest.enabled_ip_scanning'))
            <li class="{{ Request::path() ==  'home/networks' ? 'active' : ''  }}">
                <a href="{{ url('home/networks') }}"><i class='fa fa-cogs'></i> <span>{{ trans('admin.ip_servers') }}</span></a>
            </li>
            @endif

            <li class="{{ Request::path() ==  'home/management' ? 'active' : ''  }}">
                <a href="{{ url('home/management') }}"><i class='fa fa-cubes'></i> <span>{{ trans('admin.management') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  'home/license' ? 'active' : ''  }}">
                <a href="{{ url('home/license') }}"><i class='fa fa-drivers-license'></i> <span>{{ trans('admin.license') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  'home/cost-management' ? 'active' : ''  }}">
                <a href="{{ url('home/cost-management') }}"><i class='fa fa-money'></i> <span>{{ trans('admin.cost-management') }}</span></a>
            </li>
        </ul>

        <hr/>

        <ul class="sidebar-menu">
            <li class="{{ Request::path() ==  'home/apidoc' ? 'active' : ''  }}">
                <a href="{{ url('home/apidoc') }}"><i class='fa fa-podcast'></i> <span>{{ trans('admin.apidoc') }}</span></a>
            </li>

            <li class="{{ Request::path() ==  'home/user-guide' ? 'active' : ''  }}">
                <a href="{{ url('home/user-guide') }}"><i class='fa fa-info'></i> <span>{{ trans('admin.userguide') }}</span></a>
            </li>

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
