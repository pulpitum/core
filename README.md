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
            "name": "pulpitum/core",
            "url": "https://github.com/pulpitum/core.git"
        }
    ],



In the require key of composer.json file add the following line:

    "pulpitum/core": "dev-master"
    
    
Change the minimum stability to dev.

    "minimum-stability": "dev"

Save and execute ```composer update```


Now configure your database in app/config/database.php

The you have to insert the following into app/config/app.php

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

To get Pulpitum themes

Then run the following commands:

    git clone https://github.com/pulpitum/frontendTheme.git public/themes/frontend
    git clone https://github.com/pulpitum/backendTheme.git public/themes/backend


NOTE: This package is under heavy developement, is not yep in beta release.
