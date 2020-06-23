<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}">
        <i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}
    </a>
</li>

@canany(['View User List', 'View Role List', 'View Permission List'])
    <li class="nav-item nav-dropdown">
        <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Authentication</a>

        <ul class="nav-dropdown-items">
            @can('View User List')
                <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
            @endcan

            @can('View Role List')
            <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
            @endcan

            @can('View Permission List')
                <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
            @endcan
        </ul>
    </li>
@endcanany

@can('View Page List')
    <li class="nav-item nav-dropdown">
        <a class="nav-link nav-dropdown-toggle" href="#">
            <i class="nav-icon la la-folder"></i>
            Pages
        </a>

        <ul class="nav-dropdown-items">
            <li class="nav-item">
                <a class="nav-link" href="{{ backpack_url('pages') }}">
                    <i class="nav-icon la la-address-book"></i>

                    <span>
                        Manage
                    </span>
                </a>
            </li>
        </ul>
    </li>
@endcan
