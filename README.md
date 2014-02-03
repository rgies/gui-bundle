rad-bundle
==========

Symfony 2 bundle for rapid developerment (e.g. code generation).

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
            new RGies\RadBundle\RGiesRadBundle(),
            ...
        );

        return $bundles;
    }
