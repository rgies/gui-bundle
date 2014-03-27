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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Filesystem\Filesystem as FS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

use RGies\GuiBundle\Util\CommandExecutor;
use RGies\GuiBundle\Util\BundleUtil;

use Exception;
use RuntimeException;

/** Thats a fallback for PHP < 5.4 */
if (!defined('JSON_PRETTY_PRINT')) {
    define('JSON_PRETTY_PRINT', 128);
}

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
            'string(5)',
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
        foreach ($bundles->bundle as $key => $bundle) {

            $bundle->addChild('installed', BundleUtil::bundleInstalled($this->container, (string)$bundle->bundleName));
            $bundle->kernelEntry = urlencode($bundle->kernelEntry);
            $bundle->routingEntry = json_encode($bundle->routingEntry);


            // set default bundle icon
            if (!isset($bundle->icon) || !trim($bundle->icon))
            {
                $bundle->icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHgAAABkCAYAAABNcPQyAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RUY4NzM1RTBBNUM5MTFFMzlFRTZBNUZFRDM5MDcxOUQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RUY4NzM1RTFBNUM5MTFFMzlFRTZBNUZFRDM5MDcxOUQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpFRjg3MzVERUE1QzkxMUUzOUVFNkE1RkVEMzkwNzE5RCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpFRjg3MzVERkE1QzkxMUUzOUVFNkE1RkVEMzkwNzE5RCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pnyh4Y0AACclSURBVHja7H1pcGTX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';
            }
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

        $request = Request::createFromGlobals();
        $bundlePath = $request->request->get('bundlePath');
        $bundleVersion = $request->request->get('bundleVersion');
        $bundleName = $request->request->get('bundleName');
        $rootPath = rtrim(dirname($this->get('kernel')->getRootDir()), '/');
        $routingEntry = $request->request->get('routingEntry');

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
                    $data = json_encode($composerJson, JSON_PRETTY_PRINT);

                    // prepare json to a pretty json
                    $data = BundleUtil::getPrettyJson($data);

                    // dump json string into file
                    // mode 0664 = read/write for user and group and read for all other
                    file_put_contents($composerJsonFile, $data);
                    //$fs->dumpFile($composerJsonFile, '', 0777);
                    //$fs->dumpFile($composerJsonFile, $data, 0777);
                }

                unset($composerRequires);
            }

            unset($composerJson);
        }

        unset($composerJsonFile, $fs);

        /**
         * Anonymous callback for process
         *
         * @param string    $type   The process type
         * @param mixed     $output The output of process
         */
        $callback = function($type, $output)
        {
            if (Process::ERR === $type)
            {
                echo 'error on executing composer: \n\n' . $output;
                die;
            }
        };

        // execute composer self-update
        $processBuilder = new ProcessBuilder($this->getComposerArguments($rootPath, 'self-update'));
        $processBuilder->setEnv('PATH', $request->server->get('PATH'));
        $processBuilder->setEnv('COMPOSER_HOME', $rootPath . '/bin');

        $process = $processBuilder->getProcess();
        $process->setTimeout(90);
        $process->setIdleTimeout(NULL);
        $ret = $process->run($callback);
        if ($ret == 0)
        {
            echo 'Execute composer self-update finished.';
        }

        unset($processBuilder, $process);

        // execute composer update on specified bundle
        $processBuilder = new ProcessBuilder($this->getComposerArguments($rootPath, 'update', $bundlePath));
        $processBuilder->setEnv('PATH', $request->server->get('PATH'));
        $processBuilder->setEnv('COMPOSER_HOME', $rootPath . '/bin');
        $processBuilder->setWorkingDirectory($rootPath);

        // Generate output for AJAX call
        echo 'Running update on: ' . $bundleName;

        $process = $processBuilder->getProcess();
        $process->setTimeout(3600);
        $process->setIdleTimeout(60);
        $ret = $process->run($callback);

        if ($ret == 0)
        {
            // Register new bundle after it was installed
            $kernel = $this->get('kernel');
            if ($kernel instanceof Kernel)
            {
                // Check if bundle already installed
                $bundleInstalled = BundleUtil::bundleInstalled($this->container, $bundleName);
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
                    catch (RuntimeException $ex)
                    {
                        echo($ex->getMessage());
                        die;
                    }
                    unset($km);
                }
            }

            // Handle route installation
            if (!isset($routingEntry) && !empty($routingEntry))
            {
                // Retrieve project environment and handle the correct YAML file
                $env = $this->get('kernel')->getEnvironment();
                if ($env == 'dev') {
                    $routeFile = $rootPath . '/app/config/routing_' . $env . '.yml';
                }
                else {
                    $routeFile = $rootPath . '/app/config/routing.yml';
                }

                try {
                    // Get content of YAML file
                    $ymlFileContent = file_get_contents($routeFile);

                    // Parse YAML file
                    $routes = Yaml::parse($ymlFileContent, true);
                    if (!isset($routes[$routingEntry['name']]))
                    {
                        $routes[$routingEntry['name']] = array(
                          'resource' => $routingEntry['resource'],
                          'type' => $routingEntry['type'],
                          'prefix' => $routingEntry['prefix'],
                        );
                    }

                    $result = Yaml::dump($routes);
                    if ($result)
                    {
                        echo 'Installing of \'' . $routingEntry['name'] . '\' completed.';
                    }
                }
                catch (ParseException $ex) {
                    echo 'Unable to parse the YAML string: ' . $ex->getMessage();
                    die;
                }
                catch (Exception $ex)
                {
                    echo 'Exception was thrown: ' . $ex->getMessage();
                    die;
                }
            }

            // Clear cache
            BundleUtil::clearCache($kernel);

            // handle success
            echo 'Done';
        } else {
            // handle error
            echo 'Error on updating: ' . $bundleName . '\n\n' . Process::$exitCodes[$ret];
        }

        unset($processBuilder, $process);
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

    /**
     * Get the arguments for the compoer process
     *
     * @param string $path      A root path of the project folder
     * @param string $option    A option to identify the process arguments
     * @param string $what      A optional argument for update (like: name/bundle ...)
     *
     * @return array Returns the right arguments for the composer process
     */
    private function getComposerArguments($path, $option = 'self-update', $what = '')
    {
        $phpFinder = new PhpExecutableFinder();
        $php = $phpFinder->find();
        if ($php === FALSE)
        {
            $php = '';
        }

        $composer = array();
        switch ($option)
        {
            case 'self-update':
                $composer = array(
                  $php,
                  (empty($php)) ? $path . '/bin/composer' : $path . '/bin/composer.phar',
                  'self-update'
                );
                break;
            case 'update':
                $composer = array(
                  $php,
                  (empty($php)) ? $path . '/bin/composer' : $path . '/bin/composer.phar',
                  '--no-interaction',
                  'update',
                  $what
                );
                break;
        }
        return $composer;
    }
}
