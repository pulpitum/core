core
====

Pulpitum core package

This is the core package of the platform, here you will this the tools that makes this platform work.

How to install Pulpitum:

Install Laravel
composer create-project laravel/laravel pulpitum --prefer-dist

Add necessary packages to laravel composer.conf to the require json key.

    "purposemedia/menu": "dev-master",
    "teepluss/theme": "dev-master",
    "anahkiasen/former": "dev-master",
    "cartalyst/sentry": "2.1.*",
    "pulpitum/core"

Save and execute "composer update".
