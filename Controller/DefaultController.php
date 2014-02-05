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
     * @Route("/gui", name="gui")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Create bundle view.
     *
     * @Route("/gui/create-bundle", name="guiCreateBundle")
     * @Template()
     */
    public function createBundleAction()
    {
        return array();
    }

    /**
     * Execute Command.
     *
     * @Route("/gui/execute-command", name="guiExecuteCommand")
     * @Template()
     */
    public function execCommandAction()
    {
        $request = Request::createFromGlobals()->request;
        $executor = new CommandExecutor($this->get('kernel'));

        $command = $request->get('command');

        switch($command)
        {
            // Generate new bundle
            case 'generate:bundle':
                $cmd = $command;
                $cmd.= ' --bundle-name="' . $this->_formatBundleName($request->get('bundleName')) . '"';
                $cmd.= ' --namespace="' . $request->get('bundleNamespace') . '"';
                $cmd.= ' --format="annotation"';
                $cmd.= ' --dir="annotation"';
                $cmd.= ($request->get('createStructure')) ? ' --structure' : '';
                break;

            default:
                $cmd = null;
        }

        $ret = ($cmd) ? $executor->execute($cmd) : '';

        var_dump($ret);exit;
        return array();
    }

    /**
     * Format bundle name.
     *
     * @param string $name Bundle name
     * @return string
     */
    protected function _formatBundleName($name)
    {
        $name = lcfirst($name);
        $name = preg_replace('~[^-a-zA-Z0-9_]+~', '', $name);
        $name = preg_replace('~.*(Bundle|bundle)~', '', $name) . 'Bundle';

        return $name;
    }
}
