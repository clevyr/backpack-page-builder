# Backpack Page Builder

[![Latest Stable Version](https://poser.pugx.org/clevyr/backpack-page-builder/v)](//packagist.org/packages/clevyr/backpack-page-builder)
[![Total Downloads](https://poser.pugx.org/clevyr/backpack-page-builder/downloads)](//packagist.org/packages/clevyr/backpack-page-builder)
[![Latest Unstable Version](https://poser.pugx.org/clevyr/backpack-page-builder/v/unstable)](//packagist.org/packages/clevyr/backpack-page-builder)
[![License](https://poser.pugx.org/clevyr/backpack-page-builder/license)](//packagist.org/packages/clevyr/backpack-page-builder)

# Prerequisite
Laravel Backpack must be installed

1. Laravel 7
2. PHP 7.4
3. GD or Imagick for Image Intervention

# Table of Contents
* [Installation](#installation)
* [Create a super admin](#create-a-super-admin)
* [Page Development](#page-development)
    * [Sync Pages](#syncing-pages)
    * [Edit Static Pages](#editing-static-pages)
    * [Edit Dynamic pages](#editing-dynamic-pages)
    * [Creating pages](#creating-pages)
        * [Generating Pages](#generating-pages)
* [Development](#development)
    * [Local Package Development](#setting-up-local-package-development)

# Installation

Run `composer require clevyr/backpack-page-builder`

This will install the Page Builder and the https://github.com/Laravel-Backpack/PermissionManager package

This will install Image Intervention http://image.intervention.io/getting_started/introduction

To install the Page builder run the following command,
this will install the Permissions Manager and the Page Builder

Run `php artisan pagebuilder:install`

---

Update the `config -> backpack -> base.php` file
```php
'guard' => 'backback',
```

to

```php
'guard' => config('auth.defaults.guard'),
```

---

Run `composer dump-autoload`

Seed the permissions

Run `php artisan db:seed --class=PageBuilderSeeder`

---

Update `resources -> views -> vendor -> backpack -> base -> inc -> sidebar_content.blade.php` file with

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

---

Update `app -> User.php` with

```php
...
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Spatie\Permission\Traits\HasRoles;
...

class User extends Model {
    ...
    use CrudTrait;
    use HasRoles;
    ...
}
```

---

# Create a super admin
Run `php artisan pagebuilder:user`

Run with parameters
```
php artisan pagebuilder:user --name=Name --email=email@example.com --password=password
```

# Page Development

### Syncing pages

Navigate to `pages -> manage`

Click the sync icon in the bottom right, this will load in static pages (Every folder that is not the **dynamic** folder) and it will reload the page

You also have the option to sync them from the command line with the following command

`php artisan pagebuilder:sync`

### Editing static pages

Navigate to the page management page and click edit on the homepage

#### Page Settings
**Name:** Page name, admin functionality only, does not affect the functionality of the page's at all

**Title:** Page title, also generates the slug as you type

**Slug:** Slug of the page

**View:** Page view

#### Page Content

The page content populates with a list of sections, and their fields which you can edit.

---

### Editing Dynamic pages

See [Editing Static Pages](#editing-static-pages) for a run down of **Page Settings** and **Page Content**

- Page Layout

_Note - You can only use the Page Layout tab if you are working with a dynamic page_

#### Sections list

A list of dynamic sections will be displayed, you can click them to add them to the Content Section

#### Content Section

The content section displays the sections that will be available to edit on the page

You will not be able to edit the content until you save the page

---

### Creating pages

Pages are located at `resources -> views -> pages` each folder is considered as the page, with the contents inside
the folder dictating what view / sections are available

Contents
- Sections - Holds each individual section for that view
- config.php - Holds the configuration for the page sections
- index.blade.php - Is the view of the page

_Note - Pages sections will not sync if there is not a `.blade` file inside the sections directory and
a config property inside the config.php_

---

#### Generating Pages

To generate a new page run the following command
`php artisan pagebuilder:page page` with page being the name of the new page

This will create a folder with the page name with the following structure

* page
    * index.blade.php
    * config.php
    * sections
        * default.blade.php

---

#### Sections

The `sections` folder holds the `.blade` files that correspond to the section key in the `config.php`

**Using data inside a section**

You have access to the `$sections` variable which is an anonymous function that returns
the field data from the `section name` and the `field title`

```blade
<h1>
    {{-- default: section name, title: field name --}}
    {{ $sections('default', 'title') }}

    <br />

    <small>
        {{ $sections('default', 'sub-title') }}
    </small>
</h1>

{!! $sections('default', 'content') !!}
```

#### config.php

The `config.php` holds the configuration of the page sections.
Each section holds a list of backpack crud fields https://backpackforlaravel.com/docs/4.1/crud-fields
any field, including custom fields will be available to be used inside the config.

**Example config**

```php
<?php

return [
    'default' => [ // Section name, the blade file for the section's name must be the same as this key
        'is_dynamic' => true, // Defaults to false, if this is set it will be available to be used in dynamic pages
        'title' => [ // Field name
            'type' => 'text', // crud field type
            'name' => 'title', // crud field name
            'label' => 'Title' // crud field label
            // ... Any other crud field properties
        ],
        'sub-title' => [
            'type' => 'text',
            'name' => 'sub-title',
            'label' => 'Sub Title'
        ],
        'content' => [
            'type' => 'wysiwyg',
            'label' => 'Content',
            'name' => 'content',
        ],
    ],
];
```

#### index.blade.php

The `index.blade.php` is the view file for the page and it's sections.

**Example index.blade.php**

```blade
<div>
    @include('pages.homepage.sections.default')
</div>
```

# Development

#### Submitting Changes

Please push your changes to a new branch before submitting a PR

---

#### Setting up local package development 

1. Within the root of an existing Laravel Backpack project, clone this project to `./packages/clevyr/backpack-page-builder`
2. Add the following to your composer.json

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/clevyr/backpack-page-builder",
        "options": {
            "symlink": true
        }
    }
],
```

3. Add `clevyr/backpack-page-builder` to your list of requires in your `composer.json` like below:

```json
"require": {
...
"clevyr/backpack-page-builder": "dev-master",
...
}
```

4. Run `composer require clevyr/backpack-page-builder` 

   
    



