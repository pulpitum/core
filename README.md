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

NOTE: This package is under heavy developement, ist not yep in beta release.
