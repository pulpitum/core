core
====

Pulpitum core package

This is the core package of the platform, here you will this the tools that makes this platform work.

How to install Pulpitum:

Install Laravel:

```composer create-project laravel/laravel pulpitum --prefer-dist```

Add the following lines before require:

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/pulpitum/core.git"
        }
    ],



In the require key of composer.json file add the following line:

    "pulpitum/core": "dev-master"

Save and execute ```composer update```


The you have to insert the following in tyo app/config.php

Provider array:

    'Purposemedia\Menu\MenuServiceProvider',
    'Teepluss\Theme\ThemeServiceProvider',
    'Former\FormerServiceProvider',
    'Cartalyst\Sentry\SentryServiceProvider',
    'Pulpitum\Core\CoreServiceProvider',

Aliases array:

    'Menu'       => 'Purposemedia\Menu\Facades\Menu',
    'Theme'      => 'Teepluss\Theme\Facades\Theme',
    'Former'     => 'Former\Facades\Former',
    'Sentry'     => 'Cartalyst\Sentry\Facades\Laravel\Sentry',


The run the following

    php artisan config:publish teepluss/theme
    php artisan config:publish anahkiasen/former
    php artisan migrate --package=cartalyst/sentry
    php artisan config:publish cartalyst/sentry


NOTE: This package is under heavy developement, ist not yep in beta release.
