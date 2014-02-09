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
 * Class BundleUtil.
 *
 * @package RGies\GuiBundle\Util
 */
class BundleUtil
{
    /**
     * Gets list of all bundles found in src path.
     *
     * @param Controller $controller
     * @param Container $container
     * @return array
     */
    public static function getCustomBundleNameList(Controller $controller, Container $container)
    {
        $srcPath = rtrim(dirname($controller->get('kernel')->getRootDir()), '/') . '/src';
        $allBundles = $container->getParameter('kernel.bundles');
        $bundles = array();

        // find all bundles in src folder
        foreach ($allBundles as $bundle=>$path)
        {
            if (is_dir($srcPath . '/' . dirname($path)))
            {
                $bundles[] = $bundle;
            }
        }
        asort($bundles);

        return $bundles;
    }
} 