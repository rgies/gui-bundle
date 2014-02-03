Rapid Development Bundle
==============================

Symfony 2 Bundle for Rapid Developerment (e.g. Code Generation).

## Installation ##

Add the following code to your ```composer.json``` file:

    "require": {
        ..
        "rgies/rad-bundle": "dev-master"
    },

And then run the Composer update command:

    $ php composer.phar update rgies/rad-bundle

Then register the bundle in the `AppKernel.php` file:

    public function registerBundles()
    {
        $bundles = array(
            ...
            new RGies\RadBundle\RadBundle(),
            ...
        );

        return $bundles;
    }
