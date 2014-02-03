Rapid Development Bundle
==============================

Symfony 2 Bundle for Rapid Developerment (e.g. Code Generation).

## Installation ##

1. Add the following code to your ```composer.json``` file:

    "require": {
        ..
        "rgies/rad-bundle": "dev-master"
    },

2. And then run the Composer update command:

    $ php composer.phar update rgies/rad-bundle

3. Then register the bundle in the `AppKernel.php` file:

    public function registerBundles()
    {
        $bundles = array(
            ...
            new RGies\RadBundle\RadBundle(),
            ...
        );

        return $bundles;
    }

4. Add the following route to your routing configuration:

    rad:
        resource: "@RadBundle/Controller/"
        type:     annotation
        prefix:   /
