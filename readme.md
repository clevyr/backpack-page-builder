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

Login using the super admin credentials, navigate to **pages -> manage** and click the sync button in the bottom
right of the page, once it is done, reload the page.

# Pages how to - Coming soon


