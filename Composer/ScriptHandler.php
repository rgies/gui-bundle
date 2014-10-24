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
    public static function getInstallInstructions(CommandEvent $event)
    {
        echo PHP_EOL . 'Symfony RAD-Tool installation complete. ' . PHP_EOL;

        if (DIRECTORY_SEPARATOR != '\\')
        {
            // if os is not windows
            echo '===================================================' . PHP_EOL;
            echo 'PLEASE NOTE:' . PHP_EOL;
            echo 'Run the ./bin/set_dev_access_rights.sh script' . PHP_EOL;
            echo 'to enable the needed access rights for the rapid' . PHP_EOL;
            echo 'development web interface.' . PHP_EOL;
            echo '===================================================' . PHP_EOL . PHP_EOL;
        }

        echo 'Now you can start the Rapid Development Cockpit.' . PHP_EOL;
        echo 'Open your web browser with the following url:' . PHP_EOL;
        echo 'http://localhost/[your website]' . PHP_EOL . PHP_EOL;
    }
}
