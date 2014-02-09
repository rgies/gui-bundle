<?php
/**
 * Symfony2 - Rapid Development Bundle.
 *
 * @package     RGies\RadBundle\Util
 * @author      Robert Gies <mail@rgies.com>
 * @copyright   Copyright © 2014 by Robert Gies
 * @license     MIT
 * @date        2014-02-04
 */

namespace RGies\GuiBundle\Util;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;


/**
 * Class ControllerUtil.
 *
 * @package RGies\GuiBundle\Util
 */
class ControllerUtil
{
    public static function getControllerNameList(Container $container, string $bundleName)
    {
        $controllerNames = array();

        try
        {
            $bundle = $container->get('kernel')->getBundle($bundleName);
        }
        catch (\Exception $e)
        {
            return $controllerNames;
        }

        $dir = $bundle->getPath();
        $controllerDir = $dir . '/Controller/';

        if ($handle = opendir($controllerDir))
        {
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry[0] != '.' && mb_substr($entry,-14) == 'Controller.php')
                {
                    $controllerNames[] = $entry;
                }
            }

            closedir($handle);
        }

        return $controllerNames;
    }
} 