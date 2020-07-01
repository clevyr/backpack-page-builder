# Clevyr Page Builder

# Prerequisite
Laravel Backpack must be installed

# Installation

Run `composer require clevyr/backpack-page-builder`

This will install the Page Builder and the https://github.com/Laravel-Backpack/PermissionManager package

Follow the installation instructions for the Permission Manager package

please see https://github.com/Laravel-Backpack/PermissionManager

run `php artisan vendor:publish --provider="Clevyr\PageBuilder\PageBuilderServiceProvider"`

to publish the Page Builder files

run `php artisan migrate --seed`

This will migrate the Page Builder migrations and run the role / permissions seeder

A super admin user will also be created, you can login with the following credentials

```
email: super-admin@example.com
password: password
```


