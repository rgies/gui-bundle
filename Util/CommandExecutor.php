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

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\Kernel;

use Symfony\Bundle\FrameworkBundle\Console\Application;

use RGies\GuiBundle\Output\StringOutput;
use RGies\GuiBundle\Formatter\HtmlConsoleOutputFormatter;

/**
 * Class CommandExecutor
 * @package RGies\GuiBundle\Util
 */
class CommandExecutor
{
    protected $baseKernel;

    public function __construct(Kernel $baseKernel)
    {
        $this->baseKernel = $baseKernel;
    }

    /**
     * Execute symfony cli command.
     *
     * @param string $action Name of action e.g. generate:bundle
     * @param string $commandString Command to execute
     * @return array
     */
    public function execute($action, $commandString)
    {
        $input = new StringInput($commandString);
        $input->setInteractive(false);

        $output = new StringOutput();
        $formatter = $output->getFormatter();
        $formatter->setDecorated(true);
        $output->setFormatter(new HtmlConsoleOutputFormatter($formatter));

        //$application = new Application($this->getContainer()->get('kernel'));
        $application = $this->_getApplication($input);
        $kernel = $application->getKernel();
        chdir($kernel->getRootDir() . '/..');

        $application->setAutoExit(false);

        ob_start();
        $errorCode = $application->run($input, $output);
        $result = $output->getBuffer() || ob_get_contents();
        ob_end_clean();

        return array(
            'action'      => $action,
            'input'       => $commandString,
            'output'      => $output->getBuffer(),
            'environment' => $kernel->getEnvironment(),
            'errorcode'  => $errorCode
        );
    }

    /**
     * Format bundle name.
     *
     * @param string $name Bundle name
     * @return string
     */
    public function formatBundleName($name)
    {
        $name = ucfirst($name);
        $name = preg_replace('~[^a-zA-Z0-9]+~', '', $name);
        $name = preg_replace('~.*(Bundle|bundle)$~', '', $name) . 'Bundle';

        return $name;
    }

    /**
     * Format controller name.
     *
     * @param string $name Controller name
     * @return string
     */
    public function formatControllerName($name)
    {
        $name = ucfirst($name);
        $name = preg_replace('~[^a-zA-Z0-9]+~', '', $name);
        $name = preg_replace('~.*(Controller|controller)$~', '', $name);

        return $name;
    }

    /**
     * Format entity name.
     *
     * @param string $name Entity name
     * @return string
     */
    public function formatEntityName($name)
    {
        $name = ucfirst($name);
        $name = preg_replace('~[^a-zA-Z0-9]+~', '', $name);

        return $name;
    }

    /**
     * Format action name.
     *
     * @param string $name Action name
     * @return string
     */
    public function formatActionName($name)
    {
        $name = lcfirst($name);
        $name = preg_replace('~[^a-zA-Z0-9]+~', '', $name);
        $name = preg_replace('~.*(Action|action)$~', '', $name) . 'Action';

        return $name;
    }

    /**
     * Format field name.
     *
     * @param string $name Field name
     * @return string
     */
    public function formatFieldName($name)
    {
        $name = strtolower($name);
        $name = preg_replace('~[^a-zA-Z0-9_]+~', '', $name);

        return $name;
    }

    /**
     * Format action path name.
     *
     * @param string $name Action path name
     * @return string
     */
    public function formatActionPathName($name)
    {
        $name = lcfirst($name);
        $name = preg_replace('~[^a-zA-Z0-9]+~', '', $name);
        $name = '/' . trim($name, ' /'). '/';

        return $name;
    }

    /**
     * Format namespace.
     *
     * @param string $namespace Namespace
     * @return string
     */
    public function formatNamespace($namespace)
    {
        $name = ucfirst($namespace);
        $name = preg_replace('~[^a-zA-Z0-9]+~', '', $name);

        return $name;
    }

    protected function _getApplication($input = null)
    {
        $kernel = $this->_getKernel($input);

        return new Application($kernel);
    }

    protected function _getKernel($input = null)
    {
        if($input === null) {
            return $this->baseKernel;
        }

        $env = $input->getParameterOption(array('--env', '-e'), $this->baseKernel->getEnvironment());
        $debug = !$input->hasParameterOption(array('--no-debug', ''));

        if($this->baseKernel->getEnvironment() === $env && $this->baseKernel->isDebug() === $debug) {
            return $this->baseKernel;
        }

        $kernelClass = new \ReflectionClass($this->baseKernel);

        return $kernelClass->newInstance($env, $debug);
    }

}
