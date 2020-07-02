# Clevyr Page Builder

[![Latest Stable Version](https://poser.pugx.org/clevyr/backpack-page-builder/v)](//packagist.org/packages/clevyr/backpack-page-builder)
[![Total Downloads](https://poser.pugx.org/clevyr/backpack-page-builder/downloads)](//packagist.org/packages/clevyr/backpack-page-builder)
[![Latest Unstable Version](https://poser.pugx.org/clevyr/backpack-page-builder/v/unstable)](//packagist.org/packages/clevyr/backpack-page-builder)
[![License](https://poser.pugx.org/clevyr/backpack-page-builder/license)](//packagist.org/packages/clevyr/backpack-page-builder)
# Prerequisite
Laravel Backpack must be installed

# Installation

Run `composer require clevyr/backpack-page-builder`

This will install the Page Builder and the https://github.com/Laravel-Backpack/PermissionManager package

To publish and migrate the Permission Manager and Page Builder run the following command

Run `php artisan pagebuilder:install`

run `composer dump-autoload`

run `php artisan db:seed --class=PageBuilderSeeder`

Add the following to your `sidebar_content.blade.php` file

```blade
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
```

# Pages how to - Coming soon


