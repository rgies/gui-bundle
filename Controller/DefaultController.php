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

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem as FS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

use RGies\GuiBundle\Util\CommandExecutor;
use RGies\GuiBundle\Util\BundleUtil;

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
        $bundles = BundleUtil::getCustomBundleNameList($this, $this->container);
        if (count($bundles) == 0)
        {
            return $this->redirect('create-bundle');
        }

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
     * Create form view.
     *
     * @Route("/create-form", name="guiCreateForm")
     * @Template()
     */
    public function createFormAction()
    {
        return array();
    }

    /**
     * Create crud view.
     *
     * @Route("/create-crud", name="guiCreateCrud")
     * @Template()
     */
    public function createCrudAction()
    {
        $dataTypes = array(
            'integer',
            'string(10)',
            'string(50)',
            'string(100)',
            'string(255)',
            'text',
            'boolean',
            'datetime',
            'float',
            'smallint',
            'bigint',
        );

        return array(
            'types'   => $dataTypes,
            'bundles' => BundleUtil::getCustomBundleNameList($this, $this->container)
        );
    }

    /**
     * Install bundle view.
     *
     * @Route("/install-bundle", name="guiInstallBundle")
     * @Template()
     */
    public function installBundleAction()
    {
        $configFile = dirname(__DIR__). '/Resources/config/bundle_repository.xml';
        $bundles = simplexml_load_file($configFile);

        // Thats resolve a small problem with kernel entries
        foreach ($bundles->bundle as $bundle) {
            $bundle->kernelEntry = urlencode($bundle->kernelEntry);
        }

        return array('bundles' => $bundles);
    }

    /**
     * Ajax action to install defined package.
     *
     * @Route("/install-bundle-ajax", name="guiInstallBundleAjax")
     * @Template()
     */
    public function installBundleAjaxAction()
    {
        $request = Request::createFromGlobals()->request;
        $bundlePath = $request->get('bundlePath');
        $bundleVersion = $request->get('bundleVersion');
        $rootPath = rtrim(dirname($this->get('kernel')->getRootDir()), '/');

        if (!$bundlePath)
        {
            die('Error: Bundle path not set.');
        }

        if (!$bundleVersion)
        {
            die('Error: Bundle version not set.');
        }

        // insert bundle to composer.json file
        $composerJsonFile = $rootPath . '/composer.json';

        // use symfony file system
        $fs = new FS();
        if ($fs->exists($composerJsonFile))
        {
            // read entries from file
            $composerFile = file_get_contents($composerJsonFile);

            // decode json string into object
            $composerJson = json_decode($composerFile);

            // check object if it a valid object
            if ($composerJson && is_object($composerJson))
            {
                // retrieve all requirements from composer json object
                $composerRequires =  $composerJson->require;

                // check if we have allready set the new bundle
                if (!isset($composerRequires->{$bundlePath}))
                {
                    // set new bundle and their version
                    $composerRequires->{$bundlePath} = $bundleVersion;

                    // override composer requirements with new one
                    $composerJson->require = $composerRequires;

                    // encode the json object
                    $data = json_encode($composerJson, 128);

                    // prepare json to a pretty json
                    $data = BundleUtil::getPrettyJson($data);

                    // dump json string into file
                    // mode 0664 = read/write for user and group and read for all other
                    $fs->dumpFile($composerJsonFile, '', 0664);
                    $fs->dumpFile($composerJsonFile, $data, 0664);
                }

                unset($composerRequires);
            }

            unset($composerJson);
        }

        unset($composerJsonFile, $fs);

        // execute composer
        putenv('PATH=' . $_SERVER['PATH']);
        exec($rootPath . '/bin/composer self-update');
        exec($rootPath . '/bin/composer -n -d="' . $rootPath . '" update ' . $bundlePath, $out, $ret);

        //var_dump($out);

        if (!$ret)
        {
            // Register new bundle after it was installed
            $kernel = $this->get('kernel');
            if ($kernel instanceof Kernel)
            {
                // Check if bundle already installed
                $bundleName = $request->get('bundleName');
                $bundles = BundleUtil::getCustomBundleNameList($this, $this->container);
                $bundleInstalled = BundleUtil::bundleInstalled($bundles, $bundleName);
                if (!$bundleInstalled)
                {
                    // Replace some stuff from kernel entry
                    $kernelEntry = urldecode($request->get('kernelEntry'));
                    $kernelEntry = str_replace('new ', '', $kernelEntry);
                    $kernelEntry = str_replace('()', '', $kernelEntry);

                    // Register bundle
                    $km = new KernelManipulator($kernel);
                    try
                    {
                        $km->addBundle($kernelEntry);
                    }
                    catch (\RuntimeException $ex)
                    {
                        echo($ex->getMessage());
                        die;
                    }
                    unset($km);
                }
            }

            // handle success
            echo 'Done';
        } else {
            // handle error
            echo 'Error';
        }

        exit;
    }

    /**
     * Create controller view.
     *
     * @Route("/create-controller", name="guiCreateController")
     * @Template()
     */
    public function createControllerAction()
    {
        return array(
            'bundles' => BundleUtil::getCustomBundleNameList($this, $this->container)
        );
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
                $cmd.= ($request->get('createStructure')=='on') ? ' --structure' : '';
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

            // generate entity
            case 'doctrine:generate:entity':
                $bundleName = $request->get('bundleName');
                $entityName = $executor->formatEntityName($request->get('entityName'));
                $fields = $request->get('fieldName');
                $types = $request->get('fieldType');
                $cmd = $command;
                $cmd.= ' --entity="' . $bundleName . ':' . $entityName . '"';
                if (is_array($fields) && count($fields) && $fields[0])
                {
                    $cmd .= ' --fields="';
                    foreach ($fields as $key=>$field)
                    {
                        $fieldName = $executor->formatFieldName($field);
                        if ($fieldName && $fieldName != 'id' && $types[$key])
                        {
                            $cmd .= $fieldName . ':' . $types[$key] . ' ';
                        }
                    }
                    $cmd = rtrim($cmd, ' ');
                    $cmd .= '"';
                }
                $cmd.= ' --format="annotation"';
                $cmd.= ' --with-repository';
                $cmd.= ' --no-interaction';
                break;

            // generate crud
            case 'doctrine:generate:crud':
                $bundleName = $request->get('bundleName');
                $entityName = $executor->formatEntityName($request->get('entityName'));
                $cmd = 'doctrine:generate:crud';
                $cmd.= ' --entity="' . $bundleName . ':' . $entityName . '"';
                $cmd.= ' --route-prefix="' . strtolower($entityName) . '"';
                $cmd.= ' --with-write';
                $cmd.= ' --no-interaction';
            break;

            default:
                $cmd = null;
        }

        // execute command
        $ret = ($cmd) ? $executor->execute($cmd) : '';

        if ($ret['errorcode'] == 0 && $command == 'doctrine:generate:crud' && $request->get('createTable') == 'on')
        {
            $ret2 = $executor->execute('doctrine:database:create');
            $ret3 = $executor->execute('doctrine:schema:update --force');
            $ret['output'] .= '<br/>' . $ret3['output'];
        }


        // return json result
        echo json_encode($ret);
        exit;
    }
}
