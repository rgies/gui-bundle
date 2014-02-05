Rapid Development GUI
==============================

Symfony 2 GUI Bundle for Rapid Developerment (e.g. Code Generation).

## Installation ##

1.Add the following code to your ```composer.json``` file:

    "require": {
        ..
        "rgies/gui-bundle": "dev-master"
    },

2.And then run the Composer update command:

    $ php composer.phar update rgies/gui-bundle

3.Then register the bundle in the `AppKernel.php` file:

    public function registerBundles()
    {
        $bundles = array(
            ...
            new RGies\GuiBundle\GuiBundle(),
            ...
        );

        return $bundles;
    }

4.Add the following route to your routing configuration:

    rad:
        resource: "@GuiBundle/Controller/"
        type:     annotation
        prefix:   /gui
