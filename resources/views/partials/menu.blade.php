<div class="sidebar">
    <nav class="sidebar-nav ps ps--active-y">

        <ul class="nav">
            <li class="nav-item">
                <a href="{{ route('admin.home') }}" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt">

                    </i>
                    {{ trans('global.dashboard') }}
                </a>
            </li>
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="fas fa-clipboard-list  nav-icon">

                    </i>
                    {{ trans('global.ticket.title') }}
                </a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a href="{{ route('admin.tickets.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-clipboard-list"></i>
                            {{ trans('global.ticket.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.customerrequests.index') }}" class="nav-link {{ request()->is('admin/customerrequests') || request()->is('admin/customerrequests/*') ? 'active' : '' }}">
                            <i class="fas fa-user nav-icon">

                            </i>
                            {{ trans('global.customerrequest.title') }}
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="fas fa-database  nav-icon">

                    </i>
                    {{ trans('global.master.title') }}
                </a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->is('admin/customers') || request()->is('admin/customers/*') ? 'active' : '' }}">
                            <i class="fas fa-user nav-icon">

                            </i>
                            {{ trans('global.customer.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.categories.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt">

                            </i>
                            {{ trans('global.category.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.dapertements.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon far fa-building"></i>
                            {{ trans('global.dapertement.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.subdapertements.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon far fa-building"></i>
                            {{ trans('global.subdapertement.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.staffs.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon fas fa-people-carry"></i>
                            {{ trans('global.staff.title') }}
                        </a>
                    </li>                    
                </ul>
            </li>

            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="fas fa-users nav-icon">

                    </i>
                    {{ trans('global.userManagement.title') }}
                </a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a href="{{ route("admin.permissions.index") }}" class="nav-link {{ request()->is('admin/permissions') || request()->is('admin/permissions/*') ? 'active' : '' }}">
                            <i class="fas fa-unlock-alt nav-icon">

                            </i>
                            {{ trans('global.permission.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route("admin.roles.index") }}" class="nav-link {{ request()->is('admin/roles') || request()->is('admin/roles/*') ? 'active' : '' }}">
                            <i class="fas fa-briefcase nav-icon">

                            </i>
                            {{ trans('global.role.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route("admin.users.index") }}" class="nav-link {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}">
                            <i class="fas fa-user nav-icon">

                            </i>
                            {{ trans('global.user.title') }}
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                    <i class="nav-icon fas fa-sign-out-alt">

                    </i>
                    {{ trans('global.logout') }}
                </a>
            </li>
        </ul>

        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
        </div>
        <div class="ps__rail-y" style="top: 0px; height: 869px; right: 0px;">
            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 415px;"></div>
        </div>
    </nav>
    <button class="sidebar-minimizer brand-minimizer" type="button"></button>
</div>
