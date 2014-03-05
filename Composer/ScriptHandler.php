<?php

/**
 * Symfony2 - Rapid Development Bundle.
 *
 * @package     RGies\GuiBundle\Composer
 * @author      Robert Gies <mail@rgies.com>
 * @copyright   Copyright © 2014 by Robert Gies
 * @license     MIT
 * @date        2014-03-05
 */

namespace RGies\GuiBundle\Composer;

use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Composer\Script\CommandEvent;

/**
 * Class ScriptHandler.
 *
 * @package RGies\GuiBundle\Composer
 */
class ScriptHandler
{
    /**
     * Gets instructions how to use this bundle.
     *
     * @param CommandEvent $event
     */
    public static function getInstructions(CommandEvent $event)
    {
        echo PHP_EOL . 'Symfony RAD-Tool installation complete. ';
        echo 'Now you can start the Rapid Development Cockpit.' . PHP_EOL;
        echo 'Open your web browser with this url:' . PHP_EOL;
        echo 'http://localhost/[...]/app_dev.php/gui' . PHP_EOL;
    }
}
