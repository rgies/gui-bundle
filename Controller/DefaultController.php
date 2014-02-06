<?php
/**
 * Symfony2 - Rapid Development Bundle.
 *
 * @package     RGies\GuiBundle\Controller
 * @author      Robert Gies <mail@rgies.com>
 * @copyright   Copyright © 2014 by Robert Gies
 * @license     MIT
 * @date        2014-02-04
 */

namespace RGies\GuiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use RGies\GuiBundle\Executor\CommandExecutor;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController
 * @package RGies\GuiBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * Show dashboard view.
     *
     * @Route("/", name="gui")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Create bundle view.
     *
     * @Route("/create-bundle", name="guiCreateBundle")
     * @Template()
     */
    public function createBundleAction()
    {
        return array();
    }

    /**
     * Create controller view.
     *
     * @Route("/create-controller", name="guiCreateController")
     * @Template()
     */
    public function createControllerAction()
    {
        $srcPath = rtrim(dirname($this->get('kernel')->getRootDir()), '/') . '/src';
        $allBundles = $this->container->getParameter('kernel.bundles');
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

        return array('bundles' => $bundles);
    }

    /**
     * Ajax Execute Command.
     *
     * @Route("/execute-command", name="guiExecuteCommand")
     * @Template()
     */
    public function execCommandAction()
    {
        $request = Request::createFromGlobals()->request;
        $executor = new CommandExecutor($this->get('kernel'));

        $command = $request->get('command');
        $rootPath = rtrim(dirname($this->get('kernel')->getRootDir()), '/');

        switch($command)
        {
            // generate new bundle
            case 'generate:bundle':
                $bundleName = $executor->formatBundleName($request->get('bundleName'));
                $namespace = $executor->formatNamespace($request->get('bundleNamespace')) . '/' . $bundleName;
                $cmd = $command;
                $cmd.= ' --bundle-name="' . $bundleName . '"';
                $cmd.= ' --namespace="' . $namespace . '"';
                $cmd.= ' --format="annotation"';
                $cmd.= ' --dir="' . $rootPath . '/src"';
                $cmd.= ($request->get('createStructure')) ? ' --structure' : '';
                break;

            // generate new controller
            case 'gui:generate:controller':
                $bundleName = $request->get('bundleName');
                $controllerName = $executor->formatControllerName($request->get('controllerName'));
                $actionNames = $request->get('controllerActions');
                $cmd = $command;
                $cmd.= ' --controller="' . $bundleName . ':' . $controllerName . '"';
                if ($actionNames)
                {
                    $actions = explode(',', $actionNames);
                    foreach ($actions as $action)
                    {
                        $cmd.= ' --actions="' . $executor->formatActionName($action) . ':'
                            . $executor->formatActionPathName($action) . '"';
                    }
                }
                $cmd.= ' --template-format="twig"';
                $cmd.= ' --route-format="annotation"';
                $cmd.= ' --no-interaction';
                break;

            default:
                $cmd = null;
        }

        // execute command
        $ret = ($cmd) ? $executor->execute($command, $cmd) : '';

        // return json result
        echo json_encode($ret);
        exit;
    }
}
